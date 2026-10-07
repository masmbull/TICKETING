<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ServiceStatusPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('slug', 'admin')->value('id'),
        ]);

        $this->manager = User::factory()->create([
            'role_id' => Role::where('slug', 'manager')->value('id'),
        ]);
    }

    /** Only admins may open the page. */
    public function test_access_is_admin_only(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.services', ['probe' => 0]))
            ->assertOk()
            ->assertSee('Service Status');

        $this->actingAs($this->manager)
            ->get(route('settings.services'))
            ->assertForbidden();
    }

    /** Page renders without hitting the network when probing is disabled. */
    public function test_fast_load_skips_graph_probes(): void
    {
        Http::fake();

        $this->actingAs($this->admin)
            ->get(route('settings.services', ['probe' => 0]))
            ->assertOk()
            ->assertSee('probe disabled', false);

        Http::assertNothingSent();
    }

    /** With a healthy faked Graph, both Graph probes report online. */
    public function test_graph_probes_report_online_when_reachable(): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'fake-token-value'], 200),
            'graph.microsoft.com/*' => Http::response(['id' => '1', 'mail' => 'itsupport@mito.co.id'], 200),
        ]);

        config([
            'services.microsoft-graph.tenant_id' => 'tenant',
            'services.microsoft-graph.client_id' => 'client',
            'services.microsoft-graph.client_secret' => 'secret',
            'services.microsoft-graph.mail_from' => 'itsupport@mito.co.id',
        ]);

        $response = $this->actingAs($this->admin)->get(route('settings.services'));

        $response->assertOk()->assertSee('mailbox resolvable');
        // The raw token must never reach the page.
        $response->assertDontSee('fake-token-value');
    }

    /** A dead Graph endpoint degrades to an offline row, not a 500. */
    public function test_graph_failure_is_reported_not_thrown(): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'fake-token-value'], 200),
            'graph.microsoft.com/*' => Http::response(['error' => ['message' => 'boom']], 500),
        ]);

        config([
            'services.microsoft-graph.tenant_id' => 'tenant',
            'services.microsoft-graph.client_id' => 'client',
            'services.microsoft-graph.client_secret' => 'secret',
            'services.microsoft-graph.mail_from' => 'itsupport@mito.co.id',
        ]);

        $this->actingAs($this->admin)
            ->get(route('settings.services'))
            ->assertOk()
            ->assertSee('HTTP 500');
    }
}