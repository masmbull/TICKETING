<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role?->slug ?? 'user';

        // Common metadata
        $metadata = [
            'version' => config('app.version', '1.0.0'),
            'build' => '2026.08.07',
            'environment' => config('app.env', 'production'),
            'git_commit' => $this->getGitCommit(),
            'current_day' => Carbon::now('Asia/Jakarta')->format('l'),
            'current_date' => Carbon::now('Asia/Jakarta')->format('d F Y'),
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
        ];

        // System health
        $systemHealth = $this->getSystemHealth();

        if ($role === 'admin') {
            return $this->adminDashboard($metadata, $systemHealth);
        } elseif ($role === 'manager') {
            return $this->managerDashboard($metadata, $systemHealth);
        } elseif ($role === 'staff') {
            return $this->staffDashboard($metadata);
        } else {
            return $this->userDashboard($metadata);
        }
    }

    private function adminDashboard(array $metadata, array $systemHealth)
    {
        $stats = [
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::where('status', 'Open')->count(),
            'in_progress_tickets' => Ticket::where('status', 'In Progress')->count(),
            'resolved_tickets' => Ticket::where('status', 'Resolved')->count(),
            'closed_tickets' => Ticket::where('status', 'Closed')->count(),
            'waiting_tickets' => Ticket::where('status', 'Waiting User')->count(),
            'critical_tickets' => Ticket::where('priority', 'critical')->where('status', '!=', 'Closed')->count(),
            'high_tickets' => Ticket::where('priority', 'high')->where('status', '!=', 'Closed')->count(),
            'unassigned_tickets' => Ticket::whereNull('assignee_id')->where('status', '!=', 'Closed')->count(),
            'total_users' => User::count(),
            'sla_breach_count' => Ticket::whereNull('first_response_at')
                ->where('status', 'not in', ['Resolved', 'Closed'])
                ->where('created_at', '<', Carbon::now()->subHours(4))
                ->count(),
        ];

        $recentTickets = Ticket::with(['user', 'assignee', 'category'])->latest()->take(5)->get();

        $recentActivity = $this->getRecentActivity();

        $statusCounts = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->get();

        $agentPerformance = User::where('role_id', [2, 3])
            ->withCount(['assignedTickets as active_count' => function ($q) {
                $q->where('status', '!=', 'Closed');
            }])
            ->orderByDesc('active_count')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'recentTickets', 'recentActivity', 'statusCounts', 'agentPerformance', 'metadata', 'systemHealth'
        ));
    }

    private function managerDashboard(array $metadata, array $systemHealth)
    {
        $stats = [
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::where('status', 'Open')->count(),
            'in_progress_tickets' => Ticket::where('status', 'In Progress')->count(),
            'resolved_tickets' => Ticket::where('status', 'Resolved')->count(),
            'closed_tickets' => Ticket::where('status', 'Closed')->count(),
            'unassigned_tickets' => Ticket::whereNull('assignee_id')->where('status', '!=', 'Closed')->count(),
            'critical_tickets' => Ticket::where('priority', 'critical')->where('status', '!=', 'Closed')->count(),
        ];

        $recentTickets = Ticket::with(['user', 'assignee', 'category'])->latest()->take(5)->get();
        $recentActivity = $this->getRecentActivity();
        $statusCounts = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->get();

        $agentPerformance = User::where('role_id', [2, 3])
            ->withCount(['assignedTickets as active_count' => function ($q) {
                $q->where('status', '!=', 'Closed');
            }])
            ->orderByDesc('active_count')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'recentTickets', 'recentActivity', 'statusCounts', 'agentPerformance', 'metadata', 'systemHealth'
        ));
    }

    private function staffDashboard(array $metadata)
    {
        $user = auth()->user();
        $stats = [
            'assigned_to_me' => Ticket::where('assignee_id', $user->id)->where('status', '!=', 'Closed')->count(),
            'open_assigned' => Ticket::where('assignee_id', $user->id)->where('status', 'Open')->count(),
            'in_progress_assigned' => Ticket::where('assignee_id', $user->id)->where('status', 'In Progress')->count(),
            'waiting_assigned' => Ticket::where('assignee_id', $user->id)->where('status', 'Waiting User')->count(),
            'my_tickets' => Ticket::where('user_id', $user->id)->count(),
            'critical_assigned' => Ticket::where('assignee_id', $user->id)
                ->where('priority', 'critical')->where('status', '!=', 'Closed')->count(),
            'resolved_today' => Ticket::where('assignee_id', $user->id)
                ->where('status', 'Resolved')->whereDate('resolved_at', Carbon::today())->count(),
        ];

        $recentTickets = Ticket::where('assignee_id', $user->id)
            ->with(['user', 'category'])->latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'recentTickets', 'metadata'));
    }

    private function userDashboard(array $metadata)
    {
        $user = auth()->user();
        $stats = [
            'my_total' => Ticket::where('user_id', $user->id)->count(),
            'my_open' => Ticket::where('user_id', $user->id)->where('status', 'Open')->count(),
            'my_in_progress' => Ticket::where('user_id', $user->id)->where('status', 'In Progress')->count(),
            'my_resolved' => Ticket::where('user_id', $user->id)->where('status', 'Resolved')->count(),
            'my_closed' => Ticket::where('user_id', $user->id)->where('status', 'Closed')->count(),
        ];

        $recentTickets = Ticket::where('user_id', $user->id)
            ->with(['category', 'assignee'])->latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'recentTickets', 'metadata'));
    }

    private function getRecentActivity(): \Illuminate\Support\Collection
    {
        $activities = collect();

        // Recent ticket creations
        $recentTickets = Ticket::with('user')->latest()->take(3)->get();
        foreach ($recentTickets as $t) {
            $activities->push([
                'type' => 'created',
                'icon' => 'plus-circle',
                'color' => 'blue',
                'message' => "{$t->user->name} created {$t->ticket_number}",
                'time' => $t->created_at,
            ]);
        }

        // Recent status changes (using resolved_at as proxy)
        $recentResolved = Ticket::whereNotNull('resolved_at')
            ->with('assignee')
            ->latest('resolved_at')
            ->take(3)
            ->get();
        foreach ($recentResolved as $t) {
            $activities->push([
                'type' => 'resolved',
                'icon' => 'check-circle',
                'color' => 'green',
                'message' => ($t->assignee->name ?? 'System') . " resolved {$t->ticket_number}",
                'time' => $t->resolved_at,
            ]);
        }

        return $activities->sortByDesc('time')->take(5)->values();
    }

    private function getSystemHealth(): array
    {
        // Database
        try {
            DB::connection()->getPdo();
            $dbStatus = 'healthy';
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        // Storage
        $storageUsed = $this->getDirectorySize(storage_path('app/private'));
        $storageStatus = 'healthy';

        // Queue
        $queueStatus = config('queue.default') === 'sync' ? 'sync' : 'healthy';

        // Session
        $sessionStatus = session()->getId() ? 'healthy' : 'healthy';

        // Cache
        try {
            cache()->put('__health_check', true, 10);
            $cacheStatus = cache()->has('__health_check') ? 'healthy' : 'warning';
        } catch (\Exception $e) {
            $cacheStatus = 'error';
        }

        // Mail
        $mailStatus = config('mail.default') !== 'log' ? 'healthy' : 'warning';

        return [
            'laravel' => ['status' => 'healthy', 'label' => app()->version()],
            'php' => ['status' => 'healthy', 'label' => phpversion()],
            'database' => ['status' => $dbStatus, 'label' => config('database.default')],
            'storage' => ['status' => $storageStatus, 'label' => $storageUsed],
            'queue' => ['status' => $queueStatus, 'label' => config('queue.default')],
            'session' => ['status' => $sessionStatus, 'label' => config('session.driver')],
            'cache' => ['status' => $cacheStatus, 'label' => config('cache.default')],
            'mail' => ['status' => $mailStatus, 'label' => config('mail.default')],
        ];
    }

    private function getDirectorySize(string $path): string
    {
        if (!is_dir($path)) {
            return '0 B';
        }

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1) . ' ' . $units[$i];
    }

    private function getGitCommit(): string
    {
        try {
            $head = trim(file_get_contents(base_path('.git/HEAD')));
            if (str_starts_with($head, 'ref: ')) {
                $ref = substr($head, 5);
                $refPath = base_path('.git/' . $ref);
                if (file_exists($refPath)) {
                    return substr(trim(file_get_contents($refPath)), 0, 7);
                }
            }
            return substr($head, 0, 7);
        } catch (\Exception $e) {
            return 'unknown';
        }
    }
}