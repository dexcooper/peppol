<?php

use App\Enums\Currency;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Brick\Money\Money;

test('can get money attribute from total', function () {
    $invoice = Invoice::factory()->create(['currency' => Currency::EUR]);
    $invoiceLine = InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'total' => 10000, // €100.00
    ]);

    $money = $invoiceLine->money;

    expect($money)->toBeInstanceOf(Money::class);
    expect($money->getMinorAmount()->toInt())->toBe(10000);
    expect($money->getCurrency()->getCurrencyCode())->toBe('EUR');
});

test('can set money attribute', function () {
    $invoice = Invoice::factory()->create(['currency' => Currency::EUR]);
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);

    $money = Money::ofMinor(25000, 'EUR'); // €250.00
    $invoiceLine->money = $money;
    $invoiceLine->save();

    expect($invoiceLine->fresh()->total)->toBe(25000);
});

test('inherits currency from parent invoice', function () {
    $invoice = Invoice::factory()->create(['currency' => Currency::USD]);
    $invoiceLine = InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'total' => 10000,
    ]);

    $money = $invoiceLine->money;

    expect($money->getCurrency()->getCurrencyCode())->toBe('USD');
});

test('throws exception when setting money with disallowed currency', function () {
    $invoice = Invoice::factory()->create(['currency' => Currency::EUR]);
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);

    $money = Money::ofMinor(10000, 'JPY'); // Yen is not allowed

    expect(fn() => $invoiceLine->money = $money)
        ->toThrow(InvalidArgumentException::class, 'Currency not allowed');
});

test('has invoice relationship', function () {
    $invoice = Invoice::factory()->create();
    $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);

    expect($invoiceLine->invoice)->toBeInstanceOf(Invoice::class);
    expect($invoiceLine->invoice->id)->toBe($invoice->id);
});

test('fillable attributes include all expected fields', function () {
    $invoiceLine = new InvoiceLine();

    $fillable = $invoiceLine->getFillable();

    expect($fillable)->toContain('invoice_id');
    expect($fillable)->toContain('description');
    expect($fillable)->toContain('unit_price');
    expect($fillable)->toContain('number');
    expect($fillable)->toContain('vat_rate');
    expect($fillable)->toContain('vat');
    expect($fillable)->toContain('total');
});

test('can create invoice line with all fields', function () {
    $invoice = Invoice::factory()->create();

    $invoiceLine = InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Test Product',
        'unit_price' => 10000,
        'number' => 2,
        'vat_rate' => 21,
        'vat' => 4200,
        'total' => 20000,
    ]);

    expect($invoiceLine)->toBeInstanceOf(InvoiceLine::class);
    expect($invoiceLine->description)->toBe('Test Product');
    expect($invoiceLine->unit_price)->toBe(10000);
    expect($invoiceLine->number)->toBe(2);
    expect($invoiceLine->vat_rate)->toBe(21);
    expect($invoiceLine->vat)->toBe(4200);
    expect($invoiceLine->total)->toBe(20000);
});

test('calculates correct vat for 21 percent rate', function () {
    $invoice = Invoice::factory()->create();

    $invoiceLine = InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Product',
        'unit_price' => 10000, // €100.00
        'number' => 1,
        'vat_rate' => 21,
        'vat' => 2100, // €21.00
        'total' => 10000,
    ]);

    // VAT should be 21% of total
    expect($invoiceLine->vat)->toBe(2100);
    expect($invoiceLine->vat / $invoiceLine->total)->toBe(0.21);
});

test('calculates correct vat for 6 percent rate', function () {
    $invoice = Invoice::factory()->create();

    $invoiceLine = InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Product',
        'unit_price' => 10000, // €100.00
        'number' => 1,
        'vat_rate' => 6,
        'vat' => 600, // €6.00
        'total' => 10000,
    ]);

    expect($invoiceLine->vat)->toBe(600);
    expect($invoiceLine->vat / $invoiceLine->total)->toBe(0.06);
});

test('calculates correct vat for zero percent rate', function () {
    $invoice = Invoice::factory()->create();

    $invoiceLine = InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Product',
        'unit_price' => 10000,
        'number' => 1,
        'vat_rate' => 0,
        'vat' => 0,
        'total' => 10000,
    ]);

    expect($invoiceLine->vat)->toBe(0);
});

test('calculates correct total for multiple items', function () {
    $invoice = Invoice::factory()->create();

    $invoiceLine = InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Product',
        'unit_price' => 5000, // €50.00
        'number' => 3,
        'vat_rate' => 21,
        'vat' => 3150, // €31.50
        'total' => 15000, // €150.00
    ]);

    expect($invoiceLine->total)->toBe(15000);
    expect($invoiceLine->unit_price * $invoiceLine->number)->toBe(15000);
});

test('can have zero unit price', function () {
    $invoice = Invoice::factory()->create();

    $invoiceLine = InvoiceLine::create([
        'invoice_id' => $invoice->id,
        'description' => 'Free Product',
        'unit_price' => 0,
        'number' => 1,
        'vat_rate' => 0,
        'vat' => 0,
        'total' => 0,
    ]);

    expect($invoiceLine->unit_price)->toBe(0);
    expect($invoiceLine->total)->toBe(0);
});
