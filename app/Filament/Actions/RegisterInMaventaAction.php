<?php
namespace App\Filament\Actions;

use App\Models\Company;
use App\Services\Peppol\Maventa\MaventaRegistrationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RegisterInMaventaAction extends Action
{
    public static function make($name = 'registerInMaventa'): static
    {
        return parent::make($name)
            ->label('Registreer bij Maventa')
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->visible(fn ($livewire): bool => empty($livewire->getRecord()->maventa_company_id))
            ->color('primary')
            ->action(function (Company $company, $livewire): void {
                try {
                    app(MaventaRegistrationService::class)->register($company);
                    Notification::make()
                        ->title('Bedrijf succesvol geregistreerd bij Maventa.')
                        ->success()
                        ->send();
                    $livewire->dispatch('maventa-registered');
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Registratie mislukt: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
