<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        // Sprint 3.1: exactly three statuses drive the ticket workflow.
        // - Waiting Confirmation: newly submitted, no one is working on it yet.
        // - In Progress: an IT Support member has analysed the problem and is working on it.
        // - Completed: work is finished, resolution recorded and completion timestamped.
        $statuses = [
            ['name' => 'Waiting Confirmation', 'slug' => 'waiting-confirmation', 'sort_order' => 1, 'color' => 'blue', 'is_active' => true],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'sort_order' => 2, 'color' => 'yellow', 'is_active' => true],
            ['name' => 'Completed', 'slug' => 'completed', 'sort_order' => 3, 'color' => 'green', 'is_active' => true],
        ];

        foreach ($statuses as $status) {
            TicketStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }

        // Remove legacy statuses that are no longer part of the workflow.
        TicketStatus::whereNotIn('slug', ['waiting-confirmation', 'in-progress', 'completed'])->delete();
    }
}