<?php

namespace Database\Seeders;

use App\Models\SlaPolicy;
use Illuminate\Database\Seeder;

class SlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            ['name' => 'Critical', 'priority' => 'critical', 'response_hours' => 1, 'resolution_hours' => 4, 'escalation_enabled' => true, 'is_active' => true],
            ['name' => 'High', 'priority' => 'high', 'response_hours' => 2, 'resolution_hours' => 8, 'escalation_enabled' => true, 'is_active' => true],
            ['name' => 'Medium', 'priority' => 'medium', 'response_hours' => 4, 'resolution_hours' => 24, 'escalation_enabled' => false, 'is_active' => true],
            ['name' => 'Low', 'priority' => 'low', 'response_hours' => 8, 'resolution_hours' => 72, 'escalation_enabled' => false, 'is_active' => true],
        ];

        foreach ($policies as $policy) {
            SlaPolicy::updateOrCreate(['priority' => $policy['priority']], $policy);
        }
    }
}