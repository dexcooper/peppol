<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;

use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    // Maak bedrijven
    $this->company1 = Company::factory()->create();
    $this->company2 = Company::factory()->create();

    // Maak user voor company1
    $this->userPassword = 'password123';
    $this->user = User::factory()->for($this->company1)->create([
        'password' => bcrypt($this->userPassword)
    ]);

    // Maak invoices
    $this->invoice1 = Invoice::factory()->for($this->company1)->create();
    $this->invoice2 = Invoice::factory()->for($this->company2)->create();
});

// ==================== CRUD TESTS ====================

it('kan alle invoice lines van eigen invoice ophalen', function () {
    InvoiceLine::factory()->count(3)->for($this->invoice1)->create();
    InvoiceLine::factory()->count(2)->for($this->invoice2)->create();

    $this->actingAs($this->user, 'sanctum');

    $response = getJson("/api/invoices/{$this->invoice1->id}/invoice-lines")
        ->assertOk();

    $lines = $response->json('data');
    expect(count($lines))->toBe(3);

    foreach ($lines as $line) {
        $invoiceLine = InvoiceLine::find($line['id']);
        expect($invoiceLine->invoice_id)->toBe($this->invoice1->id);
    }
});

it('kan geen invoice lines van andere invoice ophalen', function () {
    InvoiceLine::factory()->count(2)->for($this->invoice2)->create();

    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoices/{$this->invoice2->id}/invoice-lines")
        ->assertForbidden();
});

it('kan een invoice line aanmaken', function () {
    $lineData = [
        'description' => 'Test product',
        'unit_price' => 500,
        'number' => 2,
        'total_amount' => 1000,
        'vat_rate' => 21
    ];

    $this->actingAs($this->user, 'sanctum');

    $response = postJson("/api/invoices/{$this->invoice1->id}/invoice-lines", $lineData)
        ->assertCreated();

    $lineId = $response->json('data.id');
    $line = InvoiceLine::find($lineId);

    expect($line->description)->toBe('Test product');
    expect($line->invoice_id)->toBe($this->invoice1->id);
});

it('kan geen invoice line voor een andere invoice aanmaken', function () {
    $lineData = [
        'description' => 'Test product',
        'unit_price' => 500,
        'number' => 2,
        'total_amount' => 1000,
        'vat_rate' => 21
    ];

    $this->actingAs($this->user, 'sanctum');

    $response = postJson("/api/invoices/{$this->invoice2->id}/invoice-lines", $lineData)
        ->assertForbidden();
});

it('kan een eigen invoice line ophalen', function () {
    $line = InvoiceLine::factory()->for($this->invoice1)->create();

    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoice-lines/{$line->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $line->id);
});

it('kan geen invoice line van andere invoice ophalen', function () {
    $line = InvoiceLine::factory()->for($this->invoice2)->create();

    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoice-lines/{$line->id}")
        ->assertForbidden();
});

it('kan een eigen invoice line updaten', function () {
    $line = InvoiceLine::factory()->for($this->invoice1)->create();

    $updateData = [
        'description' => 'Gewijzigd',
        'unit_price' => $line->unit_price,
        'number' => $line->number,
        'vat_rate' => $line->vat_rate,
        'total_amount' => $line->total_amount,
    ];

    $this->actingAs($this->user, 'sanctum');

    putJson("/api/invoice-lines/{$line->id}", $updateData)
        ->assertOk()
        ->assertJsonPath('data.description', 'Gewijzigd');

    expect($line->fresh()->description)->toBe('Gewijzigd');
});

it('kan geen invoice line van andere invoice updaten', function () {
    $line = InvoiceLine::factory()->for($this->invoice2)->create();

    $this->actingAs($this->user, 'sanctum');

    putJson("/api/invoice-lines/{$line->id}", [
        'description' => 'Hacked',
        'unit_price' => $line->unit_price,
        'number' => $line->number,
        'vat_rate' => $line->vat_rate,
        'total_amount' => $line->total_amount,
    ])
        ->assertForbidden();
});

it('kan een eigen invoice line verwijderen', function () {
    $line = InvoiceLine::factory()->for($this->invoice1)->create();

    $this->actingAs($this->user, 'sanctum');

    deleteJson("/api/invoice-lines/{$line->id}", [])
        ->assertNoContent();

    expect(InvoiceLine::find($line->id))->toBeNull();
});

it('kan geen invoice line van andere invoice verwijderen', function () {
    $line = InvoiceLine::factory()->for($this->invoice2)->create();

    $this->actingAs($this->user, 'sanctum');

    deleteJson("/api/invoice-lines/{$line->id}", [])
        ->assertForbidden();
});



it('faalt bij het aanmaken van invoice line zonder verplichte velden', function () {
    $this->actingAs($this->user, 'sanctum');

    postJson("/api/invoices/{$this->invoice1->id}/invoice-lines", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unit_price', 'number', 'total_amount', 'vat_rate']);
});

it('faalt bij negatieve unit_price', function () {
    $lineData = [
        'description' => 'Test product',
        'unit_price' => -100,
        'number' => 2,
        'total_amount' => 1000,
        'vat_rate' => 21
    ];

    $this->actingAs($this->user, 'sanctum');

    postJson("/api/invoices/{$this->invoice1->id}/invoice-lines", $lineData)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unit_price']);
});


it('faalt bij number kleiner dan 1', function () {
    $lineData = [
        'description' => 'Test product',
        'unit_price' => 500,
        'number' => 0,
        'total_amount' => 1000,
        'vat_rate' => 21
    ];

    $this->actingAs($this->user, 'sanctum');

    postJson("/api/invoices/{$this->invoice1->id}/invoice-lines", $lineData)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['number']);
});

it('faalt bij negatieve vat_rate', function () {
    $lineData = [
        'description' => 'Test product',
        'unit_price' => 500,
        'number' => 2,
        'total_amount' => 1000,
        'vat_rate' => -5
    ];

    $this->actingAs($this->user, 'sanctum');

    postJson("/api/invoices/{$this->invoice1->id}/invoice-lines", $lineData)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['vat_rate']);
});

it('herberekent invoice totaal bij het toevoegen van invoice line', function () {
    $initialTotal = $this->invoice1->total_amount;

    $lineData = [
        'description' => 'Test product',
        'unit_price' => 500,
        'number' => 2,
        'total_amount' => 1000,
        'vat_rate' => 21
    ];

    $this->actingAs($this->user, 'sanctum');

    postJson("/api/invoices/{$this->invoice1->id}/invoice-lines", $lineData)
        ->assertCreated();

    $this->invoice1->refresh();
    expect($this->invoice1->total_amount)->toBe($initialTotal + 1000);
});

it('herberekent invoice totaal bij het updaten van invoice line', function () {
    $line = InvoiceLine::factory()->for($this->invoice1)->create([
        'total_amount' => 500
    ]);
    $initialTotal = $this->invoice1->fresh()->total_amount;

    $updateData = [
        'description' => $line->description,
        'unit_price' => $line->unit_price,
        'number' => $line->number,
        'vat_rate' => $line->vat_rate,
        'total_amount' => 1000, // Change from 500 to 1000
    ];

    $this->actingAs($this->user, 'sanctum');

    putJson("/api/invoice-lines/{$line->id}", $updateData)
        ->assertOk();

    $this->invoice1->refresh();
    expect($this->invoice1->total_amount)->toBe($initialTotal + 500); // +500 difference
});

it('herberekent invoice totaal bij het verwijderen van invoice line', function () {
    $line = InvoiceLine::factory()->for($this->invoice1)->create([
        'total_amount' => 500
    ]);
    $initialTotal = $this->invoice1->fresh()->total_amount;

    $this->actingAs($this->user, 'sanctum');

    deleteJson("/api/invoice-lines/{$line->id}", [])
        ->assertNoContent();

    $this->invoice1->refresh();
    expect($this->invoice1->total_amount)->toBe($initialTotal - 500);
});

it('faalt bij het ophalen van invoice lines voor niet-bestaande invoice', function () {
    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoices/999999/invoice-lines")
        ->assertNotFound();
});

it('faalt bij het aanmaken van invoice line voor niet-bestaande invoice', function () {
    $lineData = [
        'description' => 'Test product',
        'unit_price' => 500,
        'number' => 2,
        'total_amount' => 1000,
        'vat_rate' => 21
    ];

    $this->actingAs($this->user, 'sanctum');

    postJson("/api/invoices/999999/invoice-lines", $lineData)
        ->assertNotFound();
});

it('faalt bij het ophalen van niet-bestaande invoice line', function () {
    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoice-lines/999999")
        ->assertNotFound();
});

it('kan lege invoice lines list ophalen', function () {
    $this->actingAs($this->user, 'sanctum');

    $response = getJson("/api/invoices/{$this->invoice1->id}/invoice-lines")
        ->assertOk();

    $lines = $response->json('data');
    expect(count($lines))->toBe(0);
});

it('bewaart correcte data types in database', function () {
    $lineData = [
        'description' => 'Test product',
        'unit_price' => 500,
        'number' => 2,
        'total_amount' => 1000,
        'vat_rate' => 21
    ];

    $this->actingAs($this->user, 'sanctum');

    $response = postJson("/api/invoices/{$this->invoice1->id}/invoice-lines", $lineData)
        ->assertCreated();

    $lineId = $response->json('data.id');
    $line = InvoiceLine::find($lineId);

    expect($line->unit_price)->toBeInt();
    expect($line->number)->toBeInt();
    expect($line->total_amount)->toBeInt();
    expect($line->vat_rate)->toBeInt();
    expect($line->description)->toBeString();
    expect($line->invoice_id)->toBe($this->invoice1->id);
});

it('response bevat alle verwachte velden', function () {
    $line = InvoiceLine::factory()->for($this->invoice1)->create();

    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoice-lines/{$line->id}")
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'description',
                'unitPrice',
                'number',
                'totalAmount',
                'vatRate'
            ]
        ]);
});

it('faalt zonder authentication token', function () {
    getJson("/api/invoices/{$this->invoice1->id}/invoice-lines")
        ->assertUnauthorized();
});

it('faalt met ongeldig authentication token', function () {
    getJson("/api/invoices/{$this->invoice1->id}/invoice-lines", [
        'Authorization' => 'Bearer invalid-token'
    ])->assertUnauthorized();
});
