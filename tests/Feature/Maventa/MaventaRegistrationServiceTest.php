<?php

use App\Models\User;
use App\Models\Company;
use App\Services\Maventa\MaventaRegistrationService;
use Illuminate\Support\Facades\Http;

it('creates a new Maventa user and stores the ID', function () {
    $user = User::factory()->create(['email' => 'john@example.com']);

    Http::fake([
        '*/v1/users' => Http::response(['user_api_key' => 'maventa_user_123'], 200),
    ]);

    $service = app(MaventaRegistrationService::class);
    $maventaUserId = $service->ensureMaventaUser($user);

    expect($maventaUserId)->toBe('maventa_user_123');
    expect($user->refresh()->maventa_user_id)->toBe('maventa_user_123');
});

it('returns existing maventa_user_id if already set', function () {
    $user = User::factory()->create(['maventa_user_id' => 'existing_456']);

    Http::preventStrayRequests();

    $service = app(MaventaRegistrationService::class);
    $id = $service->ensureMaventaUser($user);

    expect($id)->toBe('existing_456');
});

it('creates a new Maventa company and stores the ID', function () {
    $user = User::factory()->create(['maventa_user_id' => 'maventa_user_123']);
    $company = Company::factory()->create(['name' => 'Test BV']);

    Http::fake([
        '*/v1/companies' => Http::response(['id' => 'maventa_company_789'], 200),
    ]);

    $service = app(MaventaRegistrationService::class);
    $id = $service->ensureMaventaCompany($company, $user);

    expect($id)->toBe('maventa_company_789');
    expect($company->refresh()->maventa_company_id)->toBe('maventa_company_789');
});

it('returns existing maventa_company_id if already set', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['maventa_company_id' => 'existing_co_999']);

    Http::preventStrayRequests();

    $service = app(MaventaRegistrationService::class);
    $id = $service->ensureMaventaCompany($company, $user);

    expect($id)->toBe('existing_co_999');
});

it('throws when Maventa API fails', function () {
    $user = User::factory()->create(['email' => 'bad@example.com']);

    Http::fake([
        '*/v1/users' => Http::response(['error' => 'Invalid data'], 400),
    ]);

    $service = app(MaventaRegistrationService::class);

    $this->expectException(Throwable::class);

    $service->ensureMaventaUser($user);
});

use App\Services\Maventa\MaventaAuthenticator;
use Illuminate\Support\Facades\Cache;

it('caches access token for 50 minutes', function () {

    $company = Company::factory()->create(['name' => 'Flow BV', 'maventa_company_id' => 'company_flow_002']);
    $user = User::factory()->create(['email' => 'flow@example.com', 'maventa_user_id' => 'user_flow_001', 'company_id' => $company->id]);;

    Cache::shouldReceive('remember')
        ->once()
        ->andReturn('fake_token');

    $auth = app(MaventaAuthenticator::class);
    $token = $auth->getAccessToken($company->getApiUser());

    expect($token)->toBe('fake_token');
});

it('creates both user and company when missing', function () {
    $user = User::factory()->create(['email' => 'flow@example.com']);
    $company = Company::factory()->create(['name' => 'Flow BV']);

    Http::fake([
        '*/v1/users' => Http::response(['user_api_key' => 'user_flow_001'], 200),
        '*/v1/companies' => Http::response(['id' => 'company_flow_002'], 200),
    ]);

    $service = app(MaventaRegistrationService::class);

    $maventaCompanyId = $service->ensureMaventaCompany($company, $user);

    expect($user->refresh()->maventa_user_id)->toBe('user_flow_001');
    expect($maventaCompanyId)->toBe('company_flow_002');
});
