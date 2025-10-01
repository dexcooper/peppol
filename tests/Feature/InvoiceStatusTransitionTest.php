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
        'status' => InvoiceStatus::Draft,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Queued);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Queued);
});

test('can transition from draft to failed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Failed);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Failed);
});

test('can transition from queued to sent', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Queued,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Sent);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Sent);
});

test('can transition from queued to failed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Queued,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Failed);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Failed);
});

test('can transition from sent to delivered', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Sent,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Delivered);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Delivered);
});

test('can transition from sent to failed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Sent,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Failed);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Failed);
});

test('can transition from failed to queued for retry', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Failed,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Queued);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Queued);
});

test('can transition from received to processed', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Received,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Processed);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Processed);
});

test('can transition from processed to archived', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Processed,
    ]);

    $result = $invoice->updateStatus(InvoiceStatus::Archived);

    expect($result)->toBeTrue();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Archived);
});

test('cannot transition from draft to delivered', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Draft,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::Delivered))
        ->toThrow(Exception::class, 'Invalid status transition');

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Draft);
});

test('cannot transition from draft to sent', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Draft,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::Sent))
        ->toThrow(Exception::class);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Draft);
});

test('cannot transition from delivered to any status', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Delivered,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::Failed))
        ->toThrow(Exception::class);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Delivered);
});

test('cannot transition from archived to any status', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Archived,
    ]);

    expect(fn() => $invoice->updateStatus(InvoiceStatus::Processed))
        ->toThrow(Exception::class);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Archived);
});

test('creates status history when transitioning', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $invoice->updateStatus(InvoiceStatus::Queued);

    $this->assertDatabaseHas('invoice_status_histories', [
        'invoice_id' => $invoice->id,
        'from_status' => InvoiceStatus::Draft->value,
        'to_status' => InvoiceStatus::Queued->value,
        'changed_by' => $this->user->id,
    ]);
});

test('tracks multiple status changes', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $invoice->updateStatus(InvoiceStatus::Queued);
    $invoice->updateStatus(InvoiceStatus::Sent);
    $invoice->updateStatus(InvoiceStatus::Delivered);

    $histories = InvoiceStatusHistory::where('invoice_id', $invoice->id)->get();

    expect($histories)->toHaveCount(3);
    expect($histories[0]->from_status)->toBe(InvoiceStatus::Draft->value);
    expect($histories[0]->to_status)->toBe(InvoiceStatus::Queued->value);
    expect($histories[1]->from_status)->toBe(InvoiceStatus::Queued->value);
    expect($histories[1]->to_status)->toBe(InvoiceStatus::Sent->value);
    expect($histories[2]->from_status)->toBe(InvoiceStatus::Sent->value);
    expect($histories[2]->to_status)->toBe(InvoiceStatus::Delivered->value);
});

test('does not create history when transition fails', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Draft,
    ]);

    try {
        $invoice->updateStatus(InvoiceStatus::Delivered);
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
        'status' => InvoiceStatus::Draft,
    ]);

    // Normale flow tot failed
    $invoice->updateStatus(InvoiceStatus::Queued);
    $invoice->updateStatus(InvoiceStatus::Failed);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Failed);

    // Retry
    $invoice->updateStatus(InvoiceStatus::Queued);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Queued);

    // Kan nu verder
    $invoice->updateStatus(InvoiceStatus::Sent);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Sent);
});

test('complete outgoing invoice flow', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $invoice->updateStatus(InvoiceStatus::Queued);
    $invoice->updateStatus(InvoiceStatus::Sent);
    $invoice->updateStatus(InvoiceStatus::Delivered);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Delivered);

    $histories = InvoiceStatusHistory::where('invoice_id', $invoice->id)->get();
    expect($histories)->toHaveCount(3);
});

test('complete incoming invoice flow', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'status' => InvoiceStatus::Received,
    ]);

    $invoice->updateStatus(InvoiceStatus::Processed);
    $invoice->updateStatus(InvoiceStatus::Archived);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Archived);

    $histories = InvoiceStatusHistory::where('invoice_id', $invoice->id)->get();
    expect($histories)->toHaveCount(2);
});

