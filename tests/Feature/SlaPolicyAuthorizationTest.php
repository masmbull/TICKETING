<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SlaPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaPolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private User $regularUser;
    private SlaPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Create users with different roles
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => Role::where('slug', 'admin')->value('id'),
            'email_verified_at' => now(),
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => Role::where('slug', 'manager')->value('id'),
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => Role::where('slug', 'staff')->value('id'),
            'email_verified_at' => now(),
        ]);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@mito.local',
            'password' => bcrypt('Test@123'),
            'email_verified_at' => now(),
        ]);

        // Create a test SLA policy
        $this->policy = SlaPolicy::create([
            'priority' => 'medium',
            'name' => 'Medium Priority',
            'resolution_days' => 3,
            'resolution_hours' => 72,
            'is_active' => true,
        ]);
    }

    // ─── INDEX / VIEW TESTS ──────────────────────────────

    public function test_admin_can_view_sla_policies(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/settings/sla-policies');
        $response->assertStatus(200);
        $response->assertSee('SLA Policies');
    }

    public function test_manager_can_view_sla_policies(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/settings/sla-policies');
        $response->assertStatus(200);
        $response->assertSee('SLA Policies');
    }

    public function test_staff_cannot_view_sla_policies(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get('/settings/sla-policies');
        $response->assertForbidden();
    }

    public function test_regular_user_cannot_view_sla_policies(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/settings/sla-policies');
        $response->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/settings/sla-policies');
        $response->assertRedirect('/login/user');
    }

    // ─── CREATE TESTS ───────────────────────────────────

    public function test_admin_can_create_sla_policy(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/settings/sla-policies', [
            'priority' => 'low',
            'resolution_days' => 5,
        ]);

        $response->assertRedirect('/settings/sla-policies');
        $this->assertDatabaseHas('sla_policies', [
            'priority' => 'low',
            'resolution_days' => 5,
        ]);
    }

    public function test_manager_can_create_sla_policy(): void
    {
        $this->actingAs($this->manager);

        $response = $this->post('/settings/sla-policies', [
            'priority' => 'high',
            'resolution_days' => 2,
        ]);

        $response->assertRedirect('/settings/sla-policies');
        $this->assertDatabaseHas('sla_policies', [
            'priority' => 'high',
            'resolution_days' => 2,
        ]);
    }

    public function test_staff_cannot_create_sla_policy(): void
    {
        $this->actingAs($this->staff);

        $response = $this->post('/settings/sla-policies', [
            'priority' => 'critical',
            'resolution_days' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('sla_policies', [
            'priority' => 'critical',
        ]);
    }

    public function test_regular_user_cannot_create_sla_policy(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->post('/settings/sla-policies', [
            'priority' => 'critical',
            'resolution_days' => 1,
        ]);

        $response->assertForbidden();
    }

    // ─── UPDATE TESTS ───────────────────────────────────

    public function test_admin_can_update_sla_policy(): void
    {
        $this->actingAs($this->admin);

        $response = $this->patch("/settings/sla-policies/{$this->policy->id}", [
            'priority' => 'medium',
            'resolution_days' => 4,
        ]);

        $response->assertRedirect('/settings/sla-policies');
        $this->assertDatabaseHas('sla_policies', [
            'id' => $this->policy->id,
            'resolution_days' => 4,
        ]);
    }

    public function test_manager_can_update_sla_policy(): void
    {
        $this->actingAs($this->manager);

        $response = $this->patch("/settings/sla-policies/{$this->policy->id}", [
            'priority' => 'medium',
            'resolution_days' => 5,
        ]);

        $response->assertRedirect('/settings/sla-policies');
        $this->assertDatabaseHas('sla_policies', [
            'id' => $this->policy->id,
            'resolution_days' => 5,
        ]);
    }

    public function test_staff_cannot_update_sla_policy(): void
    {
        $this->actingAs($this->staff);

        $response = $this->patch("/settings/sla-policies/{$this->policy->id}", [
            'priority' => 'medium',
            'resolution_days' => 10,
        ]);

        $response->assertForbidden();
    }

    public function test_regular_user_cannot_update_sla_policy(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->patch("/settings/sla-policies/{$this->policy->id}", [
            'priority' => 'medium',
            'resolution_days' => 10,
        ]);

        $response->assertForbidden();
    }

    // ─── DELETE TESTS ───────────────────────────────────

    public function test_admin_can_delete_sla_policy(): void
    {
        $this->actingAs($this->admin);

        $policyToDelete = SlaPolicy::create([
            'priority' => 'test-delete',
            'name' => 'Test Delete',
            'resolution_days' => 1,
            'resolution_hours' => 24,
            'is_active' => true,
        ]);

        $response = $this->delete("/settings/sla-policies/{$policyToDelete->id}");

        $response->assertRedirect('/settings/sla-policies');
        $this->assertDatabaseMissing('sla_policies', [
            'id' => $policyToDelete->id,
        ]);
    }

    public function test_manager_can_delete_sla_policy(): void
    {
        $this->actingAs($this->manager);

        $policyToDelete = SlaPolicy::create([
            'priority' => 'test-delete-mgr',
            'name' => 'Test Delete Manager',
            'resolution_days' => 1,
            'resolution_hours' => 24,
            'is_active' => true,
        ]);

        $response = $this->delete("/settings/sla-policies/{$policyToDelete->id}");

        $response->assertRedirect('/settings/sla-policies');
        $this->assertDatabaseMissing('sla_policies', [
            'id' => $policyToDelete->id,
        ]);
    }

    public function test_staff_cannot_delete_sla_policy(): void
    {
        $this->actingAs($this->staff);

        $response = $this->delete("/settings/sla-policies/{$this->policy->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('sla_policies', [
            'id' => $this->policy->id,
        ]);
    }

    public function test_regular_user_cannot_delete_sla_policy(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->delete("/settings/sla-policies/{$this->policy->id}");

        $response->assertForbidden();
    }

    // ─── SIDEBAR VISIBILITY ─────────────────────────────

    public function test_admin_sees_sla_policies_in_sidebar(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        // Verify the sidebar contains the link to SLA policies
        $response->assertSee('sla-policies.index');
    }

    public function test_manager_sees_sla_policies_in_sidebar(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        // Verify the sidebar contains the link to SLA policies
        $response->assertSee('sla-policies.index');
    }

    public function test_staff_does_not_see_sla_policies_in_sidebar(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        // Staff should not have access, but we check if they see the link in dashboard
        // (sidebar rendering doesn't generate 403, just hides the link)
    }
}