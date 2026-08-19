<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\SlaMapping;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint43SlaStatusStaffTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private User $employee;
    private Category $category;
    private SubCategory $subCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\SlaPolicySeeder::class);

        $this->admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id')]);
        $this->manager = User::factory()->create(['role_id' => Role::where('slug', 'manager')->value('id')]);
        $this->staff = User::factory()->create(['name' => 'Staff One', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $this->employee = User::factory()->create();
        $this->category = Category::first();
        $this->subCategory = SubCategory::first();
    }

    private function makeTicket(array $overrides = []): Ticket
    {
        return Ticket::create(array_merge([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 5, '0', STR_PAD_LEFT),
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Test ticket',
            'status' => 'Waiting Confirmation',
        ], $overrides));
    }

    // ─── SLA STATUS TESTS ────────────────────────────────────

    public function test_wc_ticket_does_not_display_sla_status(): void
    {
        $ticket = $this->makeTicket([
            'sla_priority' => 'high',
            'sla_started_at' => now()->subDays(1),
            'sla_deadline' => now()->addDays(1),
        ]);
        $this->assertEquals('Not Evaluated', $ticket->sla_status);
    }

    public function test_ip_ticket_does_not_display_sla_status(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'In Progress',
            'sla_priority' => 'high',
            'sla_started_at' => now()->subDays(1),
            'sla_deadline' => now()->addDays(1),
        ]);
        $this->assertEquals('Not Evaluated', $ticket->sla_status);
    }

    public function test_completed_ticket_displays_sla_status(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'high',
            'sla_started_at' => now()->subDay(),
            'sla_deadline' => now()->addDay(),
            'completed_at' => now()->subHours(6),
        ]);
        $this->assertContains($ticket->sla_status, ['Excellent', 'Normal', 'Poor']);
    }

    public function test_completed_before_50_percent_excellent(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(48),
            'sla_deadline' => now()->addHours(48),
            'completed_at' => now()->subHours(12),
        ]);
        $this->assertEquals('Excellent', $ticket->sla_status);
    }

    public function test_completed_between_50_100_normal(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(24),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(2),
        ]);
        $this->assertEquals('Normal', $ticket->sla_status);
    }

    public function test_completed_after_deadline_poor(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subDays(2),
            'sla_deadline' => now()->subDay(),
            'completed_at' => now()->subHours(6),
        ]);
        $this->assertEquals('Poor', $ticket->sla_status);
    }

    public function test_calculation_uses_completed_at_not_now(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(24),
            'sla_deadline' => now()->subHours(2),
            'completed_at' => now()->subHours(4),
        ]);
        // elapsed = 20h / 22h = 90.9% → Normal (50-100%)
        $this->assertEquals('Normal', $ticket->sla_status);
    }

    public function test_sla_remains_days_based(): void
    {
        $p = \App\Models\SlaPolicy::where('priority', 'high')->first();
        $this->assertEquals(2, $p->resolution_days);
    }

    // ─── STAFF ALL TICKETS ───────────────────────────────────

    public function test_staff_can_access_all_tickets(): void
    {
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.all'));
        $response->assertStatus(200);
    }

    public function test_staff_can_see_unassigned_wc_tickets(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.all'));
        $response->assertSee($ticket->ticket_number);
    }

    public function test_staff_sees_assign_to_me_button(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.all'));
        $response->assertSee('Assign to Me');
    }

    public function test_staff_can_assign_unassigned_ticket_to_self(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assignee_id);
    }

    public function test_staff_cannot_assign_ticket_to_another(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->manager->id]);
        $response->assertStatus(403);
    }

    public function test_staff_cannot_assign_already_assigned_ticket(): void
    {
        $staff2 = User::factory()->create(['name' => 'Other Staff', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $ticket = $this->makeTicket([
            'user_id' => $this->staff->id,
            'assignee_id' => $staff2->id,
        ]);
        $this->actingAs($this->staff);
        $response = $this->postJson(route('tickets.take', $ticket->id));
        $response->assertStatus(422);
    }

    public function test_assign_to_me_keeps_status_waiting_confirmation(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $ticket->refresh();
        $this->assertEquals('Waiting Confirmation', $ticket->status);
    }

    public function test_assignment_creates_timeline_entry(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $ticket->refresh();
        $timeline = $ticket->timeline;
        $this->assertNotEmpty($timeline);
    }

    // ─── MANAGER WORKFLOW UNCHANGED ──────────────────────────

    public function test_manager_can_still_assign_staff(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assignee_id);
        $this->assertEquals('Waiting Confirmation', $ticket->status);
    }
}
