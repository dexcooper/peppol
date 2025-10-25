<?php

namespace App\Filament\Resources\Companies\Actions;

use App\Models\Company;
use App\Models\User;
use App\Services\Maventa\MaventaApi;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class RegisterInMaventaAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('registerInMaventa');
        $this->label('Register in Maventa');
        $this->icon('heroicon-o-cloud-arrow-up');

        $this->form([
            Forms\Components\TextInput::make('email')
                ->label('Contact Email')
                ->email()
                ->required(),
            Forms\Components\TextInput::make('first_name')
                ->label('First Name')
                ->required(),
            Forms\Components\TextInput::make('last_name')
                ->label('Last Name')
                ->required(),
        ]);

        $this->action(function (array $data, Company $record) {
            try {
                $maventa = app(MaventaApi::class);

                $user = User::where('email', $data['email'])->first();

                if (!$user) {
                    $user = $maventa->createUser([
                        'email' => $data['email'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                    ]);
                }

                $company = $maventa->createCompany([
                    'name' => $record->name,
                    'registration_number' => $record->vat_number,
                    'country_code' => $record->country_code ?? 'BE',
                    'user_id' => $user['id'] ?? $user['user_id'] ?? null,
                ]);

                $record->update([
                    'maventa_user_id' => $user['id'] ?? null,
                    'maventa_company_id' => $company['id'] ?? null,
                ]);

                Notification::make()
                    ->title('Company successfully registered in Maventa')
                    ->success()
                    ->send();

            } catch (\Throwable $e) {
                Log::error('Maventa registration failed', ['error' => $e->getMessage()]);

                Notification::make()
                    ->title('Failed to register in Maventa')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });

        $this->requiresConfirmation()
            ->modalHeading('Register company in Maventa?')
            ->modalDescription('This will create the company and user in the Maventa Peppol network.')
            ->modalSubmitActionLabel('Confirm & Register');
    }
}
