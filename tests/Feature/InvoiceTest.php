<?php

use App\Enums\Currency;
use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Brick\Money\Money;

test('can get money attribute from total_amount', function () {
    $invoice = Invoice::factory()->create([
        'currency' => Currency::EUR,
    ]);

    $money = $invoice->money;

    expect($money)->toBeInstanceOf(Money::class);
    expect($money->getMinorAmount()->toInt())->toBe($invoice->total);
    expect($money->getCurrency()->getCurrencyCode())->toBe('EUR');
});

test('has invoice lines relationship', function () {
    $invoice = Invoice::factory()->create();
    InvoiceLine::factory()->count(3)->create(['invoice_id' => $invoice->id]);

    expect($invoice->invoiceLines)->toHaveCount(3);
    expect($invoice->invoiceLines->first())->toBeInstanceOf(InvoiceLine::class);
});

test('has company relationship', function () {
    $company = Company::factory()->create();
    $invoice = Invoice::factory()->create(['company_id' => $company->id]);

    expect($invoice->company)->toBeInstanceOf(Company::class);
    expect($invoice->company->id)->toBe($company->id);
});

test('calculates total vat from invoice lines', function () {
    $invoice = Invoice::factory()->create();

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'vat' => 2100, // €21.00
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'vat' => 1050, // €10.50
    ]);

    expect($invoice->vat)->toBe(3150); // €31.50
});

test('calculates total from invoice lines', function () {
    $invoice = Invoice::factory()->create();

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'total' => 10000, // €100.00
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'total' => 5000, // €50.00
    ]);

    expect($invoice->total)->toBe(15000); // €150.00
});

test('has default status of draft', function () {
    // Note: Dit test de default waarde in de fillable array
    $invoice = new Invoice([
        'company_id' => 1,
        'title' => 'Test',
        'external_id' => 'EXT-001',
        'direction' => 'incoming',
        'issue_date' => '2025-01-01',
        'due_date' => '2025-02-01',
        'currency' => Currency::EUR,
    ]);

    // Default status zou Draft moeten zijn
    expect($invoice->status)->toBe(InvoiceStatus::Draft);
});

test('fillable attributes include all expected fields', function () {
    $invoice = new Invoice();

    $fillable = $invoice->getFillable();

    expect($fillable)->toContain('company_id');
    expect($fillable)->toContain('external_id');
    expect($fillable)->toContain('title');
    expect($fillable)->toContain('description');
    expect($fillable)->toContain('direction');
    expect($fillable)->toContain('peppol_id');
    expect($fillable)->toContain('status');
    expect($fillable)->toContain('issue_date');
    expect($fillable)->toContain('due_date');
    expect($fillable)->toContain('currency');
    expect($fillable)->toContain('raw_xml');
});

test('casts direction to enum', function () {
    $invoice = Invoice::factory()->create([
        'direction' => 'incoming',
    ]);

    expect($invoice->direction)->toBeInstanceOf(\App\Enums\InvoiceDirection::class);
});

test('casts status to enum', function () {
    $invoice = Invoice::factory()->create([
        'status' => 'draft',
    ]);

    expect($invoice->status)->toBeInstanceOf(InvoiceStatus::class);
});

test('casts currency to enum', function () {
    $invoice = Invoice::factory()->create([
        'currency' => 'EUR',
    ]);

    expect($invoice->currency)->toBeInstanceOf(Currency::class);
});

test('can create invoice with all required fields', function () {
    $company = Company::factory()->create();

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'external_id' => 'INV-001',
        'title' => 'Test Invoice',
        'description' => 'Test Description',
        'direction' => 'incoming',
        'status' => 'draft',
        'issue_date' => '2025-01-01',
        'due_date' => '2025-02-01',
        'currency' => 'EUR',
    ]);

    expect($invoice)->toBeInstanceOf(Invoice::class);
    expect($invoice->title)->toBe('Test Invoice');
    expect($invoice->external_id)->toBe('INV-001');
});

test('invoice with no lines has zero vat', function () {
    $invoice = Invoice::factory()->create();

    expect($invoice->vat)->toBe(0);
});

test('invoice with no lines has zero total', function () {
    $invoice = Invoice::factory()->create();

    expect($invoice->total)->toBe(0);
});
