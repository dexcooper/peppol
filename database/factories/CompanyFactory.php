<?php

namespace Database\Factories;

use App\Enums\Country;
use App\Enums\PeppolProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->company(),
            'email'         => $this->faker->companyEmail(),
            'vat_number'    => $this->faker->vat(),
            'contact_person_first_name' => $this->faker->firstName(),
            'contact_person_name' => $this->faker->name(),
            'street'        => $this->faker->streetAddress(),
            'number'        => $this->faker->buildingNumber(),
            'zip_code'      => $this->faker->postcode(),
            'city'          => $this->faker->city(),
            'country'       => Country::BE,
            'peppol_provider' => PeppolProvider::MAVENTA,
        ];
    }
}
