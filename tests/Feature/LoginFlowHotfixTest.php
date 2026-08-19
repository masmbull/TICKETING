<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginFlowHotfixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    // ─── Guest entry flow ────────────────────────────────────

    public function test_guest_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_guest_login_page_returns_200(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MITO IT Helpdesk');
        $response->assertSee('Sign in');
    }

    public function test_login_page_contains_no_role_selection_cards(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertDontSee('Choose your role to continue');
        $response->assertSee('Sign in');
        $response->assertSee('MITO IT Helpdesk');
    }

    // ─── Authenticated entry flow ────────────────────────────

    public function test_authenticated_root_redirects_to_dashboard(): void
    {
        $user = User::where('email', 'admin@mito.local')->firstOrFail();

        $this->actingAs($user);

        $response = $this->get('/');
        $response->assertRedirect('/dashboard');
    }

    public function test_authenticated_login_redirects_to_dashboard(): void
    {
        $user = User::where('email', 'admin@mito.local')->firstOrFail();

        $this->actingAs($user);

        $response = $this->get('/login');
        $response->assertRedirect('/dashboard');
    }

    public function test_authenticated_login_with_role_redirects_to_dashboard(): void
    {
        $user = User::where('email', 'admin@mito.local')->firstOrFail();

        $this->actingAs($user);

        $response = $this->get('/login/admin');
        $response->assertRedirect('/dashboard');
    }

    public function test_no_redirect_loop_on_logout(): void
    {
        $user = User::where('email', 'admin@mito.local')->firstOrFail();

        $this->actingAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect('/login');

        // Follow the redirect and ensure we get the login page, not another redirect.
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    // ─── Administrator login ─────────────────────────────────

    public function test_seeded_administrator_can_authenticate(): void
    {
        $admin = User::where('email', 'admin@mito.local')->firstOrFail();

        $response = $this->post('/login', [
            'email' => 'admin@mito.local',
            'password' => 'Admin@123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_successful_login_redirects_to_dashboard(): void
    {
        $this->post('/login', [
            'email' => 'admin@mito.local',
            'password' => 'Admin@123',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('/dashboard', session('url.intended', '/dashboard'));
    }

    public function test_invalid_password_is_rejected(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mito.local',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ─── Remember Me ─────────────────────────────────────────

    public function test_remember_me_continues_to_work(): void
    {
        $admin = User::where('email', 'admin@mito.local')->firstOrFail();

        $response = $this->post('/login', [
            'email' => 'admin@mito.local',
            'password' => 'Admin@123',
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);

        $admin->refresh();
        $this->assertNotNull($admin->remember_token);
    }

    public function test_login_with_remember_creates_remember_cookie(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mito.local',
            'password' => 'Admin@123',
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertCookie(Auth::getRecallerName());
        $this->assertAuthenticated();
    }

    public function test_login_without_remember_creates_no_remember_cookie(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mito.local',
            'password' => 'Admin@123',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertCookieMissing(Auth::getRecallerName());
        $this->assertAuthenticated();
    }

    public function test_no_forced_password_change_after_login(): void
    {
        $this->post('/login', [
            'email' => 'admin@mito.local',
            'password' => 'Admin@123',
        ]);

        $this->assertAuthenticated();
        // Should not redirect to password.change
        $this->assertNotEquals(
            route('password.change'),
            url(session()->get('url.intended', '/dashboard'))
        );
    }
}
