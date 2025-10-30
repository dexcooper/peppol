<?php
namespace App\Contracts\Peppol;

use App\Models\Company;

interface PeppolProviderInterface
{
    public function registerCompany(Company $company): ?string;
    public function validateInvoice(string $xml): array;
    public function sendInvoice(string $xml): array;
}
