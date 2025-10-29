<?php

use App\Enums\InvoiceStatus;
use App\Services\InvoiceStatusService;

beforeEach(function () {
    $this->service = new InvoiceStatusService();
});

// Outgoing invoice flow: Draft → Queued → Sent → Delivered
test('can transition from draft to queued', function () {
    $result = $this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::QUEUED);

    expect($result)->toBeTrue();
});

test('can transition from draft to failed', function () {
    $result = $this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::FAILED);

    expect($result)->toBeTrue();
});

test('cannot transition from draft to sent', function () {
    $result = $this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::SENT);

    expect($result)->toBeFalse();
});

test('cannot transition from draft to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::DELIVERED);

    expect($result)->toBeFalse();
});

test('can transition from queued to sent', function () {
    $result = $this->service->canTransition(InvoiceStatus::QUEUED, InvoiceStatus::SENT);

    expect($result)->toBeTrue();
});

test('can transition from queued to failed', function () {
    $result = $this->service->canTransition(InvoiceStatus::QUEUED, InvoiceStatus::FAILED);

    expect($result)->toBeTrue();
});

test('cannot transition from queued to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::QUEUED, InvoiceStatus::DELIVERED);

    expect($result)->toBeFalse();
});

test('can transition from sent to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::SENT, InvoiceStatus::DELIVERED);

    expect($result)->toBeTrue();
});

test('can transition from sent to failed', function () {
    $result = $this->service->canTransition(InvoiceStatus::SENT, InvoiceStatus::FAILED);

    expect($result)->toBeTrue();
});

test('cannot transition from sent to queued', function () {
    $result = $this->service->canTransition(InvoiceStatus::SENT, InvoiceStatus::QUEUED);

    expect($result)->toBeFalse();
});

test('cannot transition from delivered to any status', function () {
    expect($this->service->canTransition(InvoiceStatus::DELIVERED, InvoiceStatus::FAILED))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::DELIVERED, InvoiceStatus::QUEUED))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::DELIVERED, InvoiceStatus::SENT))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::DELIVERED, InvoiceStatus::DRAFT))->toBeFalse();
});

// Retry flow
test('can transition from failed to queued for retry', function () {
    $result = $this->service->canTransition(InvoiceStatus::FAILED, InvoiceStatus::QUEUED);

    expect($result)->toBeTrue();
});

test('cannot transition from failed to sent', function () {
    $result = $this->service->canTransition(InvoiceStatus::FAILED, InvoiceStatus::SENT);

    expect($result)->toBeFalse();
});

test('cannot transition from failed to delivered', function () {
    $result = $this->service->canTransition(InvoiceStatus::FAILED, InvoiceStatus::DELIVERED);

    expect($result)->toBeFalse();
});

// Incoming invoice flow: Received → Processed → Archived
test('can transition from received to processed', function () {
    $result = $this->service->canTransition(InvoiceStatus::RECEIVED, InvoiceStatus::PROCESSED);

    expect($result)->toBeTrue();
});

test('cannot transition from received to archived', function () {
    $result = $this->service->canTransition(InvoiceStatus::RECEIVED, InvoiceStatus::ARCHIVED);

    expect($result)->toBeFalse();
});

test('cannot transition from received to draft', function () {
    $result = $this->service->canTransition(InvoiceStatus::RECEIVED, InvoiceStatus::DRAFT);

    expect($result)->toBeFalse();
});

test('can transition from processed to archived', function () {
    $result = $this->service->canTransition(InvoiceStatus::PROCESSED, InvoiceStatus::ARCHIVED);

    expect($result)->toBeTrue();
});

test('cannot transition from processed to received', function () {
    $result = $this->service->canTransition(InvoiceStatus::PROCESSED, InvoiceStatus::RECEIVED);

    expect($result)->toBeFalse();
});

test('cannot transition from archived to any status', function () {
    expect($this->service->canTransition(InvoiceStatus::ARCHIVED, InvoiceStatus::PROCESSED))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::ARCHIVED, InvoiceStatus::RECEIVED))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::ARCHIVED, InvoiceStatus::DRAFT))->toBeFalse();
});

// Cross-flow transitions (outgoing vs incoming)
test('cannot transition from draft to received', function () {
    $result = $this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::RECEIVED);

    expect($result)->toBeFalse();
});

test('cannot transition from received to queued', function () {
    $result = $this->service->canTransition(InvoiceStatus::RECEIVED, InvoiceStatus::QUEUED);

    expect($result)->toBeFalse();
});

test('cannot transition from sent to processed', function () {
    $result = $this->service->canTransition(InvoiceStatus::SENT, InvoiceStatus::PROCESSED);

    expect($result)->toBeFalse();
});

// Edge cases
test('cannot transition to same status', function () {
    expect($this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::DRAFT))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::SENT, InvoiceStatus::SENT))->toBeFalse();
    expect($this->service->canTransition(InvoiceStatus::DELIVERED, InvoiceStatus::DELIVERED))->toBeFalse();
});

test('validates all possible transitions from draft', function () {
    $validTransitions = [InvoiceStatus::QUEUED, InvoiceStatus::FAILED];
    $invalidTransitions = [InvoiceStatus::SENT, InvoiceStatus::DELIVERED, InvoiceStatus::RECEIVED, InvoiceStatus::PROCESSED, InvoiceStatus::ARCHIVED];

    foreach ($validTransitions as $status) {
        expect($this->service->canTransition(InvoiceStatus::DRAFT, $status))->toBeTrue();
    }

    foreach ($invalidTransitions as $status) {
        expect($this->service->canTransition(InvoiceStatus::DRAFT, $status))->toBeFalse();
    }
});

test('validates complete outgoing invoice workflow', function () {
    // Draft → Queued → Sent → Delivered
    expect($this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::QUEUED))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::QUEUED, InvoiceStatus::SENT))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::SENT, InvoiceStatus::DELIVERED))->toBeTrue();
});

test('validates complete incoming invoice workflow', function () {
    // Received → Processed → Archived
    expect($this->service->canTransition(InvoiceStatus::RECEIVED, InvoiceStatus::PROCESSED))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::PROCESSED, InvoiceStatus::ARCHIVED))->toBeTrue();
});

test('validates retry workflow', function () {
    // Draft → Queued → Failed → Queued (retry)
    expect($this->service->canTransition(InvoiceStatus::DRAFT, InvoiceStatus::QUEUED))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::QUEUED, InvoiceStatus::FAILED))->toBeTrue();
    expect($this->service->canTransition(InvoiceStatus::FAILED, InvoiceStatus::QUEUED))->toBeTrue();
});
