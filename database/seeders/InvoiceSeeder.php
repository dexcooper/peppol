<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Brick\Money\Money;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoice = new Invoice([
            'company_id' => 2,
            'title'      => 'Factuur 1',
            'description'=> 'Beschrijving factuur 1',
            'direction'  => 'outgoing',
            'status'     => 'draft',
            'issue_date' => now(),
            'due_date'   => now()->addDays(30),
        ]);

        $invoice->money = Money::of(2500, Currency::EUR->value); // €2500
        $invoice->save();

        $invoiceLine1 = new InvoiceLine([
            'description' => 'Line 1',
            'vat_rate'    => 21,
        ]);
        $invoiceLine1->money = Money::of(1500, Currency::EUR->value); // €1500
        $invoice->invoiceLines()->save($invoiceLine1);

        $invoiceLine2 = new InvoiceLine([
            'description' => 'Line 2',
            'vat_rate'    => 6,
        ]);
        $invoiceLine2->money = Money::of(1000, Currency::EUR->value); // €1500
        $invoice->invoiceLines()->save($invoiceLine2);
    }
}
