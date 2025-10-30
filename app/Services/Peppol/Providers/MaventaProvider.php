<?php
namespace App\Services\Peppol\Providers;

use App\Models\Company;
use App\Contracts\Peppol\PeppolProviderInterface;
use App\Services\Peppol\Maventa\MaventaApi;

class MaventaProvider implements PeppolProviderInterface
{
    protected MaventaApi $api;
    protected Company $company;

    public function __construct(MaventaApi $api, Company $company)
    {
        $this->api = $api->forCompany($company);
        $this->company = $company;
    }

    public function registerCompany(Company $company): ?string
    {
        if ($company->maventa_company_id) {
            return $company->maventa_company_id;
        }

        $userId = $this->ensureUser($company);

        $payload = [
            'vendor_api_key' => config('maventa.vendor_api_key'),
            'user_api_key' => $userId,
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

    protected function ensureUser(Company $company): ?string
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

    public function validateInvoice(string $xml): array
    {
        return $this->api->useValidationApi()->postMultipart('/validate', $xml);
    }

    public function sendInvoice(string $xml): array
    {
        return $this->api->post('/v1/invoices', $xml);
    }
}
