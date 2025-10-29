<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\Country;
use App\Enums\PeppolProvider;
use App\Filament\Actions\RegisterInMaventaAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use PHPUnit\Metadata\Group;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make(__('forms.company.general'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('forms.company.name'))
                                            ->required()
                                            ->unique()
                                            ->validationMessages([
                                                'unique' => __('validation.custom.name.unique'),
                                            ]),
                                        TextInput::make('email')
                                            ->label(__('forms.company.email'))
                                            ->required()
                                            ->unique()
                                            ->validationMessages([
                                                'unique' => __('validation.custom.email.unique'),
                                            ]),
                                        TextInput::make('vat_number')
                                            ->label(__('forms.company.vat_number'))
                                            ->required()
                                            ->unique()
                                            ->validationMessages([
                                                'unique' => __('validation.custom.vat_number.unique'),
                                            ]),
                                    ]),
                                Section::make(__('forms.company.address'))
                                    ->columns(4)
                                    ->schema([
                                        TextInput::make('street')
                                            ->label(__('forms.company.street'))
                                            ->columnSpan(3)
                                            ->required(),
                                        TextInput::make('number')
                                            ->label(__('forms.company.number'))
                                            ->columnSpan(1)
                                            ->required(),
                                        TextInput::make('zip_code')
                                            ->label(__('forms.company.zip_code'))
                                            ->columnSpan(1)
                                            ->required(),
                                        TextInput::make('city')
                                            ->label(__('forms.company.city'))
                                            ->columnSpan(3)
                                            ->required(),
                                        Select::make('country')
                                            ->options(Country::class)
                                            ->default(Country::BE)
                                            ->columnSpan(4)
                                            ->required(),
                                    ])

                            ]),
                        Grid::make(1)
                            ->schema([
                                Section::make(__('forms.company.contact_person'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('contact_person_first_name')
                                            ->label(__('forms.company.first_name'))
                                            ->required(),
                                        TextInput::make('contact_person_name')
                                            ->label(__('forms.company.name'))
                                            ->required(),
                                    ]),
                                Section::make(__('forms.company.peppol'))
                                    ->schema([
                                        Select::make('peppol_provider')
                                            ->label(__('forms.company.provider'))
                                            ->options(PeppolProvider::class)
                                            ->required()
                                            ->live(),
                                        Grid::make(1)
                                            ->visible(fn ($record, $get) => $get('peppol_provider') === PeppolProvider::MAVENTA)
                                            ->schema([
                                                Grid::make(1)
                                                    ->visible(fn ($record, $get) => $get('peppol_provider') === PeppolProvider::MAVENTA && $record != null && empty($record?->maventa_company_id))
                                                    ->components([
                                                        RegisterInMaventaAction::make(),
                                                    ]),
                                                Grid::make(1)
                                                    ->visible(fn ($record) => ! empty($record?->maventa_company_id))
                                                    ->columns(5)
                                                    ->schema([
                                                        TextEntry::make('maventa')
                                                            ->columnSpan(1)
                                                            ->badge()
                                                            ->icon(fn ($record) => $record->maventa_company_id ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                                                            ->getStateUsing(fn ($record) =>
                                                                $record->maventa_company_id
                                                                    ? __('fields.active')
                                                                    : __('fields.inactive')
                                                            )
                                                            ->color(fn ($record) =>
                                                                $record->maventa_company_id
                                                                    ? 'success'
                                                                    : 'gray'
                                                            ),
                                                        TextEntry::make('maventa_company_id')
                                                            ->columnSpan(2)
                                                            ->label('Maventa Company ID'),
                                                        TextEntry::make('maventa_user_id')
                                                            ->columnSpan(2)
                                                            ->label('Maventa User ID'),
                                                    ])
                                            ])
                                    ]),
                            ])
                    ])
            ]);
    }
}
