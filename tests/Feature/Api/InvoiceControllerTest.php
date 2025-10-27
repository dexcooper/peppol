<?php

use App\Enums\Currency;
use App\Enums\InvoiceDirection;
use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id]);
    $this->actingAs($this->user);
});

test('can list all invoices for authenticated user company', function () {
    Invoice::factory()->count(3)->create(['company_id' => $this->company->id]);
    Invoice::factory()->count(2)->create(); // andere company

    $response = $this->getJson('/api/invoices');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can create invoice with invoice lines', function () {
    $invoiceData = [
        'title' => 'Test Invoice',
        'external_id' => 'INV-001',
        'vat_number' => 'BE1234',
        'description' => 'Test description',
        'direction' => InvoiceDirection::Incoming->value,
        'issue_date' => '2025-01-01',
        'due_date' => '2025-02-01',
        'currency' => Currency::EUR->value,
        'invoice_lines' => [
            [
                'description' => 'Product 1',
                'unit_price' => 10000,
                'number' => 2,
                'vat_rate' => 21,
                'vat' => 4200,
                'total' => 20000,
            ],
            [
                'description' => 'Product 2',
                'unit_price' => 5000,
                'number' => 1,
                'vat_rate' => 21,
                'vat' => 1050,
                'total' => 5000,
            ],
        ],
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Test Invoice')
        ->assertJsonPath('data.externalId', 'INV-001')
        ->assertJsonCount(2, 'data.invoiceLines');

    $this->assertDatabaseHas('invoices', [
        'title' => 'Test Invoice',
        'company_id' => $this->company->id,
    ]);

    $this->assertDatabaseHas('invoice_lines', [
        'description' => 'Product 1',
        'unit_price' => 10000,
    ]);
});

test('can show single invoice', function () {
    $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $invoice->id)
        ->assertJsonPath('data.title', $invoice->title);
});

test('cannot show invoice from other company', function () {
    $otherCompany = Company::factory()->create();
    $invoice = Invoice::factory()->create(['company_id' => $otherCompany->id]);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertForbidden();
});

test('can update invoice', function () {
    $invoice = Invoice::factory()
        ->for($this->company)
        ->has(InvoiceLine::factory()->count(3))
        ->create(['company_id' => $this->company->id]);

    $updateData = [
        'title' => 'Updated Invoice',
        'external_id' => $invoice->external_id,
        'vat_number' => $invoice->vat_number,
        'description' => 'Updated description',
        'direction' => $invoice->direction->value,
        'issue_date' => $invoice->issue_date,
        'due_date' => $invoice->due_date,
        'currency' => $invoice->currency->value,
        'invoice_lines' => $invoice->invoiceLines->map(fn($line) => [
            'id' => $line->id,
            'description' => $line->description,
            'unit_price' => $line->unit_price,
            'number' => $line->number,
            'vat_rate' => $line->vat_rate,
            'vat' => $line->vat,
            'total' => $line->total,
        ])->toArray(),
    ];

    $response = $this->putJson("/api/invoices/{$invoice->id}", $updateData);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated Invoice');

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'title' => 'Updated Invoice',
    ]);
});

test('can update invoice with new invoice lines', function () {
    $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
    $invoice->invoiceLines()->createMany([
        ['description' => 'Old line 1', 'unit_price' => 1000, 'number' => 1, 'vat_rate' => 21, 'vat' => 210, 'total' => 1000],
        ['description' => 'Old line 2', 'unit_price' => 2000, 'number' => 1, 'vat_rate' => 21, 'vat' => 420, 'total' => 2000],
    ]);

    $updateData = [
        'title' => $invoice->title,
        'external_id' => $invoice->external_id,
        'vat_number' => $invoice->vat_number,
        'description' => $invoice->description,
        'direction' => $invoice->direction->value,
        'issue_date' => $invoice->issue_date,
        'due_date' => $invoice->due_date,
        'currency' => $invoice->currency->value,
        'invoice_lines' => [
            [
                'description' => 'New line',
                'unit_price' => 5000,
                'number' => 1,
                'vat_rate' => 21,
                'vat' => 1050,
                'total' => 5000,
            ],
        ],
    ];

    $response = $this->putJson("/api/invoices/{$invoice->id}", $updateData);

    $response->assertOk();

    $this->assertDatabaseMissing('invoice_lines', ['description' => 'Old line 1']);
    $this->assertDatabaseMissing('invoice_lines', ['description' => 'Old line 2']);
    $this->assertDatabaseHas('invoice_lines', ['description' => 'New line']);
});

test('cannot update invoice from other company', function () {
    $otherCompany = Company::factory()->create();
    $invoice = Invoice::factory()->create(['company_id' => $otherCompany->id]);

    $updateData = [
        'title' => 'Updated Invoice',
        'external_id' => $invoice->external_id,
        'vat_number' => $invoice->vat_number,
        'description' => 'Updated description',
        'direction' => $invoice->direction->value,
        'issue_date' => $invoice->issue_date,
        'due_date' => $invoice->due_date,
        'currency' => $invoice->currency->value,
    ];

    $response = $this->putJson("/api/invoices/{$invoice->id}", $updateData);

    $response->assertForbidden();
});

test('cannot update peppol_id en status on invoice', function () {
    $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
    $invoice->invoiceLines()->createMany([
        ['description' => 'Old line 1', 'unit_price' => 1000, 'number' => 1, 'vat_rate' => 21, 'vat' => 210, 'total' => 1000],
        ['description' => 'Old line 2', 'unit_price' => 2000, 'number' => 1, 'vat_rate' => 21, 'vat' => 420, 'total' => 2000],
    ]);

    $oldPeppolId = $invoice->peppol_id;
    $oldInvoiceStatus = $invoice->invoice_status;

    $updateData = [
        'title' => $invoice->title,
        'external_id' => $invoice->external_id,
        'vat_number' => $invoice->vat_number,
        'description' => $invoice->description,
        'direction' => $invoice->direction->value,
        'issue_date' => $invoice->issue_date,
        'due_date' => $invoice->due_date,
        'currency' => $invoice->currency->value,
        'invoice_lines' => [
            [
                'description' => 'New line',
                'unit_price' => 5000,
                'number' => 1,
                'vat_rate' => 21,
                'vat' => 1050,
                'total' => 5000,
            ],
        ],
    ];

    $this->putJson("/api/invoices/{$invoice->id}", $updateData)
        ->assertOk();

    expect($invoice->fresh()->peppol_id)->toBe($oldPeppolId);
    expect($invoice->fresh()->invoice_status)->toBe($oldInvoiceStatus);
});

test('can delete invoice', function () {
    $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");

    $response->assertNoContent();
    $this->assertModelMissing($invoice);
});

test('cannot delete invoice from other company', function () {
    $otherCompany = Company::factory()->create();
    $invoice = Invoice::factory()->create(['company_id' => $otherCompany->id]);

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");

    $response->assertForbidden();
    $this->assertModelExists($invoice);
});

test('validates required fields when creating invoice', function () {
    $response = $this->postJson('/api/invoices', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'external_id', 'direction', 'issue_date', 'due_date', 'currency', 'invoice_lines']);
});

test('validates invoice_lines must have at least one item', function () {
    $invoiceData = [
        'title' => 'Test Invoice',
        'external_id' => 'INV-001',
        'direction' => InvoiceDirection::Incoming->value,
        'issue_date' => '2025-01-01',
        'due_date' => '2025-02-01',
        'currency' => Currency::EUR->value,
        'invoice_lines' => [],
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['invoice_lines']);
});

test('validates date format', function () {
    $invoiceData = [
        'title' => 'Test Invoice',
        'external_id' => 'INV-001',
        'direction' => InvoiceDirection::Incoming->value,
        'issue_date' => '01-01-2025', // verkeerd formaat
        'due_date' => '2025/02/01', // verkeerd formaat
        'currency' => Currency::EUR->value,
        'invoice_lines' => [
            [
                'description' => 'Product 1',
                'unit_price' => 10000,
                'number' => 1,
                'vat_rate' => 21,
                'vat' => 2100,
                'total' => 10000,
            ],
        ],
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['issue_date', 'due_date']);
});

test('validates enum values', function () {
    $invoiceData = [
        'title' => 'Test Invoice',
        'external_id' => 'INV-001',
        'direction' => 'invalid_direction',
        'issue_date' => '2025-01-01',
        'due_date' => '2025-02-01',
        'currency' => 'INVALID',
        'invoice_lines' => [
            [
                'description' => 'Product 1',
                'unit_price' => 10000,
                'number' => 1,
                'vat_rate' => 21,
                'vat' => 2100,
                'total' => 10000,
            ],
        ],
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['direction', 'currency']);
});

test('requires authentication', function () {
    auth()->logout();

    $response = $this->getJson('/api/invoices');

    $response->assertUnauthorized();
});
