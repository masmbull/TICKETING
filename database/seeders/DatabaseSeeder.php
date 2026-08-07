<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles, departments, statuses, SLA policies, categories
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            TicketStatusSeeder::class,
            SlaPolicySeeder::class,
            CategorySeeder::class,
        ]);

        // Seed all users (admin, manager, staff, regular)
        $this->call([
            UserSeeder::class,
        ]);

        // Seed sample tickets
        $this->call([
            TicketSeeder::class,
        ]);
    }
}