<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Actions\RegisterInMaventaAction;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Widgets\MaventaStatus;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
