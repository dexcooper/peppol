<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('forms.company.name'))
                    ->required(),
                TextInput::make('vat_number')
                    ->label(__('forms.company.vat_number')),
            ]);
    }
}
