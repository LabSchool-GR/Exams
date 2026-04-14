<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        User::query()->firstOrCreate(
            ['email' => 'dev-admin@example.test'],
            [
                'name' => 'Dev Admin',
                'password' => Hash::make('dev-password-change-me'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
