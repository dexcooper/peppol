<?php

namespace App\Enums;

use App\Traits\EnumArrayable;
use Filament\Support\Contracts\HasLabel;

enum PeppolProvider: string implements HasLabel
{
    use EnumArrayable;

    case MAVENTA = 'Maventa';
    case TEAMLEADER = 'Teamleader';

    public function getLabel(): string
    {
       return $this->value;
    }
}
