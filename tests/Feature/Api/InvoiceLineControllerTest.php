<?php

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id]);
    $this->actingAs($this->user);
    $this->invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
});

test('can list all invoice lines for an invoice', function () {
    InvoiceLine::factory()->count(3)->create(['invoice_id' => $this->invoice->id]);

    $response = $this->getJson("/api/invoices/{$this->invoice->id}/invoice-lines");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can create invoice line for an invoice', function () {
    $lineData = [
        'description' => 'New Product',
        'unit_price' => 10000,
        'number' => 2,
        'vat_rate' => 21,
        'vat' => 4200,
        'total' => 20000,
    ];

    $response = $this->postJson("/api/invoices/{$this->invoice->id}/invoice-lines", $lineData);

    $response->assertCreated()
        ->assertJsonPath('data.description', 'New Product')
        ->assertJsonPath('data.unitPrice', 10000)
        ->assertJsonPath('data.number', 2);

    $this->assertDatabaseHas('invoice_lines', [
        'invoice_id' => $this->invoice->id,
        'description' => 'New Product',
        'unit_price' => 10000,
    ]);
});

test('cannot create invoice line for invoice from other company', function () {
    $otherCompany = Company::factory()->create();
    $otherInvoice = Invoice::factory()->create(['company_id' => $otherCompany->id]);

    $lineData = [
        'description' => 'New Product',
        'unit_price' => 10000,
        'number' => 1,
        'vat_rate' => 21,
        'vat' => 2100,
        'total' => 10000,
    ];

    $response = $this->postJson("/api/invoices/{$otherInvoice->id}/invoice-lines", $lineData);

    $response->assertForbidden();
});

test('can show single invoice line', function () {
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $this->invoice->id]);

    $response = $this->getJson("/api/invoice-lines/{$invoiceLine->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $invoiceLine->id)
        ->assertJsonPath('data.description', $invoiceLine->description);
});

test('cannot show invoice line from other company', function () {
    $otherCompany = Company::factory()->create();
    $otherInvoice = Invoice::factory()->create(['company_id' => $otherCompany->id]);
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $otherInvoice->id]);

    $response = $this->getJson("/api/invoice-lines/{$invoiceLine->id}");

    $response->assertForbidden();
});

test('can update invoice line', function () {
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $this->invoice->id]);

    $updateData = [
        'description' => 'Updated Product',
        'unit_price' => 15000,
        'number' => 3,
        'vat_rate' => 21,
        'vat' => 9450,
        'total' => 45000,
    ];

    $response = $this->putJson("/api/invoice-lines/{$invoiceLine->id}", $updateData);

    $response->assertOk()
        ->assertJsonPath('data.description', 'Updated Product')
        ->assertJsonPath('data.unitPrice', 15000);

    $this->assertDatabaseHas('invoice_lines', [
        'id' => $invoiceLine->id,
        'description' => 'Updated Product',
        'unit_price' => 15000,
    ]);
});

test('cannot update invoice line from other company', function () {
    $otherCompany = Company::factory()->create();
    $otherInvoice = Invoice::factory()->create(['company_id' => $otherCompany->id]);
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $otherInvoice->id]);

    $updateData = [
        'description' => 'Updated Product',
        'unit_price' => 15000,
        'number' => 1,
        'vat_rate' => 21,
        'vat' => 3150,
        'total' => 15000,
    ];

    $response = $this->putJson("/api/invoice-lines/{$invoiceLine->id}", $updateData);

    $response->assertForbidden();
});

test('can delete invoice line', function () {
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $this->invoice->id]);

    $response = $this->deleteJson("/api/invoice-lines/{$invoiceLine->id}");

    $response->assertNoContent();
    $this->assertModelMissing($invoiceLine);
});

test('cannot delete invoice line from other company', function () {
    $otherCompany = Company::factory()->create();
    $otherInvoice = Invoice::factory()->create(['company_id' => $otherCompany->id]);
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $otherInvoice->id]);

    $response = $this->deleteJson("/api/invoice-lines/{$invoiceLine->id}");

    $response->assertForbidden();
    $this->assertModelExists($invoiceLine);
});

test('validates required fields when creating invoice line', function () {
    $response = $this->postJson("/api/invoices/{$this->invoice->id}/invoice-lines", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['vat', 'total']);
});

test('validates number must be at least 1', function () {
    $lineData = [
        'description' => 'Product',
        'unit_price' => 10000,
        'number' => 0, // invalid
        'vat_rate' => 21,
        'vat' => 2100,
        'total' => 10000,
    ];

    $response = $this->postJson("/api/invoices/{$this->invoice->id}/invoice-lines", $lineData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['number']);
});

test('validates integer fields', function () {
    $lineData = [
        'description' => 'Product',
        'unit_price' => 'not-an-integer',
        'number' => 'not-an-integer',
        'vat_rate' => 'not-an-integer',
        'vat' => 'not-an-integer',
        'total' => 'not-an-integer',
    ];

    $response = $this->postJson("/api/invoices/{$this->invoice->id}/invoice-lines", $lineData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['unit_price', 'number', 'vat_rate', 'vat', 'total']);
});

test('requires authentication', function () {
    auth()->logout();

    $response = $this->getJson("/api/invoices/{$this->invoice->id}/invoice-lines");

    $response->assertUnauthorized();
});

test('calculates vat correctly', function () {
    $lineData = [
        'description' => 'Product with 21% VAT',
        'unit_price' => 10000, // €100.00
        'number' => 2,
        'vat_rate' => 21,
        'vat' => 4200, // €42.00 (21% van €200)
        'total' => 20000, // €200.00
    ];

    $response = $this->postJson("/api/invoices/{$this->invoice->id}/invoice-lines", $lineData);

    $response->assertCreated();

    $invoiceLine = InvoiceLine::latest()->first();
    expect($invoiceLine->vat)->toBe(4200);
    expect($invoiceLine->total)->toBe(20000);
});
