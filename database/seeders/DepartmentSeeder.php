<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'IT Support', 'slug' => 'it-support', 'description' => 'General IT support and helpdesk', 'is_active' => true],
            ['name' => 'Network', 'slug' => 'network', 'description' => 'Network infrastructure and connectivity', 'is_active' => true],
            ['name' => 'Software', 'slug' => 'software', 'description' => 'Software applications and development', 'is_active' => true],
            ['name' => 'Hardware', 'slug' => 'hardware', 'description' => 'Hardware maintenance and repairs', 'is_active' => true],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['slug' => $dept['slug']], $dept);
        }
    }
}