<?php

namespace Database\Factories;

use App\Enums\VatRate;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceLine>
 */
class InvoiceLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = $this->faker->numberBetween(100, 1000) * 100;
        $number = $this->faker->numberBetween(1, 5);
        $vatRate = $this->faker->randomElement(VatRate::values());
        $total = $unitPrice * $number;
        $vat = $total * ($vatRate / 100);

        return [
            'invoice_id'    => Invoice::factory(),
            'description'   => $this->faker->name . ' Product',
            'unit_price'    => $unitPrice,
            'number'        => $number,
            'vat_rate'      => $vatRate,
            'vat'           => $vat,
            'total'         => $total,
        ];
    }
}
