<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiReportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);

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
    }

    // ─── Authorization Tests ─────────────────────────────

    public function test_admin_can_access_kpi_report(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('KPI Report');
    }

    public function test_manager_can_access_kpi_report(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('KPI Report');
    }

    public function test_staff_can_access_kpi_report(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('KPI Report');
    }

    public function test_regular_user_cannot_access_kpi_report(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/reports');
        $response->assertForbidden();
    }

    public function test_staff_can_access_with_direct_url(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get('/reports?staff_id=1&period=this_month');
        $response->assertStatus(200);
    }

    public function test_user_cannot_bypass_with_direct_url(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/reports?staff_id=1&period=this_month');
        $response->assertForbidden();
    }

    // ─── Full Report Tests ──────────────────────────────

    public function test_full_report_works_without_staff_selection(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports?generate_full_report=1&period=this_month');
        $response->assertStatus(200);
        $response->assertSee('All Staff');
    }

    // ─── Individual Staff Report Tests ──────────────────

    public function test_individual_staff_report_works(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get("/reports?staff_id={$this->staff->id}&period=this_month");
        $response->assertStatus(200);
        $response->assertSee($this->staff->name);
    }

    // ─── Staff Filter Tests ─────────────────────────────

    public function test_staff_filter_excludes_normal_users(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/reports');
        $response->assertStatus(200);
        
        // Regular user should NOT appear in staff selector
        $response->assertDontSee("value=\"{$this->regularUser->id}\"");
        
        // Support staff SHOULD appear
        $response->assertSee("value=\"{$this->staff->id}\"");
    }

    // ─── Period Filter Tests ────────────────────────────

    public function test_this_month_filter(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports?period=this_month');
        $response->assertStatus(200);
        $response->assertSee('This Month');
    }

    public function test_last_month_filter(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports?period=last_month');
        $response->assertStatus(200);
        $response->assertSee('Last Month');
    }

    public function test_this_year_filter(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports?period=this_year');
        $response->assertStatus(200);
        $response->assertSee('This Year');
    }

    public function test_custom_date_range(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports?period=custom&from_date=2026-08-01&to_date=2026-08-19');
        $response->assertStatus(200);
        $response->assertSee('01 Aug 2026');
    }

    // ─── Report Type Tests ──────────────────────────────

    public function test_summary_report(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports?generate_full_report=1&report_type=summary');
        $response->assertStatus(200);
    }

    public function test_detailed_report(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get('/reports?generate_full_report=1&report_type=detailed');
        $response->assertStatus(200);
    }
}