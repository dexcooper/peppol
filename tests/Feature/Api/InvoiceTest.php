<?php

use App\Models\InvoiceLine;
use App\Models\User;
use App\Models\Company;
use App\Models\Invoice;

use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    // Maak twee bedrijven
    $this->company1 = Company::factory()->create();
    $this->company2 = Company::factory()->create();

    // Maak een user voor company1
    $this->userPassword = 'password123';
    $this->user = User::factory()->for($this->company1)->create([
        'password' => bcrypt($this->userPassword),
    ]);

    // Maak invoices voor beide bedrijven
    Invoice::factory()
        ->count(3)
        ->for($this->company1)
        ->create();

    Invoice::factory()
        ->count(2)
        ->for($this->company2)
        ->create();
});

// ==================== CRUD TESTS ====================

it('kan alleen eigen invoices ophalen', function () {
    $this->actingAs($this->user, 'sanctum');

    $response = getJson('/api/invoices')
        ->assertOk();

    $data = $response->json('data');
    expect(count($data))->toBe(3);

    foreach ($data as $invoice) {
        expect($invoice['company']['id'])->toBe($this->company1->id);
    }
});

it('kan een eigen invoice ophalen', function () {
    $invoice = Invoice::factory()->for($this->company1)->create();

    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoices/{$invoice->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $invoice->id);
});

it('kan geen invoice van andere company ophalen', function () {
    $invoice = Invoice::factory()->for($this->company2)->create();

    $this->actingAs($this->user, 'sanctum');

    getJson("/api/invoices/{$invoice->id}")
        ->assertForbidden();
});

it('kan een invoice aanmaken met willekeurige invoice lines', function () {
    $lines = [];
    $totalAmount = 0;
    $numLines = rand(1,5);

    for ($i = 0; $i < $numLines; $i++) {
        $unitPrice = rand(100,1000);
        $number = rand(1,5);
        $total = $unitPrice * $number;
        $totalAmount += $total;
        $lines[] = [
            'description' => "Product $i",
            'unitPrice' => $unitPrice,
            'number' => $number,
            'totalAmount' => $total,
            'vatRate' => [0,6,9,21][array_rand([0,6,9,21])]
        ];
    }

    $invoiceData = [
        'title' => 'Nieuwe factuur',
        'description' => 'Test factuur',
        'direction' => 'outgoing',
        'status' => 'draft',
        'currency' => 'EUR',
        'issueDate' => now()->toDateString(),
        'dueDate' => now()->addDays(30)->toDateString(),
        'totalAmount' => $totalAmount,
        'invoiceLines' => $lines,
    ];

    $this->actingAs($this->user, 'sanctum');

    $response = postJson('/api/invoices', $invoiceData)
        ->assertCreated();

    $invoiceId = $response->json('data.id');
    $invoice = Invoice::with('invoiceLines')->find($invoiceId);

    expect($invoice->invoiceLines->count())->toBe($numLines);
    expect($invoice->total_amount)->toBe($totalAmount);
});

it('kan een eigen invoice updaten', function () {
    $invoice = Invoice::factory()->for($this->company1)
        ->has(InvoiceLine::factory()->count(3))
        ->create();

    $updateData = [
        'title' => 'Gewijzigd',
        'description' => $invoice->description,
        'direction' => $invoice->direction,
        'status' => $invoice->status,
        'currency' => $invoice->currency,
        'total_amount' => $invoice->total_amount,
        'issue_date' => \Carbon\Carbon::parse($invoice->issue_date)->toDateString(),
        'due_date' => \Carbon\Carbon::parse($invoice->due_date)->toDateString(),
        'invoice_lines' => $invoice->invoiceLines->map(fn($line) => [
            'id' => $line->id,
            'description' => $line->description,
            'unit_price' => $line->unit_price,
            'number' => $line->number,
            'total_amount' => $line->total_amount,
            'vat_rate' => $line->vat_rate,
        ])->toArray(),
    ];

    $this->actingAs($this->user, 'sanctum');

    putJson("/api/invoices/{$invoice->id}", $updateData)
        ->assertOk()
        ->assertJsonPath('data.title', 'Gewijzigd');

    expect($invoice->fresh()->title)->toBe('Gewijzigd');
    $invoice = $invoice->fresh();
    $totalAmount = $invoice->invoiceLines->sum(fn($l) => $l->total_amount);
    expect($invoice->total_amount)->toBe($totalAmount);
});

it('kan geen invoice van andere company updaten', function () {
    $invoice = Invoice::factory()->for($this->company2)->create();

    $this->actingAs($this->user, 'sanctum');

    putJson("/api/invoices/{$invoice->id}", ['title' => 'Hacked'])
        ->assertForbidden();
});

it('kan een eigen invoice verwijderen', function () {
    $invoice = Invoice::factory()->for($this->company1)->create();

    $this->actingAs($this->user, 'sanctum');

    deleteJson("/api/invoices/{$invoice->id}", [])
        ->assertNoContent();

    expect(Invoice::find($invoice->id))->toBeNull();
});

it('kan geen invoice van andere company verwijderen', function () {
    $invoice = Invoice::factory()->for($this->company2)->create();

    $this->actingAs($this->user, 'sanctum');

    deleteJson("/api/invoices/{$invoice->id}", [])
        ->assertForbidden();
});
