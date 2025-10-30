<?php
namespace App\Services\Peppol\Maventa;

use App\Models\Company;

class MaventaRegistrationService
{
    public function __construct(
        protected MaventaApi $api
    ) {}

    public function register(Company $company)
    {
        $this->ensureMaventaCompany($company);
    }

    public function ensureMaventaUser(Company $company): ?string
    {
        if ($company->maventa_user_id) {
            return $company->maventa_user_id;
        }

        $payload = [
            'vendor_api_key' => config('maventa.vendor_api_key'),
            'email' => $company->email,
            'first_name' => $company->contact_person_first_name,
            'last_name' => $company->contact_person_last_name,
        ];

        $response = $this->api->asVendor()->post('/v1/users', $payload);

        $company->update(['maventa_user_id' => $response['user_api_key'] ?? null]);
        return $company->maventa_user_id;
    }

    public function ensureMaventaCompany(Company $company): ?string
    {
        if ($company->maventa_company_id) {
            return $company->maventa_company_id;
        }

        $maventaUserId = $this->ensureMaventaUser($company);

        $payload = [
            'vendor_api_key' => config('maventa.vendor_api_key'),
            'user_api_key' => $maventaUserId,
            'name' => $company->name,
            'bid' => $company->vat_number ?? null,
            'no_vat' => false,
            'address1' => $company->address,
            'post_code' => $company->zip_code,
            'post_office' => $company->city,
            'city' => $company->city,
            'country' => $company->country,
            'email' => $company->email,
        ];

        $response = $this->api->asVendor()->post('/v1/companies', $payload);

        $company->update(['maventa_company_id' => $response['id'] ?? null]);
        return $company->maventa_company_id;
    }
}
