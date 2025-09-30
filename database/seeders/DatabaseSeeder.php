<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);

        // Alleen in development
        if (app()->environment('local', 'development')) {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
