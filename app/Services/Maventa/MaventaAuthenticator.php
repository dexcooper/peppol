<?php

namespace App\Services\Maventa;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\Maventa\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Log;

class MaventaAuthenticator
{
    protected string $baseUrl;

   public function __construct()
    {
        $this->baseUrl = config('maventa.base_url');
    }

   public function getAccessToken(Company $company): string
    {
        if (empty($company->maventa_user_id)) {
            throw new \RuntimeException("Company [{$company->id}] has no maventa_user_id.");
        }

        if (empty($company?->maventa_company_id)) {
            throw new \RuntimeException("Company [{$company?->id}] has no maventa_company_id.");
        }

        return Cache::remember($this->getCacheKey($company), now()->addMinutes(50), function () use ($company) {
            return $this->requestNewToken($company->maventa_company_id, $company->maventa_user_id, $company->id);
        });
    }

    protected function requestNewToken(string $maventaCompanyId, string $maventaUserIid, int $companyId): string
    {
        $response = Http::loggable()->asForm()->post("{$this->baseUrl}/oauth2/token", [
            'grant_type' => 'client_credentials',
            'client_id' => $maventaCompanyId,
            'client_secret' => $maventaUserIid,
        ]);

        if ($response->failed()) {
            Log::error('Maventa token request failed', [
                'user_id' => $companyId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException("Failed to obtain Maventa access token for company {$companyId}");
        }

        $data = $response->json();
        return $data['access_token'] ?? throw new \RuntimeException('No access token in response.');
    }

    public function refreshToken(User $user): string
    {
        Cache::forget($this->getCacheKey($user));
        return $this->getAccessToken($user);
    }

    protected function getCacheKey(Company $company)
    {
        return "maventa_token_user_{$company->id}";
    }
}
