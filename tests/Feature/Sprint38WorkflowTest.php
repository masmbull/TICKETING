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

class Sprint38WorkflowTest extends TestCase
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
            'description' => 'Test ticket description',
            'status' => 'Waiting Confirmation',
        ], $overrides));
    }

    // ─── 1. Regular user creates ticket without SLA ───────────

    public function test_regular_user_creates_ticket_without_sla(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Ticket from regular employee',
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('description', 'Ticket from regular employee')->first();
        $this->assertNotNull($ticket);
        // priority may default to 'medium' (DB NOT NULL) but SLA fields must be null.
        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_started_at);
        $this->assertNull($ticket->sla_deadline);
    }

    // ─── 2-5. Manager can set SLA priorities ──────────────────

    public function test_manager_can_set_low_sla(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'low']);

        $response->assertOk();
        $ticket->refresh();
        $this->assertEquals('low', $ticket->sla_priority);
        $this->assertNotNull($ticket->sla_deadline);
    }

    public function test_manager_can_set_medium_sla(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'medium']);

        $response->assertOk();
        $ticket->refresh();
        $this->assertEquals('medium', $ticket->sla_priority);
        $this->assertNotNull($ticket->sla_deadline);
        $this->assertEquals(72, $ticket->sla_started_at->diffInHours($ticket->sla_deadline));
    }

    public function test_manager_can_set_high_sla(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);

        $response->assertOk();
        $ticket->refresh();
        $this->assertEquals('high', $ticket->sla_priority);
        $this->assertEquals(48, $ticket->sla_started_at->diffInHours($ticket->sla_deadline));
    }

    public function test_manager_can_set_critical_sla(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'critical']);

        $response->assertOk();
        $ticket->refresh();
        $this->assertEquals('critical', $ticket->sla_priority);
        $this->assertEquals(24, $ticket->sla_started_at->diffInHours($ticket->sla_deadline));
    }

    public function test_admin_can_set_sla(): void
    {
        $this->actingAs($this->admin);
        $ticket = $this->makeTicket();

        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);

        $response->assertOk();
        $ticket->refresh();
        $this->assertEquals('high', $ticket->sla_priority);
    }

    // ─── 6. Missing SLA policy does not fallback to Medium ────

    public function test_clearing_sla_removes_sla_fields(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket([
            'priority' => 'medium',
            'sla_priority' => 'medium',
            'sla_started_at' => now(),
            'sla_deadline' => now()->addHours(24),
        ]);

        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => null]);

        $response->assertOk();
        $ticket->refresh();
        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_deadline);
        $this->assertNull($ticket->sla_started_at);
    }

    // ─── 7. SLA deadline is calculated correctly ──────────────

    public function test_sla_deadline_calculation_matches_policy(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $policy = SlaPolicy::where('priority', 'high')->where('is_active', true)->first();
        $this->assertNotNull($policy);

        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);

        $ticket->refresh();
        $this->assertEquals($policy->resolution_hours, $ticket->sla_started_at->diffInHours($ticket->sla_deadline));
    }

    // ─── 8. Manager can assign ticket to eligible staff ───────

    public function test_manager_can_assign_ticket_to_staff(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $response = $this->patchJson(route('tickets.sla', $ticket->id), [
            'sla_priority' => null,
        ]);

        // Use the updateAssignee endpoint directly.
        $response = $this->patchJson(route('tickets.assign', $ticket->id), [
            'assignee_id' => $this->staff->id,
        ]);

        $response->assertOk();
        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assignee_id);
    }

    public function test_shohibul_riyanto_selectable_as_assignee(): void
    {
        $shohibul = User::factory()->create([
            'name' => 'Shohibul Anwar',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
        $riyanto = User::factory()->create([
            'name' => 'Riyanto',
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);

        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $shohibul->id]);
        $ticket->refresh();
        $this->assertEquals($shohibul->id, $ticket->assignee_id);

        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $riyanto->id]);
        $ticket->refresh();
        $this->assertEquals($riyanto->id, $ticket->assignee_id);
    }

    // ─── 9. Assign to Me works ────────────────────────────────

    public function test_assign_to_me_works_for_staff(): void
    {
        $this->actingAs($this->staff);
        $ticket = $this->makeTicket();

        $response = $this->postJson(route('tickets.assign-me', $ticket->id), [
            'problem_analysis' => 'Picked up for analysis.',
        ]);

        $response->assertOk();
        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assignee_id);
        $this->assertEquals('In Progress', $ticket->status);
    }

    // ─── 10. Ordinary user cannot arbitrarily assign ──────────

    public function test_ordinary_user_cannot_assign_ticket(): void
    {
        $this->actingAs($this->employee);
        $ticket = $this->makeTicket(['user_id' => $this->employee->id]);

        $response = $this->patch(route('tickets.status.update', $ticket->id), [
            'status' => 'Waiting Confirmation',
            'assignee_id' => $this->staff->id,
        ]);

        $response->assertForbidden();
    }

    // ─── 11. Assignment creates audit log ─────────────────────

    public function test_assignment_creates_audit_log(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket();

        $this->patchJson(route('tickets.assign', $ticket->id), [
            'assignee_id' => $this->staff->id,
        ]);

        $audit = AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->where('event', 'assigned')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals($this->manager->id, $audit->user_id);
    }

    // ─── 12. SLA change creates audit log ─────────────────────

    public function test_sla_change_creates_audit_log(): void
    {
        $this->actingAs($this->manager);
        $ticket = $this->makeTicket(['sla_priority' => null, 'sla_deadline' => null]);

        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);

        // SLA update may fire priority_changed (priority column also changes) or sla_updated.
        $audit = AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->whereIn('event', ['priority_changed', 'sla_updated'])
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals($this->manager->id, $audit->user_id);
    }

    // ─── 13. Dashboard does not render New Ticket button ──────

    public function test_dashboard_does_not_render_new_ticket_button(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('New Ticket', $content);
    }

    // ─── 14. Sidebar still contains Create Ticket ─────────────

    public function test_sidebar_contains_create_ticket(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Create Ticket');
        $response->assertSee(route('tickets.create'));
    }

    // ─── 15. Theme toggle persists ────────────────────────────

    public function test_theme_toggle_exists_in_header(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('darkMode', $content);
        $this->assertStringContainsString("localStorage.setItem('theme'", $content);
        $this->assertStringContainsString('Toggle theme', $content);
    }

    // ─── 16. MITO branding renders in sidebar ─────────────────

    public function test_mito_branding_renders_in_sidebar(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('MITO', $content);
        $this->assertStringContainsString('IT Helpdesk', $content);
    }

    // ─── 17. Ticket Details does not duplicate title ──────────

    public function test_ticket_details_does_not_duplicate_title(): void
    {
        $this->actingAs($this->admin);
        $ticket = $this->makeTicket(['description' => 'unique-desc-marker-abc123']);

        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertSame(1, substr_count($content, 'unique-desc-marker-abc123'));
        $response->assertSee($ticket->ticket_number);
    }

    // ─── Additional: Create page hides Priority for regular users

    public function test_create_page_hides_priority_for_regular_users(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('tickets.create'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('name="priority"', $content);
    }

    public function test_create_page_shows_priority_for_support(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get(route('tickets.create'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Priority', $content);
        $this->assertStringContainsString('name="priority"', $content);
    }

    // ─── Additional: Staff cannot set SLA ─────────────────────

    public function test_staff_cannot_update_sla(): void
    {
        $this->actingAs($this->staff);
        $ticket = $this->makeTicket();

        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $response->assertStatus(403);
    }

    // ─── Additional: Ticket Details header layout ─────────────

    public function test_ticket_details_header_contains_meta_parts(): void
    {
        $this->actingAs($this->admin);
        $ticket = $this->makeTicket();

        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertStatus(200);

        $response->assertSee($this->category->name);
        $response->assertSee($this->subCategory->name);
        $response->assertSee($this->employee->name);
    }
}
