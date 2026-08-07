<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Open', 'slug' => 'open', 'sort_order' => 1, 'color' => 'blue', 'is_active' => true],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'sort_order' => 2, 'color' => 'yellow', 'is_active' => true],
            ['name' => 'Waiting User', 'slug' => 'waiting-user', 'sort_order' => 3, 'color' => 'orange', 'is_active' => true],
            ['name' => 'Resolved', 'slug' => 'resolved', 'sort_order' => 4, 'color' => 'green', 'is_active' => true],
            ['name' => 'Closed', 'slug' => 'closed', 'sort_order' => 5, 'color' => 'gray', 'is_active' => true],
        ];

        foreach ($statuses as $status) {
            TicketStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}