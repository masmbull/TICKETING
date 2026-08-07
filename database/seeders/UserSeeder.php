<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    protected string $defaultPassword = 'Admin@123';

    public function run(): void
    {
        $password = Hash::make($this->defaultPassword);

        // Look up roles and departments by slug
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $staffRole = Role::where('slug', 'staff')->first();
        $userRole = Role::where('slug', 'user')->first();

        $itSupportDept = Department::where('slug', 'it-support')->first();
        $networkDept = Department::where('slug', 'network')->first();
        $softwareDept = Department::where('slug', 'software')->first();
        $hardwareDept = Department::where('slug', 'hardware')->first();

        $users = [
            // Admin (1)
            [
                'name' => 'Admin MITO',
                'email' => 'admin@mito.local',
                'password' => $password,
                'role_id' => $adminRole->id,
                'department_id' => $itSupportDept->id,
                'force_password_change' => false,
                'is_active' => true,
            ],
            // Manager (1)
            [
                'name' => 'Rina Sari',
                'email' => 'rina.sari@mito.local',
                'password' => $password,
                'role_id' => $managerRole->id,
                'department_id' => $itSupportDept->id,
                'force_password_change' => false,
                'is_active' => true,
            ],
            // Staff (4)
            [
                'name' => 'Ahmad Hidayat',
                'email' => 'ahmad.hidayat@mito.local',
                'password' => $password,
                'role_id' => $staffRole->id,
                'department_id' => $itSupportDept->id,
                'force_password_change' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@mito.local',
                'password' => $password,
                'role_id' => $staffRole->id,
                'department_id' => $networkDept->id,
                'force_password_change' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Firmansyah',
                'email' => 'firmansyah@mito.local',
                'password' => $password,
                'role_id' => $staffRole->id,
                'department_id' => $softwareDept->id,
                'force_password_change' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@mito.local',
                'password' => $password,
                'role_id' => $staffRole->id,
                'department_id' => $hardwareDept->id,
                'force_password_change' => false,
                'is_active' => true,
            ],
            // User (2)
            [
                'name' => 'Budi Prasetyo',
                'email' => 'budi.prasetyo@mito.local',
                'password' => $password,
                'role_id' => $userRole->id,
                'department_id' => null,
                'force_password_change' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Maya Putri',
                'email' => 'maya.putri@mito.local',
                'password' => $password,
                'role_id' => $userRole->id,
                'department_id' => null,
                'force_password_change' => false,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}