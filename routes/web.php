<?php

use App\Enums\Currency;
use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use App\Services\Maventa\MaventaAuthenticator;
use App\Services\Maventa\MaventaRegistrationService;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {

    $company = Company::where('id', '=', 6)->first();

    $payload = [
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

    dd($payload);

});
