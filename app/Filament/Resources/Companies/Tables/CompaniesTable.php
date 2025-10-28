<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Enums\PeppolProvider;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('forms.company.name'))
                    ->sortable(),
                TextColumn::make('vat_number')
                    ->label(__('forms.company.vat_number'))
                    ->sortable(),
                TextColumn::make('peppol_provider')
                    ->label(__('forms.company.peppol_provider'))
                    ->sortable(),
                TextColumn::make('maventa')
                    ->label(__('fields.maventa'))
                    ->badge()
                    ->icon(function ($record) {
                        switch ($record->peppol_provider) {
                            case PeppolProvider::MAVENTA:
                               return $record->maventa_company_id
                                    ? 'heroicon-o-check-circle'
                                    : 'heroicon-o-clock';
                            default:
                                return 'heroicon-o-clock';
                        }
                    })
                    ->getStateUsing(function ($record) {
                        switch ($record->peppol_provider) {
                            case PeppolProvider::MAVENTA:
                               return $record->maventa_company_id
                                    ? __('fields.active')
                                    : __('fields.inactive');
                            default:
                                return __('fields.inactive');
                        }
                    })
                    ->color(function ($record) {
                        switch ($record->peppol_provider) {
                            case PeppolProvider::MAVENTA:
                                return $record->maventa_company_id
                                    ? 'success'
                                    : 'gray';
                            default:
                                return 'gray';
                        }
                    })
                    ->sortable(),
            ])
            ->filters([
                //
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
