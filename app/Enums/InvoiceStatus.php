<?php

namespace App\Enums;

use App\Traits\EnumArrayable;

enum InvoiceStatus: string
{
    use EnumArrayable;

    // Incoming invoices
    case DRAFT = 'draft';
    case QUEUED = 'queued';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    // Outgoing invoices
    case RECEIVED = 'received';
    case PROCESSED = 'processed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('enums.invoice_status.draft'),
            self::QUEUED => __('enums.invoice_status.queued'),
            self::SENT => __('enums.invoice_status.sent'),
            self::DELIVERED => __('enums.invoice_status.delivered'),
            self::FAILED => __('enums.invoice_status.failed'),
            self::RECEIVED => __('enums.invoice_status.received'),
            self::PROCESSED => __('enums.invoice_status.processed'),
            self::ARCHIVED => __('enums.invoice_status.archived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::QUEUED => 'warning',
            self::SENT => 'info',
            self::DELIVERED => 'success',
            self::FAILED => 'danger',
            self::RECEIVED, self::PROCESSED => 'primary',
            self::ARCHIVED => 'secondary',
        };
    }
}
