<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Sprint37AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Audit Admin',
            'email' => 'audit-admin@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => \App\Models\Role::where('slug', 'admin')->first()->id,
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Audit Staff',
            'email' => 'audit-staff@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => \App\Models\Role::where('slug', 'user')->first()->id,
            'email_verified_at' => now(),
        ]);
    }

    private function makeTicket(array $overrides = []): Ticket
    {
        static $n = 0;
        $n++;

        return Ticket::create(array_merge([
            'ticket_number' => 'ITSUP-TEST-' . str_pad($n, 5, '0', STR_PAD_LEFT),
            'description' => 'Audit test ticket ' . $n,
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
            'user_id' => $this->admin->id,
            'category_id' => Category::first()->id,
        ], $overrides));
    }

    public function test_audit_logs_migration_works(): void
    {
        $this->assertTrue(Schema::hasTable('audit_logs'));

        $log = AuditLog::create([
            'event' => 'system_check',
            'auditable_type' => Ticket::class,
            'auditable_id' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'event' => 'system_check']);
    }

    public function test_audit_log_model_works(): void
    {
        $log = AuditLog::create([
            'event' => 'created',
            'auditable_type' => Ticket::class,
            'auditable_id' => 1,
            'old_values' => ['status' => 'Open'],
            'new_values' => ['status' => 'In Progress'],
        ]);

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertIsArray($log->old_values);
        $this->assertIsArray($log->new_values);
        $this->assertEquals('Open', $log->old_values['status']);
        $this->assertEquals('In Progress', $log->new_values['status']);
    }

    public function test_ticket_creation_creates_audit_entry(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->makeTicket();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
        ]);
    }

    public function test_ticket_status_change_creates_audit_entry(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->makeTicket();
        $ticket->update(['status' => 'In Progress']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'status_changed',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
        ]);
    }

    public function test_ticket_assignment_change_creates_audit_entry(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->makeTicket();
        $ticket->update(['assignee_id' => $this->staff->id]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'assigned',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
        ]);
    }

    public function test_ticket_priority_change_creates_audit_entry(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->makeTicket();
        $ticket->update(['priority' => 'critical']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'priority_changed',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
        ]);
    }

    public function test_user_creation_and_update_creates_audit_entry(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create([
            'name' => 'Audited User',
            'email' => 'audited-user@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => \App\Models\Role::where('slug', 'staff')->first()->id,
            'email_verified_at' => now(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);

        $user->update(['name' => 'Audited User Renamed']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    public function test_category_creation_and_update_creates_audit_entry(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create([
            'name' => 'Audited Category',
            'slug' => 'audited-category',
            'description' => 'Audit test',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => Category::class,
            'auditable_id' => $category->id,
        ]);

        $category->update(['name' => 'Audited Category Renamed']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => Category::class,
            'auditable_id' => $category->id,
        ]);
    }

    public function test_sensitive_user_fields_are_not_stored(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create([
            'name' => 'Secret User',
            'email' => 'secret-user@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => \App\Models\Role::where('slug', 'staff')->first()->id,
            'email_verified_at' => now(),
        ]);

        // Update both name and password; the audit entry must capture the name
        // change but never store the password hash.
        $user->update([
            'name' => 'Secret User Renamed',
            'password' => bcrypt('Different@123'),
        ]);

        $log = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', 'updated')
            ->firstOrFail();

        $this->assertArrayHasKey('name', $log->new_values);
        $this->assertArrayNotHasKey('password', $log->old_values ?? []);
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
    }

    public function test_actor_is_correctly_associated(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->makeTicket();

        $log = AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->where('event', 'created')
            ->firstOrFail();

        $this->assertEquals($this->admin->id, $log->user_id);
    }

    public function test_audit_viewer_accessible_to_admin(): void
    {
        $this->actingAs($this->admin);

        $this->makeTicket();

        $response = $this->get(route('audit.index'));
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_audit_viewer(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get(route('audit.index'));
        $response->assertStatus(403);
    }

    public function test_audit_viewer_returns_existing_entries(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->makeTicket(['ticket_number' => 'ITSUP-VIEW-00001']);

        $response = $this->get(route('audit.index'));
        $response->assertStatus(200);
        $response->assertSee('ITSUP-VIEW-00001');
        $response->assertSee('created');
    }

    public function test_old_and_new_values_contain_expected_changes(): void
    {
        $this->actingAs($this->admin);

        $ticket = $this->makeTicket(); // status = Waiting Confirmation
        $ticket->update(['status' => 'In Progress']);

        $log = AuditLog::where('auditable_type', Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->where('event', 'status_changed')
            ->firstOrFail();

        $this->assertEquals('Waiting Confirmation', $log->old_values['status']);
        $this->assertEquals('In Progress', $log->new_values['status']);
    }

    public function test_deleting_nullable_actor_behavior(): void
    {
        // An audit entry with a null actor (system/cron) must persist cleanly.
        $log = AuditLog::create([
            'user_id' => null,
            'event' => 'system_event',
            'auditable_type' => Ticket::class,
            'auditable_id' => 99,
        ]);

        $this->assertNull($log->fresh()->user_id);
        $this->assertNull($log->user);
    }
}
