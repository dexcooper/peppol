<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DragonBe\Vies\Vies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VatValidationController extends ApiController
{
    protected Vies $vies;
    protected bool $cacheEnabled;

    public function __construct(Vies $vies, bool $cacheEnabled = true)
    {
        $this->vies = $vies;
        $this->cacheEnabled = $cacheEnabled; // option to bypass cache in tests
    }

    public function validateVat(Request $request)
    {
        $request->validate([
            'vat_number' => 'required|string',
        ]);

        $vatNumber = strtoupper(str_replace([' ', '-', '.'], '', $request->vat_number));
        $countryCode = substr($vatNumber, 0, 2);
        $number = substr($vatNumber, 2);

        if (!$this->vies->getHeartBeat()->isAlive()) {
            return response()->json(['error' => 'VIES service unavailable'], 503);
        }

        $result = $this->cacheEnabled
            ? Cache::remember("vat_validation_{$vatNumber}", 86400, function () use ($countryCode, $number) {
                return $this->vies->validateVat($countryCode, $number);
            })
            : $this->vies->validateVat($countryCode, $number);

        return $this->success([
            'valid' => $result->isValid(),
        ]);
    }
}
