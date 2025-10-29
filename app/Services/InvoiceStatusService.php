<?php

namespace App\Services;

use App\Enums\InvoiceStatus;

class InvoiceStatusService
{
    private array $transitions = [
        InvoiceStatus::DRAFT->value => [InvoiceStatus::QUEUED, InvoiceStatus::FAILED],
        InvoiceStatus::QUEUED->value => [InvoiceStatus::SENT, InvoiceStatus::FAILED],
        InvoiceStatus::SENT->value => [InvoiceStatus::DELIVERED, InvoiceStatus::FAILED],
        InvoiceStatus::DELIVERED->value => [], // einde status
        InvoiceStatus::FAILED->value => [InvoiceStatus::QUEUED], // retry mogelijk
        InvoiceStatus::RECEIVED->value => [InvoiceStatus::PROCESSED],
        InvoiceStatus::PROCESSED->value => [InvoiceStatus::ARCHIVED],
        InvoiceStatus::ARCHIVED->value => [], // einde status
    ];

    public function canTransition(InvoiceStatus $from, InvoiceStatus $to): bool
    {
        return in_array($to, $this->transitions[$from->value] ?? [], true);
    }
}
