<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    // Incoming invoices
    case Draft = 'draft';
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';

    // Outgoing invoices
    case Received = 'received';
    case Processed = 'processed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('enums.invoice_status.draft'),
            self::Queued => __('enums.invoice_status.queued'),
            self::Sent => __('enums.invoice_status.sent'),
            self::Delivered => __('enums.invoice_status.delivered'),
            self::Failed => __('enums.invoice_status.failed'),
            self::Received => __('enums.invoice_status.received'),
            self::Processed => __('enums.invoice_status.processed'),
            self::Archived => __('enums.invoice_status.archived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Queued => 'warning',
            self::Sent => 'info',
            self::Delivered => 'success',
            self::Failed => 'danger',
            self::Received, self::Processed => 'primary',
            self::Archived => 'secondary',
        };
    }
}
