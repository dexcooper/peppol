<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('admin.default_email');
        $plain = config('admin.default_password');

        if (empty($plain)) {
            $plain = Str::password(16);
            $this->command->warn("ADMIN_PASSWORD not set; generating password: {$plain}");
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name'          => 'Admin',
                'password'      => Hash::make($plain),
            ]
        );
    }
}
