<?php

namespace App\Enums;

use App\Traits\EnumArrayable;

enum VatRate: int
{
    use EnumArrayable;

    // Incoming invoices
    case Zero = 0;
    case Six = 6;
    case Twelve = 12;
    case TwentyOne = 21;

    public function label(): string
    {
        return match ($this) {
            self::Zero => '0%',
            self::Six => '6%',
            self::Twelve => '12%',
            self::TwentyOne => '21%',
        };
    }
}
