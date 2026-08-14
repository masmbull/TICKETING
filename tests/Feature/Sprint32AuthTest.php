<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint32AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'test@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => Role::where('slug', 'admin')->value('id'),
            'remember_token' => null,
        ]);
    }

    // ─── 1. Login page redesign ───────────────────────────────

    public function test_login_page_renders_centered_card_design(): void
    {
        $response = $this->get('/login/admin');
        $response->assertStatus(200);

        // New enterprise layout: title, subtitle, fields, button.
        $response->assertSee('MITO IT Helpdesk');
        $response->assertSee('Internal Ticketing System');
        $response->assertSee('Remember me');
        $response->assertSee('Forgot password?');
        $response->assertSee('Sign in');
        $response->assertSee('name="remember"', false);

        // Old split left/right illustration layout is gone.
        $response->assertDontSee('Streamline your');
        $response->assertDontSee('IT support workflow');
        $response->assertDontSee('lg:w-1/2 bg-gradient-to-br');
    }

    public function test_login_page_includes_hidden_role_field(): void
    {
        $response = $this->get('/login/admin');

        $response->assertSee('<input type="hidden" name="role" value="admin">', false);
    }

    public function test_login_page_has_no_cache_meta(): void
    {
        $response = $this->get('/login/admin');

        $response->assertSee('no-cache, no-store, must-revalidate', false);
    }

    // ─── 2. Remember Me ───────────────────────────────────────

    public function test_remember_me_persists_remember_token_and_sets_cookie(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@mito.local',
            'password' => 'Test@123',
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);

        // remember_token must be populated on the user.
        $this->user->refresh();
        $this->assertNotNull($this->user->remember_token);

        // A "remember_web_*" cookie must be set.
        $cookies = collect($response->headers->getCookies());
        $this->assertTrue(
            $cookies->contains(fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_')),
            'Remember-me cookie was not set.'
        );
    }

    public function test_without_remember_me_no_remember_token_is_issued(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@mito.local',
            'password' => 'Test@123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);

        $this->user->refresh();
        $this->assertNull($this->user->remember_token);
    }

    // ─── 3. Login security ────────────────────────────────────

    public function test_authenticated_user_redirected_from_login_page(): void
    {
        $this->actingAs($this->user);

        $this->get('/login/admin')->assertRedirect('/dashboard');
        $this->get('/login')->assertRedirect('/dashboard');
    }

    public function test_authenticated_user_redirected_from_login_post(): void
    {
        $this->actingAs($this->user);

        $this->post('/login', [
            'email' => 'test@mito.local',
            'password' => 'Test@123',
        ])->assertRedirect('/dashboard');
    }

    public function test_authenticated_pages_send_no_store_headers(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Cache-Control is normalized by Symfony (ordering + "private"), but the
        // no-store/no-cache directives must be present so the browser cannot
        // cache authenticated pages for Back/Forward navigation.
        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=0', $response->headers->get('Cache-Control'));
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_logout_invalidates_session(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_page_itself_is_not_cached(): void
    {
        $response = $this->get('/login/admin');

        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
    }

    // ─── 4. Loading screen ────────────────────────────────────

    public function test_dashboard_loading_screen_is_wordmark_progress(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // New loading screen: MITO wordmark + thin progress bar. No spinner text.
        $response->assertSee('MITO');
        $response->assertDontSee('Loading your workspace');
        // The loading bar overlay uses the MITO red brand color.
        $response->assertSee('#E30613', false);
    }

    // ─── 5. Navbar: no debug output ───────────────────────────

    public function test_navbar_has_no_debug_output_or_raw_alpine(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $html = $response->getContent();

        // No JS console debug output on the page.
        $this->assertStringNotContainsString('console.log', $html);
        $this->assertStringNotContainsString('console.debug', $html);
        $this->assertStringNotContainsString('debugger;', $html);

        // No raw Blade braces / Alpine variables leaked into text.
        $this->assertStringNotContainsString('{{', $html);
        $this->assertStringNotContainsString('}}', $html);
    }

    // ─── 6. Dashboard polish ──────────────────────────────────

    public function test_dashboard_stat_cards_align_number_with_icon(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $html = $response->getContent();

        // Stat cards use a horizontal icon + number layout (vertically centered).
        $this->assertStringContainsString('stat-card flex items-center gap-3', $html);
        $this->assertStringContainsString('leading-tight tabular-nums', $html);
    }
}
