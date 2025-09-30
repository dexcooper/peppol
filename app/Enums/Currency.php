<?php

namespace App\Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';

    public static function allowed(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
