<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * GET /health is a lightweight, unauthenticated readiness probe:
 * 200 when the app and database are reachable, 503 when a critical
 * dependency is down. It must never call external APIs.
 */
class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_returns_200_with_application_and_database_checks(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
            'checks' => ['application' => 'ok', 'database' => 'ok'],
        ]);
    }

    public function test_health_is_accessible_without_authentication(): void
    {
        // No actingAs(): guests and monitoring probes must be served.
        $this->get('/health')->assertStatus(200);
    }

    public function test_health_returns_503_when_database_is_unavailable(): void
    {
        $original = config('database.default');

        // Point the default connection at an unusable driver to simulate outage.
        config(['database.default' => 'nonexistent-connection']);

        try {
            $response = $this->get('/health');

            $response->assertStatus(503);
            $response->assertJson([
                'status' => 'unavailable',
                'checks' => ['application' => 'ok', 'database' => 'fail'],
            ]);
        } finally {
            // Must be restored explicitly: a leaked default connection silently
            // breaks every subsequent test in the same process.
            config(['database.default' => $original]);
            DB::purge('nonexistent-connection');
        }
    }
}
