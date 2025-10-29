<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::unguard();

        $path = database_path('seeders/data/companies.json');
        $data = json_decode(file_get_contents($path), true);

        if (!isset($data['companies'])) return;

        foreach ($data['companies'] as $companyData) {
            Company::updateOrCreate(
                ['id' => $companyData['id']],
                $companyData
            );
        }

        Company::reguard();
    }
}
