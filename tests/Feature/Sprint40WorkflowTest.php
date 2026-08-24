<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Role;
use App\Models\SlaPolicy;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint40WorkflowTest extends TestCase
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

        $this->admin = User::factory()->create([
            'role_id' => Role::where('slug', 'admin')->value('id'),
        ]);
        $this->manager = User::factory()->create([
            'role_id' => Role::where('slug', 'manager')->value('id'),
        ]);
        $this->staff = User::factory()->create([
            'name' => 'IT Staff',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
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

    // ─── 1-3: New ticket starts OPEN, Unassigned, No SLA ─────

    public function test_new_ticket_starts_open(): void
    {
        $this->actingAs($this->employee);
        $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'description' => 'New ticket workflow test',
        ]);
        $ticket = Ticket::where('description', 'New ticket workflow test')->first();
        $this->assertEquals('Waiting Confirmation', $ticket->status);
    }

    public function test_new_ticket_is_unassigned(): void
    {
        $this->actingAs($this->employee);
        $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'description' => 'Unassigned check',
        ]);
        $ticket = Ticket::where('description', 'Unassigned check')->first();
        $this->assertNull($ticket->assignee_id);
    }

    public function test_new_ticket_has_no_sla(): void
    {
        $this->actingAs($this->employee);
        $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'description' => 'No SLA check',
        ]);
        $ticket = Ticket::where('description', 'No SLA check')->first();
        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_deadline);
    }

    // ─── 4-6: Staff can Take Ticket ──────────────────────────

    public function test_staff_can_take_ticket(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->postJson(route('tickets.take', $ticket->id));
        $response->assertOk();
    }

    public function test_take_ticket_assigns_current_user(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assignee_id);
    }

    public function test_take_ticket_moves_to_in_progress(): void
    {
        $ticket = $this->makeTicket(['status' => 'Waiting Confirmation']);
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $ticket->refresh();
        $this->assertEquals('In Progress', $ticket->status);
    }

    // ─── 7: Staff cannot assign to another user ──────────────

    public function test_staff_cannot_assign_to_another_user(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->patchJson(route('tickets.assign', $ticket->id), [
            'assignee_id' => $this->employee->id,
        ]);
        $response->assertStatus(403);
    }

    // ─── 8: Staff cannot modify SLA ──────────────────────────

    public function test_staff_cannot_modify_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $response->assertStatus(403);
    }

    // ─── 9-10: Manager can assign ────────────────────────────

    public function test_manager_can_assign_to_staff(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assignee_id);
    }

    public function test_manager_can_assign_to_named_staff(): void
    {
        $shohibul = User::factory()->create([
            'name' => 'Shohibul Anwar',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
        $riyanto = User::factory()->create([
            'name' => 'Riyanto',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);

        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);

        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $riyanto->id]);
        $ticket->refresh();
        $this->assertEquals($riyanto->id, $ticket->assignee_id);
    }

    // ─── 11: Manager can Take Ticket ─────────────────────────

    public function test_manager_can_take_ticket(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->postJson(route('tickets.take', $ticket->id));
        $ticket->refresh();
        $this->assertEquals($this->manager->id, $ticket->assignee_id);
        // Assignment now starts work: take moves the ticket to In Progress.
        $this->assertEquals('In Progress', $ticket->status);
    }

    // ─── 12-15: Manager can set SLA ──────────────────────────

    public function test_manager_can_set_low_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'low']);
        $ticket->refresh();
        $this->assertEquals('low', $ticket->sla_priority);
        $this->assertNotNull($ticket->sla_deadline);
    }

    public function test_manager_can_set_medium_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'medium']);
        $ticket->refresh();
        $this->assertEquals('medium', $ticket->sla_priority);
    }

    public function test_manager_can_set_high_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $ticket->refresh();
        $this->assertEquals('high', $ticket->sla_priority);
        $this->assertEquals(48, $ticket->sla_started_at->diffInHours($ticket->sla_deadline));
    }

    public function test_manager_can_set_critical_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'critical']);
        $ticket->refresh();
        $this->assertEquals('critical', $ticket->sla_priority);
        $this->assertEquals(24, $ticket->sla_started_at->diffInHours($ticket->sla_deadline));
    }

    // ─── 16: Missing SLA policy returns error ────────────────

    public function test_missing_sla_policy_returns_error(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        SlaPolicy::where('priority', 'low')->update(['is_active' => false]);
        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'low']);
        $response->assertStatus(422);
    }

    // ─── 17: No SLA clears fields ────────────────────────────

    public function test_no_sla_clears_sla_fields(): void
    {
        $ticket = $this->makeTicket([
            'sla_priority' => 'high',
            'sla_started_at' => now(),
            'sla_deadline' => now()->addHours(8),
        ]);
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => null]);
        $ticket->refresh();
        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_deadline);
    }

    // ─── 18: SLA deadline uses resolution_hours ──────────────

    public function test_sla_deadline_uses_resolution_hours(): void
    {
        $ticket = $this->makeTicket();
        $policy = SlaPolicy::where('priority', 'high')->where('is_active', true)->first();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $ticket->refresh();
        $this->assertEquals($policy->resolution_hours, $ticket->sla_started_at->diffInHours($ticket->sla_deadline));
    }

    // ─── 19: Assignment generates audit log ──────────────────

    public function test_assignment_generates_audit_log(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $audit = AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->where('event', 'assigned')
            ->first();
        $this->assertNotNull($audit);
    }

    // ─── 20: Status transition generates audit log ───────────

    public function test_status_transition_generates_audit_log(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $audit = AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->whereIn('event', ['assigned', 'status_changed'])
            ->first();
        $this->assertNotNull($audit);
    }

    // ─── 21: SLA change generates audit log ──────────────────

    public function test_sla_change_generates_audit_log(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $audit = AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->whereIn('event', ['priority_changed', 'sla_updated'])
            ->first();
        $this->assertNotNull($audit);
    }

    // ─── 22: Ticket Details renders correct action ───────────

    public function test_show_renders_take_ticket_for_open_unassigned(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertOk();
        $response->assertSee('Take Ticket');
        $response->assertSee('waiting for');
    }

    // ─── 23: Staff does not see manager-only controls ────────

    public function test_staff_does_not_see_sla_dropdown(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'In Progress']);
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.show', $ticket->id));
        $content = $response->getContent();
        $this->assertStringNotContainsString('SLA Priority', $content);
    }

    // ─── 24: Manager/Admin sees assignment and SLA controls ──

    public function test_manager_sees_sla_and_assign_controls(): void
    {
        $ticket = $this->makeTicket(['status' => 'In Progress']);
        $this->actingAs($this->manager);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertOk();
        $response->assertSee('SLA Priority');
        $response->assertSee('Assignee');
    }
}
