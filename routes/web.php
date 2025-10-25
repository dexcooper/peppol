<?php

use App\Enums\Currency;
use App\Enums\InvoiceStatus;
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

    $baseUrl = config('maventa.base_url');
    $endpoint = 'v1/users';

    Http::fake([
        '*/v1/users' => function ($request) {
            dump('Intercepted:', $request->url());
            return Http::response(['user_api_key' => 'maventa_user_123'], 200);
        },
    ]);

    $fullUrl = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

     $client = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->timeout(config('maventa.timeout', 15));

     $response = $client->get($fullUrl, []);

     dd($response->json());

});
