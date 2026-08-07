<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles, departments, statuses, SLA policies
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            TicketStatusSeeder::class,
            SlaPolicySeeder::class,
            CategorySeeder::class,
        ]);

        // Seed admin user
        $adminRole = Role::where('slug', 'admin')->first();
        $itDept = Department::where('slug', 'it-support')->first();

        User::updateOrCreate(
            ['email' => 'admin@mito.local'],
            [
                'name' => 'Administrator',
                'email' => 'admin@mito.local',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
                'role_id' => $adminRole?->id,
                'department_id' => $itDept?->id,
                'is_active' => true,
                'force_password_change' => true,
            ]
        );

        // Seed a manager user
        $managerRole = Role::where('slug', 'manager')->first();
        User::updateOrCreate(
            ['email' => 'manager@mito.local'],
            [
                'name' => 'Manager',
                'email' => 'manager@mito.local',
                'password' => Hash::make('Manager@123'),
                'email_verified_at' => now(),
                'role_id' => $managerRole?->id,
                'department_id' => $itDept?->id,
                'is_active' => true,
                'force_password_change' => true,
            ]
        );

        // Seed a staff user
        $staffRole = Role::where('slug', 'staff')->first();
        User::updateOrCreate(
            ['email' => 'staff@mito.local'],
            [
                'name' => 'Staff Member',
                'email' => 'staff@mito.local',
                'password' => Hash::make('Staff@123'),
                'email_verified_at' => now(),
                'role_id' => $staffRole?->id,
                'department_id' => $itDept?->id,
                'is_active' => true,
                'force_password_change' => false,
            ]
        );

        // Seed a regular user
        $userRole = Role::where('slug', 'user')->first();
        User::updateOrCreate(
            ['email' => 'user@mito.local'],
            [
                'name' => 'Regular User',
                'email' => 'user@mito.local',
                'password' => Hash::make('User@123'),
                'email_verified_at' => now(),
                'role_id' => $userRole?->id,
                'department_id' => null,
                'is_active' => true,
                'force_password_change' => false,
            ]
        );

        // ========== Additional Realistic Users ==========

        $netDept = Department::where('slug', 'network')->first();
        $swDept = Department::where('slug', 'software')->first();
        $hwDept = Department::where('slug', 'hardware')->first();

        // Additional Staff (IT agents)
        $staffUsers = [
            ['name' => 'Ahmad Rizky',       'email' => 'ahmad@mito.local',  'dept' => $itDept,   'force_change' => false],
            ['name' => 'Siti Nurhaliza',     'email' => 'siti@mito.local',   'dept' => $itDept,   'force_change' => false],
            ['name' => 'Budi Santoso',       'email' => 'budi@mito.local',   'dept' => $netDept,  'force_change' => false],
            ['name' => 'Dewi Lestari',       'email' => 'dewi@mito.local',   'dept' => $swDept,   'force_change' => false],
            ['name' => 'Eko Prasetyo',       'email' => 'eko@mito.local',    'dept' => $hwDept,   'force_change' => false],
        ];

        foreach ($staffUsers as $su) {
            User::updateOrCreate(
                ['email' => $su['email']],
                [
                    'name' => $su['name'],
                    'password' => Hash::make('Staff@123'),
                    'email_verified_at' => now(),
                    'role_id' => $staffRole->id,
                    'department_id' => $su['dept']?->id,
                    'is_active' => true,
                    'force_password_change' => $su['force_change'],
                ]
            );
        }

        // Additional Regular Users (employees)
        $regularUsers = [
            'Fadli Maulana',
            'Gita Puspita',
            'Hendra Wijaya',
            'Indah Permata',
            'Joko Susilo',
        ];

        foreach ($regularUsers as $i => $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@mito.local';
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('User@123'),
                    'email_verified_at' => now(),
                    'role_id' => $userRole->id,
                    'department_id' => null,
                    'is_active' => true,
                    'force_password_change' => false,
                ]
            );
        }

        // ========== Seed Sample Tickets ==========
        $this->call([TicketSeeder::class]);
    }
}
