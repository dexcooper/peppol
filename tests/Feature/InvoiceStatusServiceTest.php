<?php

use App\Enums\InvoiceStatus;
use App\Services\InvoiceStatusService;

beforeEach(function () {
    $this->service = new InvoiceStatusService();
});

// Outgoing invoice flow: Draft → Queued → Sent → Delivered
test('can transition from draft to queued', function () {
    $result = $this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Queued);

    expect($result)->toBeTrue();
});

test('can transition from draft to failed', function () {
    $result = $this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Failed);

    expect($result)->toBeTrue();
});

test('cannot transition from draft to sent', function () {
    $result = $this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Sent);

    expect($result)->toBeFalse();
});

test('cannot transition from draft to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Delivered);

    expect($result)->toBeFalse();
});

test('can transition from queued to sent', function () {
    $result = $this->service->canTransition(InvoiceStatus::Queued, InvoiceStatus::Sent);

    expect($result)->toBeTrue();
});

test('can transition from queued to failed', function () {
    $result = $this->service->canTransition(InvoiceStatus::Queued, InvoiceStatus::Failed);

    expect($result)->toBeTrue();
});

test('cannot transition from queued to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::Queued, InvoiceStatus::Delivered);

    expect($result)->toBeFalse();
});

test('can transition from sent to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::Sent, InvoiceStatus::Delivered);

    expect($result)->toBeTrue();
});

test('can transition from sent to failed', function () {
    $result = $this->service->canTransition(InvoiceStatus::Sent, InvoiceStatus::Failed);

    expect($result)->toBeTrue();
});

test('cannot transition from sent to queued', function () {
    $result = $this->service->canTransition(InvoiceStatus::Sent, InvoiceStatus::Queued);

    expect($result)->toBeFalse();
});

test('cannot transition from delivered to any status', function () {
    expect($this->service->canTransition(InvoiceStatus::Delivered, InvoiceStatus::Failed))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::Delivered, InvoiceStatus::Queued))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::Delivered, InvoiceStatus::Sent))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::Delivered, InvoiceStatus::Draft))->toBeFalse();
});

// Retry flow
test('can transition from failed to queued for retry', function () {
    $result = $this->service->canTransition(InvoiceStatus::Failed, InvoiceStatus::Queued);

    expect($result)->toBeTrue();
});

test('cannot transition from failed to sent', function () {
    $result = $this->service->canTransition(InvoiceStatus::Failed, InvoiceStatus::Sent);

    expect($result)->toBeFalse();
});

test('cannot transition from failed to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::Failed, InvoiceStatus::Delivered);

    expect($result)->toBeFalse();
});

// Incoming invoice flow: Received → Processed → Archived
test('can transition from received to processed', function () {
    $result = $this->service->canTransition(InvoiceStatus::Received, InvoiceStatus::Processed);

    expect($result)->toBeTrue();
});

test('cannot transition from received to archived', function () {
    $result = $this->service->canTransition(InvoiceStatus::Received, InvoiceStatus::Archived);

    expect($result)->toBeFalse();
});

test('cannot transition from received to draft', function () {
    $result = $this->service->canTransition(InvoiceStatus::Received, InvoiceStatus::Draft);

    expect($result)->toBeFalse();
});

test('can transition from processed to archived', function () {
    $result = $this->service->canTransition(InvoiceStatus::Processed, InvoiceStatus::Archived);

    expect($result)->toBeTrue();
});

test('cannot transition from processed to received', function () {
    $result = $this->service->canTransition(InvoiceStatus::Processed, InvoiceStatus::Received);

    expect($result)->toBeFalse();
});

test('cannot transition from archived to any status', function () {
    expect($this->service->canTransition(InvoiceStatus::Archived, InvoiceStatus::Processed))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::Archived, InvoiceStatus::Received))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::Archived, InvoiceStatus::Draft))->toBeFalse();
});

// Cross-flow transitions (outgoing vs incoming)
test('cannot transition from draft to received', function () {
    $result = $this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Received);

    expect($result)->toBeFalse();
});

test('cannot transition from received to queued', function () {
    $result = $this->service->canTransition(InvoiceStatus::Received, InvoiceStatus::Queued);

    expect($result)->toBeFalse();
});

test('cannot transition from sent to processed', function () {
    $result = $this->service->canTransition(InvoiceStatus::Sent, InvoiceStatus::Processed);

    expect($result)->toBeFalse();
});

// Edge cases
test('cannot transition to same status', function () {
    expect($this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Draft))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::Sent, InvoiceStatus::Sent))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::Delivered, InvoiceStatus::Delivered))->toBeFalse();
});

test('validates all possible transitions from draft', function () {
    $validTransitions = [InvoiceStatus::Queued, InvoiceStatus::Failed];
    $invalidTransitions = [InvoiceStatus::Sent, InvoiceStatus::Delivered, InvoiceStatus::Received, InvoiceStatus::Processed, InvoiceStatus::Archived];

    foreach ($validTransitions as $status) {
        expect($this->service->canTransition(InvoiceStatus::Draft, $status))->toBeTrue();
    }

    foreach ($invalidTransitions as $status) {
        expect($this->service->canTransition(InvoiceStatus::Draft, $status))->toBeFalse();
    }
});

test('validates complete outgoing invoice workflow', function () {
    // Draft → Queued → Sent → Delivered
    expect($this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Queued))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::Queued, InvoiceStatus::Sent))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::Sent, InvoiceStatus::Delivered))->toBeTrue();
});

test('validates complete incoming invoice workflow', function () {
    // Received → Processed → Archived
    expect($this->service->canTransition(InvoiceStatus::Received, InvoiceStatus::Processed))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::Processed, InvoiceStatus::Archived))->toBeTrue();
});

test('validates retry workflow', function () {
    // Draft → Queued → Failed → Queued (retry)
    expect($this->service->canTransition(InvoiceStatus::Draft, InvoiceStatus::Queued))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::Queued, InvoiceStatus::Failed))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::Failed, InvoiceStatus::Queued))->toBeTrue();
});
