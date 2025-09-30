<?php

use App\Models\Company;
use App\Models\User;
use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    // Maak een bedrijf
    $this->company1 = Company::factory()->create();

    // Maak een user voor company1
    $this->userPassword = 'password123';
    $this->user = User::factory()->for($this->company1)->create([
        'password' => bcrypt($this->userPassword),
    ]);
});

it('kan inloggen met geldige credentials', function () {
    $response = postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->userPassword
    ]);

    $response->assertOk()
             ->assertJsonStructure(['data' => ['token']]);
});

it('faalt bij ongeldige credentials', function () {
    postJson('/api/login', [
        'email' => $this->user->email,
        'password' => 'wrongpassword'
    ])->assertUnauthorized();
});
