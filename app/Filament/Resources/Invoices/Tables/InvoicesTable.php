<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Support\MoneyFormatter;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label(__('forms.invoice.company')),
                TextColumn::make('external_id')
                    ->label(__('forms.invoice.external_id'))
                    ->searchable(),
                TextColumn::make('issue_date')
                    ->label(__('forms.invoice.issue_date'))
                    ->formatStateUsing(function ($record) {
                        return $record->issue_date ? Carbon::parse($record->issue_date)->translatedFormat('d F y') : '-';
                    }),
                TextColumn::make('vat')
                    ->label(__('forms.invoice.vat'))
                    ->money(fn($record) => $record->currency->value, 100),
                TextColumn::make('total')
                    ->label(__('forms.invoice.total'))
                    ->money(fn($record) => $record->currency->value, 100),
                TextColumn::make('status')
                    ->label(__('forms.invoice.status'))
                    ->formatStateUsing(fn(InvoiceStatus $state) => $state->label())
                    ->badge()
                    ->color(fn(InvoiceStatus $state) => $state->color()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(
                        collect(InvoiceStatus::cases())
                            ->mapWithKeys(fn(InvoiceStatus $state) => [$state->value => $state->label()])
                            ->toArray(),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
