<?php

namespace App\Enums;

use App\Traits\EnumArrayable;
use Filament\Support\Contracts\HasLabel;

enum Country: string implements HasLabel
{
    use EnumArrayable;

    case BE = 'BE';

    public function getLabel(): string
    {
        return match ($this) {
            self::BE => __('enums.maventa_countries.BE'),
        };
    }
}
