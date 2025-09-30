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
                ->components([

                    Flex::make([
                        Section::make([
                            TextInput::make('title'),
                            Textarea::make('content'),
                        ]),
                        Section::make([
                            Toggle::make('is_published'),
                            Toggle::make('is_featured'),
                        ])->grow(true),
                    ])->from('md')
                ])
            ;

            /*
            ->columns(2)
            ->schema([
                    Section::make(__('forms.invoice.details'))
                        ->schema([
                            Select::make('company_id')
                                ->relationship('company', 'name')
                                ->label(__('forms.invoice.company'))
                                ->required(),
                            Select::make('currency')
                                ->options(collect(Currency::cases())->mapWithKeys(fn(Currency $currency) => [$currency->value => $currency->value]))
                                ->required()
                                ->default(Currency::EUR->value),
                            DatePicker::make('issue_date')
                                ->label(__('forms.invoice.issue_date')),
                            DatePicker::make('due_date')
                                ->label(__('forms.invoice.due_date')),
                            TextInput::make('title')
                                ->label(__('forms.invoice.title')),
                            TextArea::make('description')
                                ->label(__('forms.invoice.description')),
                        ]),
                    Section::make(__('forms.invoice.other'))
                        ->visibleOn('edit')
                        ->schema([
                            TextEntry::make('direction')
                                ->label(__('forms.invoice.direction'))
                                ->formatStateUsing(fn (InvoiceDirection $state) => $state->label()),
                            TextEntry::make('money')
                                ->label(__('forms.invoice.total_amount'))
                                ->formatStateUsing(fn ($state) => MoneyFormatter::format($state)),
                        ])->columns(2),
                    Section::make(__('forms.invoice.status'))
                        ->visibleOn('edit')
                        ->schema([
                            TextEntry::make('status')
                                ->label(__('forms.invoice.status'))
                                ->badge()
                                ->color(fn(InvoiceStatus $state) => $state->color())
                                ->formatStateUsing(fn (InvoiceStatus $state) => $state->label()),
                        ])->columns(2)

            ])
            */

    }
}
