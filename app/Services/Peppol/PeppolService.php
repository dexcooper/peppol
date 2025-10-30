<?php
namespace App\Services\Peppol;

use App\Contracts\Peppol\PeppolProviderInterface;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;

class PeppolService
{
    protected PeppolProviderInterface $provider;
    protected XmlBuilder $xmlBuilder;

    public function __construct(XmlBuilder $xmlBuilder, Company $company)
    {
        $this->xmlBuilder = $xmlBuilder;
        $this->provider = $this->resolveProvider($company);
    }

    public function resolveProvider(Company $company): PeppolProviderInterface
    {
        return match (strtolower($company->peppol_provider->value)) {
            'maventa' => app(\App\Services\Peppol\Providers\MaventaProvider::class, [
                'api' => app(\App\Services\Peppol\Maventa\MaventaApi::class),
                'company' => $company,
            ]),
            default => throw new \Exception("Unknown Peppol provider: {$company->peppol_provider->value}")
        };
    }

    public function sendInvoice($invoice)
    {
        $xml = $this->xmlBuilder->build($invoice);
        dd($xml);
        $result = $this->provider->validate($xml);
        dd($result);
        return $this->provider->send($xml);
    }
}
