<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'tommy@publi4u.be'],
            [
                'company_id'    => 1,
                'name'          => 'Tommy Masschelein',
                'password'      => Hash::make('1234'),
            ]
        );
        User::firstOrCreate(
            ['email' => 'david@caenen.be'],
            [
                'company_id'    => 2,
                'name'          => 'David Demeersseman',
                'password'      => Hash::make('1234'),
            ]
        );
    }
}
