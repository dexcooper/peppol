<?php
namespace App\Services\Maventa;

use App\Models\User;
use App\Models\Company;

class MaventaRegistrationService
{
    public function __construct(
        protected MaventaApi $api
    ) {}

    public function ensureMaventaUser(User $user): ?string
    {
        if ($user->maventa_user_id) {
            return $user->maventa_user_id;
        }

        $payload = [
            'vendor_api_key' => config('maventa.vendor_api_key'),
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ];

        $response = $this->api->asVendor()->post('/v1/users', $payload);

        $user->update(['maventa_user_id' => $response['user_api_key'] ?? null]);
        return $user->maventa_user_id;
    }

    public function ensureMaventaCompany(Company $company, User $user): ?string
    {
        if ($company->maventa_company_id) {
            return $company->maventa_company_id;
        }

        $maventaUserId = $this->ensureMaventaUser($user);

        $payload = [
            'vendor_api_key' => config('maventa.vendor_api_key'),
            'user_id' => $maventaUserId,
            'name' => $company->name,
            'business_id' => $company->vat_number ?? null,
            'country' => $company->country ?? 'BE',
        ];

        $response = $this->api->asVendor()->post('/v1/companies', $payload);

        $company->update(['maventa_company_id' => $response['id'] ?? null]);
        return $company->maventa_company_id;
    }
}
