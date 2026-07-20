<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) env('ADMIN_DEFAULT_NAME', 'Velmics Admin', 'velmics 2024');
        $email = (string) env('ADMIN_DEFAULT_EMAIL', 'admin@velmics.test', 'admin@velmics24.test');
        $password = (string) env('ADMIN_DEFAULT_PASSWORD', 'password', 'password24');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
