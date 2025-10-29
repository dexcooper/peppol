<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\Currency;
use App\Enums\InvoiceDirection;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Support\MoneyFormatter;
use Brick\Money\Money;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
                ->columns(1)
                ->schema([
                    Grid::make(4)
                        ->schema([
                            Grid::make(1)
                                ->columnSpan(3)
                                ->schema([
                                    Section::make(__('forms.invoice.details'))
                                        ->columns(4)
                                        ->schema([
                                            Select::make('company_id')
                                                ->relationship('company', 'name')
                                                ->label(__('forms.invoice.company'))
                                                ->columnSpan(2)
                                                ->required(),
                                            TextInput::make('vat_number')
                                                ->label(function($record) {
                                                    if ($record) {
                                                        return $record?->direction == InvoiceDirection::OUTGOING ? __('forms.invoice.receiving_vat_number') : __('forms.invoice.sending_vat_number');
                                                    } else {
                                                        return __('forms.invoice.vat_number');
                                                    }
                                                })
                                                 ->columnSpan(2)
                                                ->required(),
                                            TextInput::make('external_id')
                                                ->label(__('forms.invoice.external_id'))
                                                ->required(),
                                            Select::make('currency')
                                                ->options(collect(Currency::cases())->mapWithKeys(fn(Currency $currency) => [$currency->value => $currency->value]))
                                                ->required()
                                                ->default(Currency::EUR->value),
                                            DatePicker::make('issue_date')
                                                ->label(__('forms.invoice.issue_date'))
                                                ->required(),
                                            DatePicker::make('due_date')
                                                ->label(__('forms.invoice.due_date')),
                                            TextInput::make('title')
                                                ->columnSpan(4)
                                                ->label(__('forms.invoice.title'))
                                                ->required(),
                                            TextArea::make('description')
                                                ->columnSpan(4)
                                                ->label(__('forms.invoice.description')),
                                        ]),
                                ]),
                            Grid::make(1)
                                ->schema([
                                    Section::make(__('forms.invoice.status'))
                                        ->visibleOn('edit')
                                        ->schema([
                                            TextEntry::make('direction')
                                                ->label(__('forms.invoice.direction'))
                                                ->formatStateUsing(fn (InvoiceDirection $state) => $state?->label())
                                                ->inlineLabel(),
                                            TextEntry::make('status')
                                                ->label(__('forms.invoice.status'))
                                                ->inlineLabel()
                                                ->badge()
                                                ->color(fn(InvoiceStatus $state) => $state?->color())
                                                ->formatStateUsing(fn (InvoiceStatus $state) => $state?->label()),
                                            TextEntry::make('peppol_id')
                                                ->label(__('forms.invoice.peppol_id'))
                                                ->inlineLabel()
                                                ->visible(fn ($record) => ! empty($record?->peppol_id)),
                                        ])
                                ])
                        ]),
                ]);
    }
}
