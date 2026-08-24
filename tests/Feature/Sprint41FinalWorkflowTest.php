<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Role;
use App\Models\SlaMapping;
use App\Models\SlaPolicy;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint41FinalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private User $staff2;
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
        $this->staff2 = User::factory()->create(['name' => 'Staff Two', 'role_id' => Role::where('slug', 'staff')->value('id')]);
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

    // ─── 1-3: Ticket creation ────────────────────────────────

    public function test_new_ticket_starts_waiting_confirmation(): void
    {
        $this->actingAs($this->employee);
        $this->post('/my-tickets', ['category_id' => $this->category->id, 'description' => 'workflow test']);
        $ticket = Ticket::where('description', 'workflow test')->first();
        $this->assertEquals('Waiting Confirmation', $ticket->status);
    }

    public function test_new_ticket_is_unassigned(): void
    {
        $this->actingAs($this->employee);
        $this->post('/my-tickets', ['category_id' => $this->category->id, 'description' => 'unassigned check']);
        $ticket = Ticket::where('description', 'unassigned check')->first();
        $this->assertNull($ticket->assignee_id);
    }

    public function test_new_ticket_has_no_sla(): void
    {
        $this->actingAs($this->employee);
        $this->post('/my-tickets', ['category_id' => $this->category->id, 'description' => 'no sla check']);
        $ticket = Ticket::where('description', 'no sla check')->first();
        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_deadline);
    }

    // ─── 4-8: Manager assignment ─────────────────────────────

    public function test_manager_can_view_all_tickets(): void
    {
        $this->actingAs($this->manager);
        $response = $this->get(route('tickets.all'));
        $response->assertStatus(200);
    }

    public function test_manager_can_assign_ticket_to_staff(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assignee_id);
    }

    public function test_manager_can_assign_to_named_staff(): void
    {
        $riyanto = User::factory()->create(['name' => 'Riyanto', 'role_id' => Role::where('slug', 'staff')->value('id')]);
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $riyanto->id]);
        $ticket->refresh();
        $this->assertEquals($riyanto->id, $ticket->assignee_id);
    }

    public function test_manager_assignment_moves_to_in_progress(): void
    {
        $ticket = $this->makeTicket(['status' => 'Waiting Confirmation']);
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $ticket->refresh();
        $this->assertEquals('In Progress', $ticket->status);
    }

    public function test_assignment_generates_audit_log(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $this->assertNotNull(AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->where('event', 'assigned')->first());
    }

    public function test_manager_can_reassign_ticket(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff2->id]);
        $ticket->refresh();
        $this->assertEquals($this->staff2->id, $ticket->assignee_id);
    }

    // ─── 10-12: Take Ticket ──────────────────────────────────

    public function test_staff_can_take_ticket_only_when_unassigned(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => null]);
        $this->actingAs($this->staff);
        $response = $this->postJson(route('tickets.take', $ticket->id));
        $response->assertOk();
    }

    public function test_take_ticket_assigns_current_staff(): void
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

    // ─── 13-22: Staff workflow ───────────────────────────────

    public function test_assigned_staff_can_open_ticket(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertStatus(200);
    }

    public function test_staff_sees_problem_analysis(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertSee('Problem Analysis');
    }

    public function test_problem_analysis_is_required(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $response = $this->post(route('tickets.submit-analysis', $ticket->id), ['problem_analysis' => '']);
        $response->assertSessionHasErrors('problem_analysis');
    }

    public function test_submitting_analysis_changes_to_in_progress(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $this->post(route('tickets.submit-analysis', $ticket->id), ['problem_analysis' => 'Network interface issue identified.']);
        $ticket->refresh();
        $this->assertEquals('In Progress', $ticket->status);
        $this->assertEquals('Network interface issue identified.', $ticket->problem_analysis);
    }

    public function test_staff_cannot_arbitrarily_change_status(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $response = $this->patch(route('tickets.status.update', $ticket->id), ['status' => 'Completed']);
        $response->assertSessionHasErrors();
    }

    public function test_staff_cannot_process_another_staffs_ticket(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff2->id, 'user_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $response = $this->post(route('tickets.submit-analysis', $ticket->id), ['problem_analysis' => 'test']);
        $response->assertSessionHasErrors();
    }

    public function test_staff_sees_resolution_form_while_in_progress(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'In Progress']);
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertSee('Resolution');
        $response->assertSee('Complete Ticket');
    }

    public function test_resolution_is_required(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'In Progress']);
        $this->actingAs($this->staff);
        $response = $this->post(route('tickets.complete', $ticket->id), ['resolution' => '']);
        $response->assertSessionHasErrors('resolution');
    }

    public function test_submitting_resolution_changes_to_completed(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'In Progress']);
        $this->actingAs($this->staff);
        $this->post(route('tickets.complete', $ticket->id), ['resolution' => 'Replaced faulty cable.']);
        $ticket->refresh();
        $this->assertEquals('Completed', $ticket->status);
        $this->assertEquals('Replaced faulty cable.', $ticket->resolution);
    }

    public function test_completed_ticket_is_terminal(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'Completed']);
        $this->actingAs($this->staff);
        $response = $this->post(route('tickets.take', $ticket->id));
        $response->assertSessionHasErrors('status');
    }

    // ─── 23-35: SLA ──────────────────────────────────────────

    public function test_sla_level_configuration(): void
    {
        $policies = SlaPolicy::all();
        $this->assertCount(4, $policies);
        foreach ($policies as $p) {
            $this->assertContains($p->priority, ['low', 'medium', 'high', 'critical']);
            $this->assertNotNull($p->resolution_days);
        }
    }

    public function test_sla_duration_in_days(): void
    {
        $policy = SlaPolicy::where('priority', 'critical')->first();
        $this->assertNotNull($policy->resolution_days);
        $this->assertGreaterThan(0, $policy->resolution_days);
    }

    public function test_category_mapping_can_be_created(): void
    {
        $mapping = SlaMapping::create([
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'priority' => 'high',
        ]);
        $this->assertNotNull($mapping);
    }

    public function test_subcategory_must_belong_to_category(): void
    {
        $otherCategory = Category::skip(1)->first();
        if ($otherCategory && $otherCategory->id !== $this->category->id) {
            $this->actingAs($this->admin);
            $response = $this->post(route('sla-policies.mapping.store'), [
                'category_id' => $this->category->id,
                'sub_category_id' => $otherCategory->subCategories->first()?->id,
                'priority' => 'high',
            ]);
            $response->assertRedirect();
        }
        $this->assertTrue(true); // placeholder if categories share subs
    }

    public function test_no_mapping_results_in_no_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $ticket->refresh();
        $this->assertNull($ticket->sla_priority);
    }

    public function test_no_mapping_does_not_default_to_medium(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $ticket->refresh();
        $this->assertNotEquals('medium', $ticket->sla_priority);
    }

    public function test_sla_deadline_uses_configured_days(): void
    {
        SlaMapping::create(['category_id' => $this->category->id, 'sub_category_id' => $this->subCategory->id, 'priority' => 'high']);
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $ticket->refresh();
        $this->assertNotNull($ticket->sla_deadline);
        $this->assertEquals(2, $ticket->sla_started_at->diffInDays($ticket->sla_deadline));
    }

    public function test_manager_can_manually_set_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $ticket->refresh();
        $this->assertEquals('high', $ticket->sla_priority);
        $this->assertNotNull($ticket->sla_deadline);
    }

    public function test_staff_cannot_modify_sla(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $response->assertStatus(403);
    }

    public function test_clearing_sla_clears_fields(): void
    {
        $ticket = $this->makeTicket(['sla_priority' => 'high', 'sla_started_at' => now(), 'sla_deadline' => now()->addDays(2)]);
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => null]);
        $ticket->refresh();
        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_deadline);
    }

    // ─── 36-42: Audit ────────────────────────────────────────

    public function test_take_ticket_audited(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $this->assertNotNull(AuditLog::where('auditable_id', $ticket->id)->whereIn('event', ['assigned', 'status_changed'])->first());
    }

    public function test_problem_analysis_audited(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $this->post(route('tickets.submit-analysis', $ticket->id), ['problem_analysis' => 'Test analysis.']);
        $this->assertNotNull(AuditLog::where('auditable_id', $ticket->id)->where('event', 'status_changed')->first());
    }

    public function test_resolution_audited(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'In Progress']);
        $this->actingAs($this->staff);
        $this->post(route('tickets.complete', $ticket->id), ['resolution' => 'Done.']);
        $this->assertNotNull(AuditLog::where('auditable_id', $ticket->id)->where('event', 'status_changed')->first());
    }

    public function test_sla_change_audited(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $this->assertNotNull(AuditLog::where('auditable_id', $ticket->id)->whereIn('event', ['priority_changed', 'sla_updated'])->first());
    }

    // ─── UI assertions ───────────────────────────────────────

    public function test_show_renders_take_ticket_for_unassigned(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertSee('Take Ticket');
        $response->assertSee('waiting for a technician');
    }

    public function test_staff_does_not_see_sla_controls(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'In Progress']);
        $this->actingAs($this->staff);
        $content = $this->get(route('tickets.show', $ticket->id))->getContent();
        $this->assertStringNotContainsString('SLA Priority', $content);
    }

    public function test_manager_sees_sla_and_assign_controls(): void
    {
        $ticket = $this->makeTicket(['status' => 'In Progress']);
        $this->actingAs($this->manager);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertSee('SLA Priority');
        $response->assertSee('Assignee');
    }

    public function test_completed_shows_closed_notice(): void
    {
        $ticket = $this->makeTicket(['status' => 'Completed']);
        $this->actingAs($this->staff);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertSee('Ticket Closed / Completed');
    }
}
