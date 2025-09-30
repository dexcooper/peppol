<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Tiptap\Nodes\Text;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
            return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label(__('forms.user.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('forms.user.email'))
                    ->required(),
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('password')
                    ->label(__('forms.user.password'))
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create' && false) // niet verplicht
            ]);
    }
}
