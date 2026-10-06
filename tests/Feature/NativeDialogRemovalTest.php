<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\SlaMapping;
use App\Models\SlaPolicy;
use App\Models\SecurityInsight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * All popups must be modal (Alpine dialog or SweetAlert2 via window.MITO),
 * never native browser confirm()/alert()/prompt().
 */
class NativeDialogRemovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\SlaPolicySeeder::class);
        $this->seed(\Database\Seeders\SecurityInsightSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => Role::where('slug', 'admin')->value('id'),
            'email_verified_at' => now(),
        ]);
    }

    public function test_sla_mapping_delete_uses_mito_confirm_not_native_confirm(): void
    {
        $policy = SlaPolicy::first();
        $category = Category::first();

        SlaMapping::create([
            'category_id' => $category->id,
            'priority' => $policy->priority,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);

        $content = $this->get('/settings/sla-policies')->assertStatus(200)->getContent();

        $this->assertStringContainsString('MITO.confirm(', $content, 'Delete must route through the MITO.confirm modal');
        $this->assertStringNotContainsString("if(confirm('", $content, 'Native confirm() must be gone');
    }

    public function test_security_insight_delete_uses_mito_confirm_not_native_confirm(): void
    {
        SecurityInsight::create([
            'subject' => 'Test insight for dialog check',
            'description' => 'Ensures delete routes through the modal.',
            'insight_type' => 'network',
            'severity' => 'high',
            'scan_source' => 'UnitTest',
            'scan_performed_on' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->admin);

        $content = $this->get('/security-insights')->assertStatus(200)->getContent();

        $this->assertStringContainsString('MITO.confirm(', $content, 'Delete must route through the MITO.confirm modal');
        $this->assertStringNotContainsString("return confirm('", $content, 'Native confirm() must be gone');
    }

    public function test_ticket_inline_updates_use_sweetalert_not_native_alert(): void
    {
        $this->actingAs($this->admin);

        $content = $this->get('/tickets')->assertStatus(200)->getContent();

        // Admin sees the inline assignee/priority/status update script
        $this->assertStringContainsString('MITO.alertError(', $content, 'Errors must use the SweetAlert2 helper');
        $this->assertStringNotContainsString('alert(', $content, 'Native alert() must be gone');
    }

    public function test_layout_ships_the_sweetalert_mito_helpers(): void
    {
        $this->actingAs($this->admin);

        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('build/assets/app-', false)
            ->assertSee('MITO', false);
    }
}