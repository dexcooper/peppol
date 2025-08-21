<?php

namespace App\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup implements HasLabel
{
    case Settings;

    public function getLabel(): string
    {
        return match ($this) {
            self::Settings => __('navigation/groups.settings'),
        };
    }
}
