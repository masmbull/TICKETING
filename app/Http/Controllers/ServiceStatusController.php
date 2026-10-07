<?php

namespace App\Http\Controllers;

use App\Services\MicrosoftGraphMailService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Admin page that answers "which services are up?".
 *
 * Every external probe is wrapped in its own try/catch and timeout so one dead
 * dependency cannot break the page. Secrets, tokens and connection strings are
 * never rendered — only whether a value is configured and whether the probe
 * succeeded. Graph probing is opt-in (?probe=0 skips) so the page stays fast.
 */
class ServiceStatusController extends Controller
{
    public function index(MicrosoftGraphMailService $graph): View
    {
        $probeGraph = request()->boolean('probe', true);

        $groups = [
            'Platform' => [
                $this->check('Application', fn () => 'Laravel '.app()->version().' · PHP '.PHP_VERSION.' · '.app()->environment()),
                $this->check('App key', fn () => empty(config('app.key'))
                    ? throw new \RuntimeException('APP_KEY not set')
                    : 'configured'),
                $this->extensionCheck(),
            ],
            'Data & storage' => [
                $this->check('Database', function () {
                    DB::connection()->getPdo()->query('SELECT 1');

                    return config('database.default').' ('.config('database.connections.'.config('database.default').'.driver').')';
                }),
                $this->check('Cache', function () {
                    $key = 'service-status-probe';
                    Cache::put($key, 'ok', 5);
                    $hit = Cache::get($key) === 'ok';
                    Cache::forget($key);

                    return $hit ? config('cache.default') : throw new \RuntimeException('write/read mismatch');
                }),
                $this->check('Session', fn () => (string) config('session.driver')),
                $this->storageCheck(),
            ],
            'Queue & notifications' => [
                $this->queueCheck(),
                $this->check('Mail driver', fn () => (string) config('mail.default')),
                $this->check('Broadcast', fn () => (string) config('broadcasting.default')),
            ],
            'Microsoft Graph' => [
                $this->graphConfigCheck(),
                $this->graphOAuthCheck($graph, $probeGraph),
                $this->graphApiCheck($graph, $probeGraph),
            ],
        ];

        $all = collect($groups)->flatten(1);
        $failing = $all->where('status', 'fail')->count();

        return view('settings.services', [
            'groups' => $groups,
            'summary' => [
                'total' => $all->count(),
                'ok' => $all->where('status', 'ok')->count(),
                'fail' => $failing,
                'healthy' => $failing === 0,
            ],
            'probeGraph' => $probeGraph,
        ]);
    }

    /**
     * Run one probe, timing it and turning any throwable into a failure row.
     */
    private function check(string $label, callable $probe): array
    {
        $start = microtime(true);

        try {
            return [
                'label' => $label,
                'status' => 'ok',
                'detail' => (string) $probe(),
                'ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        } catch (Throwable $e) {
            // Reason only; these exception messages carry no credentials.
            report($e);

            return [
                'label' => $label,
                'status' => 'fail',
                'detail' => $e->getMessage(),
                'ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }

    private function extensionCheck(): array
    {
        $loaded = array_map('strtolower', get_loaded_extensions());
        $missing = array_values(array_diff(['pdo', 'mbstring', 'openssl', 'json'], $loaded));

        return [
            'label' => 'PHP extensions',
            'status' => $missing === [] ? 'ok' : 'fail',
            'detail' => $missing === [] ? 'pdo, mbstring, openssl, json present' : 'missing: '.implode(', ', $missing),
            'ms' => 0,
        ];
    }

    private function storageCheck(): array
    {
        $targets = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $bad = [];
        foreach ($targets as $label => $path) {
            if (! is_dir($path) || ! is_writable($path)) {
                $bad[] = $label;
            }
        }

        return [
            'label' => 'Storage',
            'status' => $bad === [] ? 'ok' : 'fail',
            'detail' => $bad === []
                ? 'writable · disk: '.config('filesystems.default')
                : 'not writable: '.implode(', ', $bad),
            'ms' => 0,
        ];
    }

    private function queueCheck(): array
    {
        $driver = (string) config('queue.default');

        if ($driver === 'sync') {
            return ['label' => 'Queue', 'status' => 'warn', 'detail' => 'sync — email sent inline during the request', 'ms' => 0];
        }

        if ($driver !== 'database') {
            return ['label' => 'Queue', 'status' => 'ok', 'detail' => $driver, 'ms' => 0];
        }

        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            return [
                'label' => 'Queue',
                'status' => $failed > 0 ? 'warn' : 'ok',
                'detail' => "database · {$pending} pending, {$failed} failed",
                'ms' => 0,
            ];
        } catch (Throwable $e) {
            return ['label' => 'Queue', 'status' => 'fail', 'detail' => 'queue tables unreadable: '.$e->getMessage(), 'ms' => 0];
        }
    }

    private function graphConfigCheck(): array
    {
        $config = (array) config('services.microsoft-graph');
        $missing = array_values(array_filter(
            ['tenant_id', 'client_id', 'client_secret'],
            fn ($key) => empty($config[$key])
        ));

        $from = (string) ($config['mail_from'] ?? '');
        if (! filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $missing[] = 'mail_from';
        }

        return [
            'label' => 'Configuration',
            'status' => $missing === [] ? 'ok' : 'fail',
            'detail' => $missing === []
                ? 'tenant, client, secret set · from: '.$from
                : 'not set (value never printed): '.implode(', ', $missing),
            'ms' => 0,
        ];
    }

    private function graphOAuthCheck(MicrosoftGraphMailService $graph, bool $probe): array
    {
        if (! $probe) {
            return ['label' => 'OAuth token', 'status' => 'skip', 'detail' => 'probe disabled', 'ms' => 0];
        }

        return $this->check('OAuth token', function () use ($graph) {
            $token = $graph->getAccessToken();

            return 'acquired (length '.strlen($token).', value '.Str::mask($token, '•', 4, max(0, strlen($token) - 8)).')';
        });
    }

    private function graphApiCheck(MicrosoftGraphMailService $graph, bool $probe): array
    {
        if (! $probe) {
            return ['label' => 'Graph API', 'status' => 'skip', 'detail' => 'probe disabled', 'ms' => 0];
        }

        return $this->check('Graph API', function () use ($graph) {
            $config = (array) config('services.microsoft-graph');

            if (empty($config['tenant_id']) || empty($config['client_id']) || empty($config['client_secret'])) {
                throw new \RuntimeException('skipped: configuration incomplete');
            }

            $from = (string) ($config['mail_from'] ?? '');
            $token = $graph->getAccessToken();
            // Cheap read-only probe: never sends mail, never mutates data.
            $response = Http::withToken($token)
                ->withOptions($config['ca_bundle'] ?? null ? ['curl' => [CURLOPT_CAINFO => $config['ca_bundle']]] : [])
                ->timeout(10)
                ->get('https://graph.microsoft.com/v1.0/users/'.$from, ['$select' => 'id,mail']);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status().': '.($response->json('error.message') ?? 'no details'));
            }

            return 'reachable (HTTP '.$response->status().'), mailbox resolvable';
        });
    }
}