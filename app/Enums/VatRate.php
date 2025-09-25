<?php

namespace App\Enums;

enum VatRate: int
{
    // Incoming invoices
    case Six = 6;
    case Twelve = 12;
    case TwentyOne = 21;

    public function label(): string
    {
        return match ($this) {
            self::Six => '6%',
            self::Twelve => '12%',
            self::TwentyOne => '21%',
        };
    }
}
