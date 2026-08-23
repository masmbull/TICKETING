<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'       => 'Administrator',
                'email'      => 'admin@mito.local',
                'role_slug'  => 'admin',
                'department' => 'IT Support',
            ],
            [
                'name'       => 'IT Manager',
                'email'      => 'manager.it@mito.local',
                'role_slug'  => 'manager',
                'department' => 'IT Support',
            ],
            [
                'name'       => 'Shohibul Anwar',
                'email'      => 'shohibul@mito.local',
                'role_slug'  => 'staff',
                'department' => 'IT Support',
            ],
            [
                'name'       => 'Riyanto',
                'email'      => 'riyanto.it@mito.local',
                'role_slug'  => 'staff',
                'department' => 'IT Support',
            ],
            [
                'name'       => 'Daniel',
                'email'      => 'daniel@mito.local',
                'role_slug'  => 'user',
                'department' => 'Marketing',
            ],
            [
                'name'       => 'Marketing 1',
                'email'      => 'marketing1@mito.local',
                'role_slug'  => 'user',
                'department' => 'Marketing',
            ],
            [
                'name'       => 'Finance 1',
                'email'      => 'finance1@mito.local',
                'role_slug'  => 'user',
                'department' => 'Finance',
            ],
            [
                'name'       => 'Warehouse 1',
                'email'      => 'warehouse1@mito.local',
                'role_slug'  => 'user',
                'department' => 'Warehouse',
            ],
        ];

        // 'hashed' cast on User model auto-hashes on save — do NOT Hash::make() here
        // SECURITY: never seed a shared/known password (old value was a fixed
        // string that also existed in git history). Random per run, local/test
        // only. Printed once below for local login convenience.
        $password = Str::random(16);

        foreach ($users as $userData) {
            $roleSlug  = $userData['role_slug'];
            $deptName  = $userData['department'];

            unset($userData['role_slug'], $userData['department']);

            $role = \App\Models\Role::where('slug', $roleSlug)->first();
            $dept = \App\Models\Department::where('name', $deptName)->first();

            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password'              => $password,
                    'role_id'               => $role?->id,
                    'department_id'         => $dept?->id,
                    'is_active'             => true,
                    'force_password_change' => false,
                    'email_verified_at'     => now(),
                ])
            );
        }

        $this->command?->info("Seeded users password for THIS RUN ONLY: {$password}");
    }
}