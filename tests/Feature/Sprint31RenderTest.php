<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint31RenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_show_renders_workflow_card(): void
    {
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id')]);
        $user = User::factory()->create();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-WF-0001',
            'user_id' => $user->id,
            'category_id' => \App\Models\Category::first()->id,
            'description' => 'Workflow render test',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ]);

        $this->actingAs($admin);

        $response = $this->get("/my-tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('Workflow');
        $response->assertSee('Assign to Me');
        $response->assertSee('Problem Analysis');
        $response->assertSee('Resolution');

        // Unassigned ticket visible to staff for Assign to Me.
        $this->actingAs(User::factory()->create(['role_id' => Role::where('slug', 'staff')->value('id')]));
        $response = $this->get("/my-tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('Assign to Me');
    }
}
