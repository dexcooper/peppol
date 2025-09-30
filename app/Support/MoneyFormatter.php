<?php

namespace App\Support;

use Brick\Money\Money;

class MoneyFormatter
{
    public static function format(Money $money, ?string $money_locale = null): string
    {
        $money_locale = $money_locale ?? config('app.money_locale');
        return $money->formatTo($money_locale);
    }
}
