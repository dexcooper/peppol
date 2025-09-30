<?php

namespace App\Services;

use App\Enums\InvoiceStatus;

class InvoiceStatusService
{
    private array $transitions = [
        InvoiceStatus::Draft->value => [InvoiceStatus::Queued, InvoiceStatus::Failed],
        InvoiceStatus::Queued->value => [InvoiceStatus::Sent, InvoiceStatus::Failed],
        InvoiceStatus::Sent->value => [InvoiceStatus::Delivered, InvoiceStatus::Failed],
        InvoiceStatus::Delivered->value => [], // einde status
        InvoiceStatus::Failed->value => [InvoiceStatus::Queued], // retry mogelijk
        InvoiceStatus::Received->value => [InvoiceStatus::Processed],
        InvoiceStatus::Processed->value => [InvoiceStatus::Archived],
        InvoiceStatus::Archived->value => [], // einde status
    ];

    public function canTransition(InvoiceStatus $from, InvoiceStatus $to): bool
    {
        return in_array($to, $this->transitions[$from->value] ?? [], true);
    }
}
