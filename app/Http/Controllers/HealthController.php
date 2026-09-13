<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Lightweight, unauthenticated liveness/readiness probe.
 *
 * Deliberately cheap: it only verifies that the application booted and that
 * the database answers a trivial query. No external API (Graph, identity
 * platform, mail) is contacted so the endpoint stays fast and safe to poll.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'application' => 'ok',
            'database' => $this->databaseStatus(),
        ];

        $healthy = ! in_array('fail', $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'unavailable',
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function databaseStatus(): string
    {
        try {
            // Driver-agnostic connectivity probe; never touches user tables.
            DB::connection()->getPdo()->query('SELECT 1');

            return 'ok';
        } catch (Throwable $e) {
            // Reason only; connection strings/credentials must never leak.
            report($e);

            return 'fail';
        }
    }
}
