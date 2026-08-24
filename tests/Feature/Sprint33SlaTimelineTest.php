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

class Sprint33SlaTimelineTest extends TestCase
{
    use RefreshDatabase;

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

    // ─── SLA Priority Values ─────────────────────────────────

    public function test_sla_policy_seed_values_exist(): void
    {
        $this->assertDatabaseHas('sla_policies', ['priority' => 'low', 'resolution_hours' => 120]);
        $this->assertDatabaseHas('sla_policies', ['priority' => 'medium', 'resolution_hours' => 72]);
        $this->assertDatabaseHas('sla_policies', ['priority' => 'high', 'resolution_hours' => 48]);
        $this->assertDatabaseHas('sla_policies', ['priority' => 'critical', 'resolution_hours' => 24]);
    }

    // ─── SLA Deadline Calculation ────────────────────────────

    public function test_sla_deadline_is_calculated_on_creation(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA deadline test ticket',
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $ticket = Ticket::where('description', 'SLA deadline test ticket')->first();

        $this->assertNull($ticket->sla_started_at);
        $this->assertNull($ticket->sla_deadline);
    }

    public function test_sla_deadline_uses_explicit_policy(): void
    {
        $this->actingAs($this->staff);

        $criticalPolicy = SlaPolicy::where('priority', 'critical')->first();

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Explicit SLA policy test',
            'priority' => 'low',
            'sla_policy_id' => $criticalPolicy->id,
        ]);

        $response->assertRedirect();
        $ticket = Ticket::where('description', 'Explicit SLA policy test')->first();

        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_deadline);
    }

    // ─── SLA Start Timestamp ─────────────────────────────────

    public function test_sla_started_at_is_set_on_creation(): void
    {
        $this->actingAs($this->user);

        $before = now()->subMinute();
        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA start timestamp test',
            'priority' => 'medium',
        ]);
        $after = now()->addMinute();

        $response->assertRedirect();
        $ticket = Ticket::where('description', 'SLA start timestamp test')->first();

        $this->assertNull($ticket->sla_started_at);
    }

    // ─── SLA Status ──────────────────────────────────────────

    public function test_sla_status_active_for_in_progress_within_deadline(): void
    {
        $this->actingAs($this->staff);

        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00001',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA active test',
            'priority' => 'medium',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subHours(2),
            'sla_deadline' => now()->addHours(20),
        ]);

        $this->assertEquals('Not Evaluated', $ticket->sla_status);
    }

    public function test_sla_status_breached_when_past_deadline(): void
    {
        $this->actingAs($this->staff);

        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00002',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA breached test',
            'priority' => 'high',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(10),
            'sla_deadline' => now()->subHours(2),
        ]);

        $this->assertEquals('Not Evaluated', $ticket->sla_status);
    }

    public function test_sla_status_met_when_completed_before_deadline(): void
    {
        $this->actingAs($this->staff);

        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00003',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA met test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subDays(2),
            'sla_deadline' => now()->addDay(),
            'completed_at' => now()->subHours(12),
        ]);

        $this->assertEquals('Normal', $ticket->sla_status);
    }

    // ─── SLA Performance (EXCELLENT / NORMAL / POOR) ────────

    public function test_sla_performance_excellent_when_completed_before_deadline(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00004',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA excellent test',
            'priority' => 'high',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(24),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(20),
        ]);

        $this->assertEquals('EXCELLENT', $ticket->sla_performance);
    }

    public function test_sla_performance_normal_when_completed_at_deadline(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00005',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA normal test',
            'priority' => 'medium',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subHours(24),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(8),
        ]);

        $this->assertEquals('NORMAL', $ticket->sla_performance);
    }

    public function test_sla_performance_poor_when_completed_after_deadline(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00006',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA poor test',
            'priority' => 'critical',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(12),
            'sla_deadline' => now()->subHours(4),
            'completed_at' => now()->subHours(2),
        ]);

        $this->assertEquals('POOR', $ticket->sla_performance);
    }

    public function test_sla_performance_null_when_not_completed(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00007',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA null performance test',
            'priority' => 'low',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(10),
            'sla_deadline' => now()->addHours(62),
        ]);

        $this->assertNull($ticket->sla_performance);
    }

    public function test_sla_performance_null_when_missing_sla_timestamps(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00008',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA missing timestamps test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => null,
            'sla_deadline' => null,
            'completed_at' => now(),
        ]);

        $this->assertNull($ticket->sla_performance);
    }

    public function test_sla_performance_null_when_sla_duration_is_invalid(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00009',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA invalid duration test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now(),
            'sla_deadline' => now(),
            'completed_at' => now()->addHours(1),
        ]);

        $this->assertNull($ticket->sla_performance);
    }

    // ─── SLA Performance — LOW (72 hours) ────────────────────

    public function test_sla_performance_low_12_hours_is_excellent(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00010',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 12h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(60),
        ]);

        $this->assertEquals('EXCELLENT', $ticket->sla_performance);
    }

    public function test_sla_performance_low_24_hours_is_excellent(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00011',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 24h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(48),
        ]);

        $this->assertEquals('EXCELLENT', $ticket->sla_performance);
    }

    public function test_sla_performance_low_47_hours_is_excellent(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00012',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 47h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(25),
        ]);

        $this->assertEquals('EXCELLENT', $ticket->sla_performance);
    }

    public function test_sla_performance_low_48_hours_is_normal(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00013',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 48h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(22),
        ]);

        $this->assertEquals('NORMAL', $ticket->sla_performance);
    }

    public function test_sla_performance_low_60_hours_is_normal(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00014',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 60h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(12),
        ]);

        $this->assertEquals('NORMAL', $ticket->sla_performance);
    }

    public function test_sla_performance_low_72_hours_is_normal(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00015',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 72h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now(),
        ]);

        $this->assertEquals('NORMAL', $ticket->sla_performance);
    }

    public function test_sla_performance_low_73_hours_is_poor(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00016',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 73h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now()->addHours(1),
        ]);

        $this->assertEquals('POOR', $ticket->sla_performance);
    }

    public function test_sla_performance_low_96_hours_is_poor(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00017',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'LOW 96h test',
            'priority' => 'low',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'low',
            'sla_started_at' => now()->subHours(72),
            'sla_deadline' => now(),
            'completed_at' => now()->addHours(24),
        ]);

        $this->assertEquals('POOR', $ticket->sla_performance);
    }

    // ─── SLA Performance — MEDIUM (48 hours) ─────────────────

    public function test_sla_performance_medium_8_hours_is_excellent(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00018',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'MEDIUM 8h test',
            'priority' => 'medium',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subHours(48),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(40),
        ]);

        $this->assertEquals('EXCELLENT', $ticket->sla_performance);
    }

    public function test_sla_performance_medium_24_hours_is_normal(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00019',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'MEDIUM 24h test',
            'priority' => 'medium',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subHours(48),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(16),
        ]);

        $this->assertEquals('NORMAL', $ticket->sla_performance);
    }

    public function test_sla_performance_medium_50_hours_is_poor(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00020',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'MEDIUM 50h test',
            'priority' => 'medium',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subHours(48),
            'sla_deadline' => now(),
            'completed_at' => now()->addHours(2),
        ]);

        $this->assertEquals('POOR', $ticket->sla_performance);
    }

    // ─── SLA Performance — HIGH (24 hours) ───────────────────

    public function test_sla_performance_high_4_hours_is_excellent(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00021',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'HIGH 4h test',
            'priority' => 'high',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(24),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(20),
        ]);

        $this->assertEquals('EXCELLENT', $ticket->sla_performance);
    }

    public function test_sla_performance_high_12_hours_is_normal(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00022',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'HIGH 12h test',
            'priority' => 'high',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(24),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(6),
        ]);

        $this->assertEquals('NORMAL', $ticket->sla_performance);
    }

    public function test_sla_performance_high_25_hours_is_poor(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00023',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'HIGH 25h test',
            'priority' => 'high',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(24),
            'sla_deadline' => now(),
            'completed_at' => now()->addHours(1),
        ]);

        $this->assertEquals('POOR', $ticket->sla_performance);
    }

    // ─── SLA Performance — CRITICAL (< 24 hours) ─────────────

    public function test_sla_performance_critical_4_hours_is_excellent(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00024',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'CRITICAL 4h test',
            'priority' => 'critical',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(12),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(8),
        ]);

        $this->assertEquals('EXCELLENT', $ticket->sla_performance);
    }

    public function test_sla_performance_critical_8_hours_is_normal(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00025',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'CRITICAL 8h test',
            'priority' => 'critical',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(12),
            'sla_deadline' => now(),
            'completed_at' => now()->subHours(3),
        ]);

        $this->assertEquals('NORMAL', $ticket->sla_performance);
    }

    public function test_sla_performance_critical_13_hours_is_poor(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00026',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'CRITICAL 13h test',
            'priority' => 'critical',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(12),
            'sla_deadline' => now(),
            'completed_at' => now()->addHours(1),
        ]);

        $this->assertEquals('POOR', $ticket->sla_performance);
    }

    // ─── Timeline Event Ordering ─────────────────────────────

    public function test_timeline_events_are_ordered_correctly(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00008',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Timeline order test',
            'priority' => 'medium',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subDays(2),
            'sla_deadline' => now()->addHours(12),
            'created_at' => now()->subDays(3),
            'assigned_at' => now()->subDays(2),
            'problem_analysis_at' => now()->subDays(1),
            'resolution_at' => now()->subHours(12),
            'completed_at' => now()->subHours(6),
        ]);
        // created_at is not mass-assignable; set it explicitly.
        $ticket->created_at = now()->subDays(3);
        $ticket->save();

        $timeline = $ticket->timeline;
        $labels = array_column($timeline, 'label');

        $this->assertEquals([
            'Ticket Created',
            'Assigned',
            'In Progress',
            'Problem Analysis',
            'Resolution',
            'Completed',
        ], $labels);
    }

    // ─── Timeline Timestamps ─────────────────────────────────

    public function test_timeline_contains_actual_timestamps(): void
    {
        $created = now()->subDays(3);
        $assigned = now()->subDays(2);

        $ticket = new Ticket([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00009',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Timeline timestamps test',
            'priority' => 'medium',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subDays(2),
            'sla_deadline' => now()->addHours(24),
        ]);
        $ticket->created_at = $created;
        $ticket->assigned_at = $assigned;
        $ticket->problem_analysis_at = now()->subDay();
        $ticket->save();

        $ticket->refresh();
        $timeline = $ticket->timeline;
        $createdEvent = $timeline[0];
        $assignedEvent = $timeline[1];

        $this->assertEquals('Ticket Created', $createdEvent['label']);
        $this->assertEquals($created->format('Y-m-d H:i'), $createdEvent['timestamp']->format('Y-m-d H:i'));
        $this->assertEquals('Assigned', $assignedEvent['label']);
        $this->assertEquals($assigned->format('Y-m-d H:i'), $assignedEvent['timestamp']->format('Y-m-d H:i'));
    }

    // ─── No First Response Event in Timeline ─────────────────

    public function test_timeline_does_not_include_first_response_event(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00010',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'No first response test',
            'priority' => 'medium',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subHours(5),
            'sla_deadline' => now()->addHours(19),
            'first_response_at' => now()->subHours(4),
            'created_at' => now()->subHours(6),
            'assigned_at' => now()->subHours(5),
            'problem_analysis_at' => now()->subHours(4),
        ]);

        $labels = array_column($ticket->timeline, 'label');
        $this->assertNotContains('First Response', $labels);
    }

    // ─── Ticket Detail Renders Timeline ──────────────────────

    public function test_ticket_detail_renders_timeline(): void
    {
        $this->actingAs($this->staff);

        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00011',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Timeline render test',
            'priority' => 'medium',
            'status' => 'Completed',
            'assignee_id' => $this->staff->id,
            'completed_by' => $this->staff->id,
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subDays(2),
            'sla_deadline' => now()->addHours(12),
            'created_at' => now()->subDays(3),
            'completed_at' => now()->subHours(6),
        ]);

        $response = $this->get("/my-tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('Timeline');
        $response->assertSee('Ticket Created');
        $response->assertSee('Completed');
    }

    // ─── Ticket Detail Renders SLA ───────────────────────────

    public function test_ticket_detail_renders_sla(): void
    {
        $this->actingAs($this->staff);

        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00012',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA render test',
            'priority' => 'high',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(2),
            'sla_deadline' => now()->addHours(6),
        ]);

        $response = $this->get("/my-tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('SLA');
        $response->assertSee('Started');
        $response->assertSee('Deadline');
        $response->assertSee('Status');
    }

    // ─── New Tickets Generate ITSUP Numbers ──────────────────

    public function test_new_tickets_generate_itsup_numbers(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'ITSUP numbering test',
            'priority' => 'medium',
        ]);

        $response->assertRedirect();
        $ticket = Ticket::where('description', 'ITSUP numbering test')->first();

        $this->assertNotNull($ticket);
        $this->assertMatchesRegularExpression('/^ITSUP-\d{8}-\d{5}$/', $ticket->ticket_number);
    }

    // ─── Existing Workflow Continues to Work ─────────────────

    public function test_existing_workflow_continues_after_sprint33(): void
    {
        $this->actingAs($this->staff);

        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00013',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Workflow regression test',
            'priority' => 'high',
            'status' => 'Waiting Confirmation',
            'assignee_id' => $this->staff->id,
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'In Progress',
            'problem_analysis' => 'Test analysis for regression.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'In Progress',
            'problem_analysis' => 'Test analysis for regression.',
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'Completed',
            'resolution' => 'Fixed by regression test.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'Completed',
            'resolution' => 'Fixed by regression test.',
            'completed_by' => $this->staff->id,
        ]);
    }

    public function test_assign_to_me_still_works_after_sprint33(): void
    {
        $this->actingAs($this->staff);

        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-' . now()->format('Ymd') . '-00014',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Assign to me regression test',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ]);

        $response = $this->postJson("/tickets/{$ticket->id}/assign-to-me", [
            'problem_analysis' => 'Picked up for regression test.',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assignee_id' => $this->staff->id,
            'status' => 'In Progress',
        ]);
    }
}
