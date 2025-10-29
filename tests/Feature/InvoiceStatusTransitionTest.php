<?php

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatusHistory;
use App\Models\User;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id]);
    $this->actingAs($this->user);
});


test('can transition from draft to queued', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::QUEUED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::QUEUED);
});

test('can transition from draft to failed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::FAILED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::FAILED);
});

test('can transition from queued to sent', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::QUEUED,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::SENT);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::SENT);
});

test('can transition from queued to failed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::QUEUED,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::FAILED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::FAILED);
});

test('can transition from sent to delivered', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::SENT,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::DELIVERED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::DELIVERED);
});

test('can transition from sent to failed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::SENT,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::FAILED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::FAILED);
});

test('can transition from failed to queued for retry', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::FAILED,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::QUEUED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::QUEUED);
});

test('can transition from received to processed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::RECEIVED,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::PROCESSED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::PROCESSED);
});

test('can transition from processed to archived', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::PROCESSED,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::ARCHIVED);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::ARCHIVED);
});

test('cannot transition from draft to delivered', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::DELIVERED))
        ->toThrow(Exception::class, 'Invalid status transition');

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::DRAFT);
});

test('cannot transition from draft to sent', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::SENT))
        ->toThrow(Exception::class);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::DRAFT);
});

test('cannot transition from delivered to any status', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DELIVERED,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::FAILED))
        ->toThrow(Exception::class);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::DELIVERED);
});

test('cannot transition from archived to any status', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::ARCHIVED,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::PROCESSED))
        ->toThrow(Exception::class);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::ARCHIVED);
});

test('creates status history when transitioning', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    $invoice->updateStatus(InvoiceStatus::QUEUED);

    $this->assertDatabaseHas('invoice_status_histories', [
        'invoice_id' => $invoice->id,
        'from_status' => InvoiceStatus::DRAFT->value,
        'to_status' => InvoiceStatus::QUEUED->value,
        'changed_by' => $this->user->id,
    ]);
});

test('tracks multiple status changes', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    $invoice->updateStatus(InvoiceStatus::QUEUED);
    $invoice->updateStatus(InvoiceStatus::SENT);
    $invoice->updateStatus(InvoiceStatus::DELIVERED);

    $histories = InvoiceStatusHistory::where('invoice_id', $invoice->id)->get();

    expect($histories)->toHaveCount(3);
    expect($histories[0]->from_status)->toBe(InvoiceStatus::DRAFT->value);
    expect($histories[0]->to_status)->toBe(InvoiceStatus::QUEUED->value);
    expect($histories[1]->from_status)->toBe(InvoiceStatus::QUEUED->value);
    expect($histories[1]->to_status)->toBe(InvoiceStatus::SENT->value);
    expect($histories[2]->from_status)->toBe(InvoiceStatus::SENT->value);
    expect($histories[2]->to_status)->toBe(InvoiceStatus::DELIVERED->value);
});

test('does not create history when transition fails', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    try {
        $invoice->updateStatus(InvoiceStatus::DELIVERED);
    } catch (Exception $e) {
        // Expected exception
    }

    $this->assertDatabaseMissing('invoice_status_histories', [
        'invoice_id' => $invoice->id,
    ]);
});

test('retry flow from failed back to queued', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    // Normale flow tot failed
    $invoice->updateStatus(InvoiceStatus::QUEUED);
    $invoice->updateStatus(InvoiceStatus::FAILED);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::FAILED);

    // Retry
    $invoice->updateStatus(InvoiceStatus::QUEUED);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::QUEUED);

    // Kan nu verder
    $invoice->updateStatus(InvoiceStatus::SENT);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::SENT);
});

test('complete outgoing invoice flow', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::DRAFT,
    ]);

    $invoice->updateStatus(InvoiceStatus::QUEUED);
    $invoice->updateStatus(InvoiceStatus::SENT);
    $invoice->updateStatus(InvoiceStatus::DELIVERED);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::DELIVERED);

    $histories = InvoiceStatusHistory::where('invoice_id', $invoice->id)->get();
    expect($histories)->toHaveCount(3);
});

test('complete incoming invoice flow', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::RECEIVED,
    ]);

    $invoice->updateStatus(InvoiceStatus::PROCESSED);
    $invoice->updateStatus(InvoiceStatus::ARCHIVED);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::ARCHIVED);

    $histories = InvoiceStatusHistory::where('invoice_id', $invoice->id)->get();
    expect($histories)->toHaveCount(2);
});

