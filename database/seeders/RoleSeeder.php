<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'System administrator with full access', 'is_active' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Team manager with department oversight', 'is_active' => true],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'IT support staff who handles tickets', 'is_active' => true],
            ['name' => 'User', 'slug' => 'user', 'description' => 'Regular user who can create and view own tickets', 'is_active' => true],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}