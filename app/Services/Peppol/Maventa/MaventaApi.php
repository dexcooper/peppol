<?php

namespace App\Services\Peppol\Maventa;

use App\Models\Company;
use App\Services\Peppol\Maventa\Exceptions\MaventaApiException;
use Illuminate\Support\Facades\Http;

class MaventaApi
{
    protected string $baseUrl;
    protected ?Company $company = null;
    protected bool $useAccessToken = true;
    protected MaventaAuthenticator $auth;

    public function __construct(MaventaAuthenticator $auth)
    {
       $this->auth = $auth;
       $this->baseUrl = config('maventa.base_url');
    }

    public function forCompany(Company $company): static
    {
        $this->company = $company;
        $this->useAccessToken = true;
        return $this;
    }

    public function asVendor(): static
    {
        $this->company = null;
        $this->useAccessToken = false;
        return $this;
    }

    public function withBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function useValidationApi(): static
    {
        $this->baseUrl = config('maventa.validation_base_url');
        return $this;
    }

    protected function client()
    {
        $client = Http::loggable()->baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(config('maventa.timeout', 15));

        if ($this->useAccessToken && $this->company) {
            $token = $this->auth->getAccessToken($this->company);
            $client = $client->withToken($token);
        }

        return $client;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $response = $this->client()->get($this->fullUrl($endpoint), $params);
        return $this->handleResponse($response);
    }

    public function post(string $endpoint, array $payload = []): array
    {
        $response = $this->client()->post($this->fullUrl($endpoint), $payload);
        return $this->handleResponse($response);
    }

    public function postMultipart(string $endpoint, $xml): array
    {
        $client = $this->client();

        $response = $client->attach('file', $xml)
            ->post($this->fullUrl($endpoint));

        return $this->handleResponse($response);
    }

    protected function fullUrl(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    protected function handleResponse($response): array
    {
        if ($response->failed()) {
            throw new MaventaApiException("Maventa API error [{$response->status()}]: {$response->body()}");
        }
        return $response->json() ?? [];
    }
}
