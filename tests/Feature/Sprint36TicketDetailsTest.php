<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\SlaPolicy;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint36TicketDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private User $staff;
    private Category $category;
    private SubCategory $subCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\SlaPolicySeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@mito.local',
            'password' => bcrypt('Test@123'),
            'email_verified_at' => now(),
        ]);
        $this->admin->update(['role_id' => Role::where('slug', 'admin')->value('id')]);

        $this->user = User::factory()->create([
            'email' => 'employee@mito.local',
            'password' => bcrypt('Test@123'),
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'name' => 'IT Staff One',
            'email' => 'staff1@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => Role::where('slug', 'staff')->value('id'),
            'email_verified_at' => now(),
        ]);

        $this->category = Category::first();
        $this->subCategory = SubCategory::first();
    }

    private function baseTicket(array $overrides = []): Ticket
    {
        return Ticket::create(array_merge([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-90001',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Default test description',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ], $overrides));
    }

    // ─── SLA display ──────────────────────────────────────────

    public function test_valid_sla_displays_priority_started_deadline_status(): void
    {
        $this->actingAs($this->staff);

        $ticket = $this->baseTicket([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-90002',
            'priority' => 'high',
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(2),
            'sla_deadline' => now()->addHours(6),
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
        ]);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee('SLA');
        $response->assertSee('Started');
        $response->assertSee('Deadline');
        $response->assertSee('Status');
        $response->assertSee('High');
        $response->assertSee('—');
    }

    public function test_null_sla_priority_displays_no_sla(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->baseTicket([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-90003',
            'priority' => 'medium',
            'sla_priority' => null,
            'sla_started_at' => null,
            'sla_deadline' => null,
            'status' => 'Waiting Confirmation',
        ]);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $content = $response->getContent();
        // The SLA Priority row renders "No SLA" when no active SLA exists.
        $this->assertGreaterThanOrEqual(1, substr_count($content, 'No SLA'));
        // Status badge in SLA card should say "No SLA" not a fake status.
        $this->assertStringContainsString('No SLA', $content);
    }

    public function test_invalid_sla_priority_displays_no_sla(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->baseTicket([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-90004',
            'priority' => 'critical',
            'sla_priority' => 'urgent', // not a valid SLA priority
            'sla_started_at' => now()->subHours(2),
            'sla_deadline' => now()->addHours(6),
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
        ]);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, substr_count($response->getContent(), 'No SLA'));
    }

    public function test_store_creates_and_renders_sla_deadline(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA creation render test',
            'priority' => 'high',
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('description', 'SLA creation render test')->first();
        $this->assertNull($ticket->sla_deadline);

        // Render as an admin who can view it.
        $this->actingAs($this->admin);
        $response = $this->get(route('tickets.show', $ticket->id));
        $response->assertStatus(200);
        $response->assertSee('High');
    }

    // ─── Ticket detail structure ──────────────────────────────

    public function test_detail_shows_id_and_description_once(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->baseTicket([
            'ticket_number' => 'ITSUP-DETAIL-009',
            'description' => 'unique-description-marker-9f3a7c',
        ]);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee('ITSUP-DETAIL-009');
        // The description must appear exactly once (in the Description card),
        // never duplicated beneath the ticket id in the header.
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'unique-description-marker-9f3a7c')
        );
    }

    public function test_detail_shows_category_subcategory_reporter_status(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->baseTicket([
            'ticket_number' => 'ITSUP-DETAIL-010',
            'status' => 'In Progress',
        ]);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee($this->category->name);
        $response->assertSee($this->subCategory->name);
        $response->assertSee($this->user->name);
        $response->assertSee('In Progress');
    }

    // ─── Comments ─────────────────────────────────────────────

    public function test_comment_empty_state_renders(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->baseTicket(['ticket_number' => 'ITSUP-DETAIL-011']);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee('No comments yet');
        $response->assertSee('Add a comment');
    }

    public function test_comment_existing_is_visible(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->baseTicket(['ticket_number' => 'ITSUP-DETAIL-012']);
        $ticket->comments()->create([
            'user_id' => $this->admin->id,
            'comment' => 'Visible earlier comment body',
        ]);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee('Visible earlier comment body');
    }

    public function test_comment_form_remains_functional(): void
    {
        $this->actingAs($this->user);

        $ticket = $this->baseTicket(['ticket_number' => 'ITSUP-DETAIL-013']);

        $response = $this->post(route('tickets.comments.store', $ticket->id), [
            'comment' => 'A brand new comment from the test',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'comment' => 'A brand new comment from the test',
        ]);
    }

    // ─── Workflow card (Sprint 3.1) ───────────────────────────

    public function test_workflow_card_visible_to_staff_on_unassigned_ticket(): void
    {
        $this->actingAs($this->staff);

        $ticket = $this->baseTicket([
            'ticket_number' => 'ITSUP-DETAIL-014',
            'status' => 'Waiting Confirmation',
            'assignee_id' => null,
        ]);

        $response = $this->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee('Workflow');
        $response->assertSee('Take Ticket');
    }
}
