<?php

use App\Models\Company;
use App\Services\Peppol\Maventa\MaventaAuthenticator;
use App\Services\Peppol\Maventa\MaventaRegistrationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('creates a new Maventa user and stores the id', function () {
    $company = Company::factory()->create(['name' => 'Test BV']);

    Http::fake([
        '*/v1/users' => Http::response(['user_api_key' => 'maventa_user_123'], 200),
    ]);

    $service = app(MaventaRegistrationService::class);
    $maventaUserId = $service->ensureMaventaUser($company);

    expect($maventaUserId)->toBe('maventa_user_123');
    expect($company->refresh()->maventa_user_id)->toBe('maventa_user_123');
});

it('returns existing maventa_user_id if already set', function () {
    $company = Company::factory()->create(['maventa_user_id' => 'existing_456']);

    Http::preventStrayRequests();

    $service = app(MaventaRegistrationService::class);
    $id = $service->ensureMaventaUser($company);

    expect($id)->toBe('existing_456');
});

it('creates a new Maventa company and stores the ID', function () {
    $company = Company::factory()->create(['name' => 'Test BV']);

    Http::fake([
        '*/v1/companies' => Http::response(['id' => 'maventa_company_789'], 200),
    ]);

    $service = app(MaventaRegistrationService::class);
    $id = $service->ensureMaventaCompany($company);

    expect($id)->toBe('maventa_company_789');
    expect($company->refresh()->maventa_company_id)->toBe('maventa_company_789');
});

it('returns existing maventa_company_id if already set', function () {
    $company = Company::factory()->create(['maventa_company_id' => 'existing_co_999']);

    Http::preventStrayRequests();

    $service = app(MaventaRegistrationService::class);
    $id = $service->ensureMaventaCompany($company);

    expect($id)->toBe('existing_co_999');
});

it('throws when Maventa API fails', function () {
    $company = Company::factory()->create(['email' => 'bad@example.com']);

    Http::fake([
        '*/v1/users' => Http::response(['error' => 'Invalid data'], 400),
    ]);

    $service = app(MaventaRegistrationService::class);

    $this->expectException(Throwable::class);

    $service->ensureMaventaUser($company);
});

it('caches access token for 50 minutes', function () {
    $company = Company::factory()->create(['name' => 'Flow BV', 'maventa_user_id' => 'user_flow_002', 'maventa_company_id' => 'company_flow_002']);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturn('fake_token');

    $auth = app(MaventaAuthenticator::class);
    $token = $auth->getAccessToken($company);

    expect($token)->toBe('fake_token');
});

it('creates both user and company when missing', function () {
    $company = Company::factory()->create(['name' => 'Flow BV']);

    Http::fake([
        '*/v1/users' => Http::response(['user_api_key' => 'user_flow_001'], 200),
        '*/v1/companies' => Http::response(['id' => 'company_flow_002'], 200),
    ]);

    $service = app(MaventaRegistrationService::class);

    $maventaCompanyId = $service->ensureMaventaCompany($company);

    expect($company->refresh()->maventa_user_id)->toBe('user_flow_001');
    expect($maventaCompanyId)->toBe('company_flow_002');
});
