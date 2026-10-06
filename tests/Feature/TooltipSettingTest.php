<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TooltipSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TooltipSettingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

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

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => Role::where('slug', 'user')->value('id'),
            'email_verified_at' => now(),
        ]);
    }

    // ─── ACCESS ─────────────────────────────────────────

    public function test_admin_can_view_tooltip_settings(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/settings/tooltip');
        $response->assertStatus(200);
        $response->assertSee('Tooltip Onboarding');
    }

    public function test_manager_cannot_view_tooltip_settings(): void
    {
        $this->actingAs($this->manager);

        $this->get('/settings/tooltip')->assertForbidden();
    }

    public function test_regular_user_cannot_view_tooltip_settings(): void
    {
        $this->actingAs($this->regularUser);

        $this->get('/settings/tooltip')->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/settings/tooltip')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_update_tooltip_settings(): void
    {
        $this->actingAs($this->regularUser);

        $this->patch('/settings/tooltip', ['enabled' => true])->assertForbidden();
    }

    // ─── UPDATE ─────────────────────────────────────────

    public function test_admin_can_toggle_and_save_steps(): void
    {
        $this->actingAs($this->admin);

        $response = $this->patch('/settings/tooltip', [
            'enabled' => '1',
            'steps' => [
                ['title' => 'Step A', 'description' => 'Do A', 'target' => 'create-ticket-link'],
                ['title' => 'Step B', 'description' => 'Do B', 'target' => ''],
            ],
        ]);

        $response->assertRedirect('/settings/tooltip');

        $setting = TooltipSetting::current();
        $this->assertTrue($setting->enabled);
        $this->assertCount(2, $setting->steps);
        $this->assertEquals('Step A', $setting->steps[0]['title']);
    }

    // ─── TOUR RENDER ────────────────────────────────────

    public function test_tour_absent_when_disabled(): void
    {
        TooltipSetting::create(['enabled' => false, 'steps' => TooltipSetting::defaultSteps()]);

        $this->actingAs($this->regularUser);

        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertDontSee('onboardingTour', false);
    }

    public function test_tour_present_when_enabled(): void
    {
        TooltipSetting::create(['enabled' => true, 'steps' => TooltipSetting::defaultSteps()]);

        $this->actingAs($this->regularUser);

        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('onboardingTour', false);
    }

    public function test_admin_sees_tooltip_in_sidebar(): void
    {
        $this->actingAs($this->admin);

        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('/settings/tooltip', false);
    }

    public function test_regular_user_does_not_see_tooltip_in_sidebar(): void
    {
        $this->actingAs($this->regularUser);

        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertDontSee('/settings/tooltip', false);
    }

    // ─── DATA-TOUR TARGETS ──────────────────────────────

    public function test_create_ticket_form_exposes_tour_targets(): void
    {
        $this->actingAs($this->regularUser);

        $this->get('/my-tickets/create')
            ->assertStatus(200)
            ->assertSee('data-tour="ticket-category"', false)
            ->assertSee('data-tour="ticket-description"', false)
            ->assertSee('data-tour="ticket-submit"', false);
    }
}