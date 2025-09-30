<?php

namespace Database\Factories;

use App\Enums\InvoiceDirection;
use App\Enums\InvoiceStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id'   => Company::factory(),
            'title'        => $this->faker->sentence(3),
            'description'  => $this->faker->paragraph(),
            'direction'    => $this->faker->randomElement(InvoiceDirection::values()),
            'peppol_id'    => $this->faker->uuid(),
            'status'       => $this->faker->randomElement(InvoiceStatus::values()),
            'issue_date'   => $this->faker->date(),
            'due_date'     => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'currency'     => 'EUR',
            'total_amount' => 0,
            'raw_xml'      => null, // of een fake XML string als je dat wil
        ];
    }
}
