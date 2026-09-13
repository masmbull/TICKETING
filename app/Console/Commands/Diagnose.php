<?php

namespace App\Console\Commands;

use App\Services\MicrosoftGraphMailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Read-only environment diagnostic.
 *
 * Verifies platform, configuration, database, storage, queue, and the
 * Microsoft Graph OAuth/API integrations. It never sends email, never mutates
 * data, and never prints secrets, tokens, client secrets or passwords — only
 * whether a value is configured and whether a connectivity probe succeeded.
 */
class Diagnose extends Command
{
    protected $signature = 'mito:diagnose
                            {--skip-external : Skip Graph OAuth/API connectivity probes (offline mode)}';

    protected $description = 'Diagnose Laravel, config, database, storage, queue and Microsoft Graph connectivity';

    private int $failures = 0;

    public function handle(MicrosoftGraphMailService $graph): int
    {
        $this->info('MITO Ticketing — environment diagnosis');
        $this->newLine();

        $this->platform();
        $this->configuration();
        $this->database();
        $this->storage();
        $this->queue();
        $this->graph($graph);
        $this->integrations();

        $this->newLine();
        if ($this->failures === 0) {
            $this->info('Diagnosis complete: all checks passed.');

            return self::SUCCESS;
        }

        $this->error("Diagnosis complete: {$this->failures} check(s) need attention.");

        return self::FAILURE;
    }

    private function platform(): void
    {
        $this->line('<comment>Platform</comment>');
        $this->ok('PHP', PHP_VERSION.' ('.PHP_SAPI.')');
        $this->ok('Laravel', app()->version());
        $this->ok('Environment', (string) app()->environment());

        $loaded = array_map('strtolower', get_loaded_extensions());
        $missing = array_diff(['pdo', 'mbstring', 'openssl', 'json'], $loaded);
        $missing ? $this->bad('Extensions', 'missing: '.implode(', ', $missing))
                 : $this->ok('Extensions', 'pdo, mbstring, openssl, json present');
    }

    private function configuration(): void
    {
        $this->newLine();
        $this->line('<comment>Application configuration</comment>');

        $this->ok('App name', (string) config('app.name'));
        $this->ok('URL', (string) config('app.url'));
        $this->ok('Debug mode', config('app.debug') ? 'enabled (local only)' : 'disabled');

        empty(config('app.key'))
            ? $this->bad('APP_KEY', 'not set — run "php artisan key:generate"')
            : $this->ok('APP_KEY', 'configured');

        $this->ok('Timezone', (string) config('app.timezone'));
        $this->ok('Log channel', (string) config('logging.default'));
    }

    private function database(): void
    {
        $this->newLine();
        $this->line('<comment>Database</comment>');

        $connection = (string) config('database.default');
        $driver = (string) config("database.connections.{$connection}.driver");

        // Host/database names are not secrets; credentials never printed.
        $this->ok('Connection', $connection.' ('.$driver.')');
        $this->ok('Database', $this->safeDatabaseName($connection));

        try {
            DB::connection()->getPdo()->query('SELECT 1');
            $this->ok('Connectivity', 'SELECT 1 succeeded');
        } catch (Throwable $e) {
            $this->bad('Connectivity', $e->getMessage());

            return;
        }

        try {
            $applied = DB::table('migrations')->count();
            $files = count(glob(database_path('migrations/*.php')));
            $pending = max(0, $files - $applied);
            $this->ok('Migrations', $applied.' applied'.($pending > 0 ? ', '.$pending.' file(s) newer' : ''));
        } catch (Throwable $e) {
            $this->bad('Migrations', 'migrations table unreadable: '.$e->getMessage());
        }
    }

    private function storage(): void
    {
        $this->newLine();
        $this->line('<comment>Storage</comment>');

        $targets = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($targets as $label => $path) {
            if (! is_dir($path)) {
                $this->bad($label, 'directory missing');
            } elseif (is_writable($path)) {
                $this->ok($label, 'writable');
            } else {
                $this->bad($label, 'not writable');
            }
        }

        $this->ok('Filesystem disk', (string) config('filesystems.default'));
    }

    private function queue(): void
    {
        $this->newLine();
        $this->line('<comment>Queue</comment>');

        $connection = (string) config('queue.default');
        $this->ok('Driver', $connection);

        if ($connection === 'sync') {
            $this->ok('Mode', 'synchronous — email is sent inline during the request');
        } elseif ($connection === 'database') {
            try {
                $pending = DB::table('jobs')->count();
                $failed = DB::table('failed_jobs')->count();
                $this->ok('Jobs', $pending.' pending, '.$failed.' failed');
            } catch (Throwable $e) {
                $this->bad('Jobs', 'queue tables unreadable: '.$e->getMessage());
            }
        }

        $this->ok('Notification channel', 'Microsoft Graph (GraphMailChannel)');
    }

    private function graph(MicrosoftGraphMailService $graph): void
    {
        $this->newLine();
        $this->line('<comment>Microsoft Graph</comment>');

        $config = (array) config('services.microsoft-graph');

        foreach (['tenant_id', 'client_id', 'client_secret'] as $key) {
            empty($config[$key])
                ? $this->bad('Config '.$key, 'not set (value is never printed)')
                : $this->ok('Config '.$key, 'configured');
        }

        $from = (string) ($config['mail_from'] ?? '');
        filter_var($from, FILTER_VALIDATE_EMAIL)
            ? $this->ok('Mail from', $from)
            : $this->bad('Mail from', 'not a valid email address');

        if ($this->option('skip-external')) {
            $this->line('  <fg=gray>- external probes skipped (--skip-external)</>');

            return;
        }

        if (empty($config['tenant_id']) || empty($config['client_id']) || empty($config['client_secret'])) {
            $this->bad('OAuth token', 'skipped: configuration incomplete');

            return;
        }

        try {
            $token = $graph->getAccessToken();
            $this->ok('OAuth token', 'acquired (length '.strlen($token).'; value never printed)');
        } catch (Throwable $e) {
            $this->bad('OAuth token', $e->getMessage());

            return;
        }

        try {
            // Cheap read-only probe. Never sends mail, never mutates data.
            $options = empty($config['ca_bundle']) ? [] : ['curl' => [CURLOPT_CAINFO => $config['ca_bundle']]];

            $response = Http::withToken($token)
                ->withOptions($options)
                ->timeout(10)
                ->get('https://graph.microsoft.com/v1.0/users/'.$from, ['$select' => 'id,mail']);

            $response->successful()
                ? $this->ok('Graph API', 'reachable (HTTP '.$response->status().'), mailbox resolvable')
                : $this->bad('Graph API', 'HTTP '.$response->status().': '.($response->json('error.message') ?? 'no details'));
        } catch (Throwable $e) {
            $this->bad('Graph API', $e->getMessage());
        }
    }

    private function integrations(): void
    {
        $this->newLine();
        $this->line('<comment>Other integrations</comment>');

        $this->ok('Mail driver', (string) config('mail.default'));
        $this->ok('Cache store', (string) config('cache.default'));
        $this->ok('Session driver', (string) config('session.driver'));
        $this->ok('Broadcast driver', (string) config('broadcasting.default'));
    }

    /** Host/database names only; usernames and passwords are never printed. */
    private function safeDatabaseName(string $connection): string
    {
        if (config("database.connections.{$connection}.driver") === 'sqlite') {
            return (string) config("database.connections.{$connection}.database");
        }

        return sprintf(
            '%s@%s:%s',
            (string) config("database.connections.{$connection}.database"),
            (string) config("database.connections.{$connection}.host"),
            (string) config("database.connections.{$connection}.port")
        );
    }

    private function ok(string $label, string $detail): void
    {
        $this->line(sprintf('  <fg=green>[OK]</> %-22s %s', $label, $detail));
    }

    private function bad(string $label, string $detail): void
    {
        $this->failures++;
        $this->line(sprintf('  <fg=red>[!!]</> %-22s %s', $label, $detail));
    }
}
