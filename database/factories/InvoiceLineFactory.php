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
        $unitPrice = $this->faker->numberBetween(100, 1000);
        $number = $this->faker->numberBetween(1, 5);
        $total = $unitPrice * $number;

        return [
            'invoice_id' => Invoice::factory(),
            'description'  => $this->faker->sentence(4),
            'unit_price'   => $unitPrice,
            'number'       => $number,
            'total_amount' => $total,
            'vat_rate'     => $this->faker->randomElement(VatRate::values()),
        ];
    }
}
