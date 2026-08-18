<?php

namespace Database\Seeders;

use App\Models\SlaPolicy;
use Illuminate\Database\Seeder;

class SlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            ['name' => 'Critical', 'priority' => 'critical', 'response_hours' => 1, 'resolution_hours' => 24, 'resolution_days' => 1, 'escalation_enabled' => true, 'is_active' => true],
            ['name' => 'High', 'priority' => 'high', 'response_hours' => 2, 'resolution_hours' => 48, 'resolution_days' => 2, 'escalation_enabled' => true, 'is_active' => true],
            ['name' => 'Medium', 'priority' => 'medium', 'response_hours' => 4, 'resolution_hours' => 72, 'resolution_days' => 3, 'escalation_enabled' => false, 'is_active' => true],
            ['name' => 'Low', 'priority' => 'low', 'response_hours' => 8, 'resolution_hours' => 120, 'resolution_days' => 5, 'escalation_enabled' => false, 'is_active' => true],
        ];

        foreach ($policies as $policy) {
            SlaPolicy::updateOrCreate(['priority' => $policy['priority']], $policy);
        }
    }
}
