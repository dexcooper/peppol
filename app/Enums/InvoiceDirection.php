<?php

namespace App\Enums;

use App\Traits\EnumArrayable;

enum InvoiceDirection: string
{
    use EnumArrayable;

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
