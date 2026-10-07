<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TooltipSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    // ─── TOGGLE UI ──────────────────────────────────────

    public function test_toggle_label_is_alpine_reactive_not_static(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/settings/tooltip');
        $response->assertStatus(200);

        // The label must bind to Alpine state (`x-text`), not be server-rendered
        // once, otherwise clicking the switch never updates "Aktif"/"Nonaktif".
        $response->assertSee("x-model=\"on\"", false);
        $response->assertSee("x-text=\"on ? 'Aktif' : 'Nonaktif'\"", false);
        // And the toggle must persist immediately, not only via the bottom button.
        $response->assertSee('requestSubmit()', false);
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

    // ─── SCREENSHOT SLIDES ──────────────────────────────

    public function test_admin_can_upload_screenshot_and_it_is_served(): void
    {
        $this->actingAs($this->admin);

        $this->patch('/settings/tooltip', [
            'enabled' => '1',
            'steps' => [
                [
                    'title' => 'Slide A',
                    'description' => 'With a screenshot',
                    'target' => '',
                    'image' => '',
                    'image_upload' => UploadedFile::fake()->image('slide.png', 400, 300),
                ],
            ],
        ])->assertRedirect('/settings/tooltip');

        $step = TooltipSetting::current()->steps[0];
        $this->assertNotEmpty($step['image']);

        // The screenshot is served on the authenticated tour image route.
        $this->actingAs($this->regularUser);
        $this->get(route('tooltip.image', 0))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_upload_rejects_non_image(): void
    {
        $this->actingAs($this->admin);

        $this->patch('/settings/tooltip', [
            'steps' => [
                ['title' => 'Bad', 'description' => 'x', 'image_upload' => UploadedFile::fake()->create('evil.pdf', 10, 'application/pdf')],
            ],
        ])->assertSessionHasErrors('steps.0.image_upload');
    }

    public function test_image_route_404_when_step_has_no_screenshot(): void
    {
        TooltipSetting::create(['enabled' => true, 'steps' => [
            ['title' => 'No shot', 'description' => 'text only', 'target' => '', 'image' => ''],
        ]]);

        $this->actingAs($this->regularUser);
        $this->get(route('tooltip.image', 0))->assertNotFound();
    }

    public function test_default_slide_served_from_bundled_resources(): void
    {
        // Defaults reference a bundled slide, served even with no storage upload.
        TooltipSetting::create(['enabled' => true, 'steps' => TooltipSetting::defaultSteps()]);

        $this->actingAs($this->regularUser);
        $this->get(route('tooltip.image', 0))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_tour_renders_screenshot_src_when_step_has_image(): void
    {
        TooltipSetting::create([
            'enabled' => true,
            'steps' => [
                ['title' => 'Slide', 'description' => 'desc', 'target' => '', 'image' => 'slide.png'],
            ],
        ]);

        $this->actingAs($this->regularUser);

        $this->get('/dashboard')
            ->assertStatus(200)
            // The step (with its image) reaches the Alpine component and the
            // slide <img> binding is rendered (URL itself is asserted via the
            // serve route in test_admin_can_upload_screenshot_and_it_is_served).
            ->assertSee('slide.png', false)
            ->assertSee('steps[index].img', false);
    }
}