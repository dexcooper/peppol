<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected $listeners = [
        'refreshInvoiceForm' => 'refreshTotalPayments',
    ];

    public function refreshTotalPayments()
    {
        $this->form->fill([
            'total_amount' => $this->record->invoiceLines->sum('amount'),
        ]);
    }
}
