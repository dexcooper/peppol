<?php

namespace App\Enums;

use App\Traits\EnumArrayable;

enum InvoiceDirection: string
{
    use EnumArrayable;

    // Incoming invoices
    case OUTGOING = 'outgoing';
    case INCOMING = 'incoming';

    public function label(): string
    {
        return match ($this) {
            self::OUTGOING => __('enums.invoice_direction.outgoing'),
            self::INCOMING => __('enums.invoice_direction.incoming'),
        };
    }
}
