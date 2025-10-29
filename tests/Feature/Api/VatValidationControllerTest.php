<?php

use App\Models\User;
use DragonBe\Vies\Vies;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

uses()->afterEach(fn() => Mockery::close());

it('returns true for a valid VAT number without caching', function () {
    $viesMock = Mockery::mock(Vies::class);
    $viesMock->shouldReceive('getHeartBeat->isAlive')->andReturn(true);

    $resultMock = Mockery::mock(DragonBe\Vies\CheckVatResponse::class);
    $resultMock->shouldReceive('isValid')->andReturn(true);

    $viesMock->shouldReceive('validateVat')->andReturn($resultMock);

    // Bind de controller in de container met cache disabled
    $this->app->instance(Vies::class, $viesMock);
    $this->app->bind(\App\Http\Controllers\Api\VatValidationController::class, fn($app) =>
        new \App\Http\Controllers\Api\VatValidationController($viesMock, false)
    );

    $response = $this->postJson('/api/validate-vat', [
        'vat_number' => 'BE0123456789',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['valid' => true],
        ]);
});


it('returns false for an invalid VAT number without caching', function () {
    $viesMock = Mockery::mock(Vies::class);
    $viesMock->shouldReceive('getHeartBeat->isAlive')->andReturn(true);

    $resultMock = Mockery::mock(DragonBe\Vies\CheckVatResponse::class);
    $resultMock->shouldReceive('isValid')->andReturn(false);

    $viesMock->shouldReceive('validateVat')->andReturn($resultMock);

    $this->app->instance(Vies::class, $viesMock);
    $this->app->bind(\App\Http\Controllers\Api\VatValidationController::class, fn($app) =>
        new \App\Http\Controllers\Api\VatValidationController($viesMock, false)
    );

    $response = $this->postJson('/api/validate-vat', [
        'vat_number' => 'BE0000000000',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['valid' => false],
        ]);
});
