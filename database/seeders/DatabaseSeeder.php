<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mito.local'],
            [
                'name' => 'Administrator',
                'email' => 'admin@mito.local',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
            ]
        );
    }
}