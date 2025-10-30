<?php

namespace App\Enums;

use App\Traits\EnumArrayable;

enum Currency: string
{
    use EnumArrayable;

    case EUR = 'EUR';
    case USD = 'USD';

    public static function allowed(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
