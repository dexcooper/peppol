<?php

use App\Models\Company;
use Illuminate\Support\Facades\Route;
use App\Services\ApiLogger;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {

    $company = Company::where('id', 7)->first();
    $invoice = $company->invoices()->first();

//    $maventaAuthenticator = new \App\Services\Peppol\Maventa\MaventaAuthenticator();

//    $response = Http::withToken($maventaAuthenticator->getAccessToken($company))
//        ->attach('file', file_get_contents(storage_path('app/private/invoice.xml')), 'test.xml')
//        ->post('https://validator-stage.maventa.com/validate');
//
//    dd($response->json());

    $peppolService = app(\App\Services\Peppol\PeppolService::class, ['company' => $company]);
    $peppolService->sendInvoice($invoice);

});
