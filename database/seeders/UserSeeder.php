<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::unguard();

        $path = database_path('seeders/data/users.json');
        $data = json_decode(file_get_contents($path), true);

        if (!isset($data['users'])) return;

        foreach ($data['users'] as $userData) {
            User::updateOrCreate(
                ['id' => $userData['id']],
                $userData
            );
        }

        User::reguard();
    }
}
