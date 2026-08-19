<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Role;
use App\Models\SlaMapping;
use App\Models\SlaPolicy;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint42NotificationSlaTest extends TestCase
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

    // ─── NOTIFICATION TESTS ──────────────────────────────────

    public function test_notification_on_assignment(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\TicketNotification',
            'notifiable_id' => $this->staff->id,
        ]);
    }

    public function test_notification_on_reassignment(): void
    {
        $staff2 = User::factory()->create(['role_id' => Role::where('slug', 'staff')->value('id')]);
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $staff2->id]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff2->id,
        ]);
    }

    public function test_notification_on_take_ticket(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->staff);
        $this->postJson(route('tickets.take', $ticket->id));
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->employee->id,
        ]);
    }

    public function test_notification_on_status_change(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id]);
        $this->actingAs($this->staff);
        $this->post(route('tickets.submit-analysis', $ticket->id), ['problem_analysis' => 'Analysis']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->employee->id,
        ]);
    }

    public function test_notification_on_sla_change(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.sla', $ticket->id), ['sla_priority' => 'high']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->employee->id,
        ]);
    }

    public function test_notification_on_comment(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->employee);
        $this->post(route('tickets.comments.store', $ticket->id), ['comment' => 'Hello from requester']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->employee->id,
        ]);
    }

    public function test_notification_on_completion(): void
    {
        $ticket = $this->makeTicket(['assignee_id' => $this->staff->id, 'status' => 'In Progress']);
        $this->actingAs($this->staff);
        $this->post(route('tickets.complete', $ticket->id), ['resolution' => 'Done.']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->employee->id,
        ]);
    }

    public function test_unread_count(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);

        $count = $this->staff->unreadNotifications()->count();
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function test_mark_as_read(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);

        $notification = $this->staff->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $this->actingAs($this->staff);
        $this->post(route('notifications.read', $notification->id));
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $ticket1 = $this->makeTicket();
        $ticket2 = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket1->id), ['assignee_id' => $this->staff->id]);
        $this->patchJson(route('tickets.assign', $ticket2->id), ['assignee_id' => $this->staff->id]);

        $this->actingAs($this->staff);
        $this->post(route('notifications.mark-all-read'));

        $unread = $this->staff->unreadNotifications()->count();
        $this->assertEquals(0, $unread);
    }

    public function test_notification_opens_correct_ticket(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);

        $notification = $this->staff->unreadNotifications()->first();
        $this->assertStringContainsString((string) $ticket->id, $notification->data['url']);
    }

    public function test_user_cannot_access_another_users_notifications(): void
    {
        $ticket = $this->makeTicket();
        $this->actingAs($this->manager);
        $this->patchJson(route('tickets.assign', $ticket->id), ['assignee_id' => $this->staff->id]);

        $notification = $this->staff->unreadNotifications()->first();

        $this->actingAs($this->employee);
        $response = $this->post(route('notifications.read', $notification->id));
        $response->assertStatus(404);
    }

    // ─── SLA AUTOMATIC STATUS TESTS ──────────────────────────

    public function test_sla_excellent_threshold(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(1),
            'sla_deadline' => now()->addHours(23),
            'completed_at' => now(),
        ]);
        $this->assertEquals('Excellent', $ticket->sla_status);
    }

    public function test_sla_normal_threshold(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(18),
            'sla_deadline' => now()->addHours(6),
            'completed_at' => now(),
        ]);
        $this->assertEquals('Normal', $ticket->sla_status);
    }

    public function test_sla_poor_threshold(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(25),
            'sla_deadline' => now()->subHours(1),
            'completed_at' => now(),
        ]);
        $this->assertEquals('Poor', $ticket->sla_status);
    }

    public function test_sla_status_critical(): void
    {
        // 11h elapsed / 24h total = 45.8% → Excellent
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'critical',
            'sla_started_at' => now()->subHours(11),
            'sla_deadline' => now()->addHours(13),
            'completed_at' => now(),
        ]);
        $this->assertEquals('Excellent', $ticket->sla_status);
    }

    public function test_sla_status_high(): void
    {
        // 23h elapsed / 48h total = 47.9% → Excellent
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(23),
            'sla_deadline' => now()->addHours(25),
            'completed_at' => now(),
        ]);
        $this->assertEquals('Excellent', $ticket->sla_status);
    }

    public function test_sla_status_medium(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'medium',
            'sla_started_at' => now()->subDays(2),
            'sla_deadline' => now()->addDays(1),
            'completed_at' => now(),
        ]);
        $this->assertEquals('Normal', $ticket->sla_status);
    }

    public function test_sla_status_low(): void
    {
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'low',
            'sla_started_at' => now()->subDays(3),
            'sla_deadline' => now()->addDays(2),
            'completed_at' => now(),
        ]);
        $this->assertEquals('Normal', $ticket->sla_status);
    }

    public function test_sla_status_cannot_be_manually_changed(): void
    {
        // 23h elapsed / 48h total = 47.9% → Excellent
        $ticket = $this->makeTicket([
            'status' => 'Completed',
            'sla_priority' => 'high',
            'sla_started_at' => now()->subHours(23),
            'sla_deadline' => now()->addHours(25),
            'completed_at' => now(),
        ]);

        $this->actingAs($this->manager);
        $ticket->update(['sla_status' => 'Poor']);
        $ticket->refresh();
        $this->assertEquals('Excellent', $ticket->sla_status);
    }

    public function test_sla_remains_days_based(): void
    {
        $policy = SlaPolicy::where('priority', 'high')->first();
        $this->assertEquals(2, $policy->resolution_days);
    }

    public function test_no_sla_remains_no_sla(): void
    {
        $ticket = $this->makeTicket([
            'sla_priority' => null,
            'sla_started_at' => null,
            'sla_deadline' => null,
        ]);
        $this->assertEquals('No SLA', $ticket->sla_status);
    }
}
