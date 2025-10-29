<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Enums\Currency;
use App\Enums\VatRate;
use App\Support\MoneyFormatter;
use Brick\Money\Money;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Tiptap\Nodes\Text;
use Illuminate\Database\Eloquent\Model;

class InvoiceLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoiceLines';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                TextInput::make('number')
                    ->label(__('forms.invoice_line.amount'))
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total_amount', $state * $get('unit_price'));
                    }),
                TextInput::make('unit_price')
                    ->label(__('forms.invoice_line.unit_price'))
                    ->numeric()
                    ->prefix('€')
                    ->dehydrateStateUsing(fn ($state) => $state ? (int) round($state * 100) : '')
                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : '')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total_amount', $state * $get('number'));
                    }),
                Select::make('vat_rate')
                    ->label(__('forms.invoice_line.vat_rate'))
                    ->options(collect(VatRate::cases())->mapWithKeys(fn(VatRate $vatRate) => [$vatRate->value => $vatRate->label()]))
                    ->required(),
                TextInput::make('vat')
                    ->label(__('forms.invoice_line.vat'))
                    ->numeric()
                    ->prefix('€')
                    ->dehydrateStateUsing(fn ($state) => $state ? (int) round($state * 100) : '')
                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : '')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total_amount', $state * $get('number'));
                    }),
                TextInput::make('total')
                    ->label(__('forms.invoice_line.total'))
                    ->required()
                    ->numeric()
                    ->prefix('€')
                    ->dehydrateStateUsing(fn ($state) => $state ? (int) round($state * 100) : 0)
                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : ''),
                TextInput::make('description')
                    ->label(__('forms.invoice_line.description'))
                    ->required()
                    ->columnSpan(4)
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')
                    ->label(__('forms.invoice_line.description'))
                    ->searchable(),
                TextColumn::make('money')
                    ->label(__('forms.invoice.total_amount'))
                    ->getStateUsing(fn($record) => MoneyFormatter::format($record->money)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function(){
                        $this->dispatch('refreshInvoiceForm');
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->after(function(){
                        $this->dispatch('refreshInvoiceForm');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('navigation/items.invoice_lines');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation/items.invoice_lines');
    }

    public static function getModelLabel(): string
    {
        return __('resources.invoice_line.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.invoice_line.plural');
    }
}
