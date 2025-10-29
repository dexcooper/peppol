<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CompanySeeder::class);
        $this->call(USerSeeder::class);
        /*
        Company::factory()->count(5)
            ->has(Invoice::factory()->count(rand(1,10))
                ->has(InvoiceLine::factory()->count(rand(1,5)))
            )
            ->has(User::factory()->count(rand(1,3)))
            ->create();
        */
    }
}
