<?php

namespace App\Enums;

enum InvoiceDirection: string
{
    // Incoming invoices
    case Outgoing = 'outgoing';
    case Incoming = 'incoming';

    public function label(): string
    {
        return match ($this) {
            self::Outgoing => __('enums.invoice_direction.outgoing'),
            self::Incoming => __('enums.invoice_direction.incoming'),
        };
    }
}
