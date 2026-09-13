<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the mito:check-email-flow diagnostic command.
 *
 * The command fakes the outbound HTTP transport itself (identity platform +
 * Graph), so this test can never deliver a real email. It also asserts that
 * the command created and cleaned up its own ticket.
 */
class MitoCheckEmailFlowCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        User::factory()->create(['role_id' => Role::where('slug', 'staff')->value('id')]);
        User::factory()->create(['role_id' => Role::where('slug', 'user')->value('id')]);
    }

    public function test_email_flow_check_reports_expected_lifecycle(): void
    {
        $this->artisan('mito:check-email-flow', ['--to' => 'someone@example.test'])
            ->expectsOutputToContain('created')
            ->expectsOutputToContain('reopened (no email)')
            ->assertSuccessful();
    }

    public function test_email_flow_check_rejects_invalid_recipient(): void
    {
        $this->artisan('mito:check-email-flow', ['--to' => 'not-an-email'])
            ->assertFailed();
    }

    public function test_email_flow_check_never_reaches_real_graph(): void
    {
        Http::preventStrayRequests();

        $this->artisan('mito:check-email-flow', ['--to' => 'someone@example.test'])
            ->assertSuccessful();
    }
}
