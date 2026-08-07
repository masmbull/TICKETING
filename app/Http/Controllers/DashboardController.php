<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard with role-based statistics.
     */
    public function index()
    {
        $user = auth()->user();
        $role = $user->role?->slug ?? 'user';

        $stats = [];

        if ($role === 'admin') {
            $stats['total_tickets'] = Ticket::count();
            $stats['open_tickets'] = Ticket::where('status', 'Open')->count();
            $stats['in_progress_tickets'] = Ticket::where('status', 'In Progress')->count();
            $stats['resolved_tickets'] = Ticket::where('status', 'Resolved')->count();
            $stats['closed_tickets'] = Ticket::where('status', 'Closed')->count();
            $stats['waiting_tickets'] = Ticket::where('status', 'Waiting User')->count();
            $stats['critical_tickets'] = Ticket::where('priority', 'critical')->where('status', '!=', 'Closed')->count();
            $stats['high_tickets'] = Ticket::where('priority', 'high')->where('status', '!=', 'Closed')->count();
            $stats['unassigned_tickets'] = Ticket::whereNull('assignee_id')->where('status', '!=', 'Closed')->count();
            $stats['total_users'] = User::count();
            $stats['total_categories'] = Category::count();

            // SLA breach count (tickets where first_response_at is null and created more than response_hours ago)
            $stats['sla_breach_count'] = Ticket::whereNull('first_response_at')
                ->where('status', 'not in', ['Resolved', 'Closed'])
                ->where('created_at', '<', Carbon::now()->subHours(4))
                ->count();

            // Recent tickets
            $recentTickets = Ticket::with(['user', 'assignee', 'category'])->latest()->take(10)->get();

            // Tickets by status for chart
            $statusCounts = Ticket::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')->get();

            // Agent performance
            $agentPerformance = User::where('role_id', [2, 3])
                ->withCount(['assignedTickets as ticket_count' => function ($q) {
                    $q->where('status', '!=', 'Closed');
                }])
                ->orderByDesc('ticket_count')
                ->take(5)
                ->get();

            return view('dashboard.index', compact(
                'stats', 'recentTickets', 'statusCounts', 'agentPerformance'
            ));

        } elseif ($role === 'manager') {
            $stats['total_tickets'] = Ticket::count();
            $stats['open_tickets'] = Ticket::where('status', 'Open')->count();
            $stats['in_progress_tickets'] = Ticket::where('status', 'In Progress')->count();
            $stats['resolved_tickets'] = Ticket::where('status', 'Resolved')->count();
            $stats['closed_tickets'] = Ticket::where('status', 'Closed')->count();
            $stats['unassigned_tickets'] = Ticket::whereNull('assignee_id')->where('status', '!=', 'Closed')->count();
            $stats['critical_tickets'] = Ticket::where('priority', 'critical')->where('status', '!=', 'Closed')->count();

            $recentTickets = Ticket::with(['user', 'assignee', 'category'])->latest()->take(10)->get();
            $statusCounts = Ticket::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')->get();

            $agentPerformance = User::where('role_id', [2, 3])
                ->withCount(['assignedTickets as ticket_count' => function ($q) {
                    $q->where('status', '!=', 'Closed');
                }])
                ->orderByDesc('ticket_count')
                ->take(5)
                ->get();

            return view('dashboard.index', compact(
                'stats', 'recentTickets', 'statusCounts', 'agentPerformance'
            ));

        } elseif ($role === 'staff') {
            $stats['assigned_to_me'] = Ticket::where('assignee_id', $user->id)
                ->where('status', '!=', 'Closed')->count();
            $stats['open_assigned'] = Ticket::where('assignee_id', $user->id)
                ->where('status', 'Open')->count();
            $stats['in_progress_assigned'] = Ticket::where('assignee_id', $user->id)
                ->where('status', 'In Progress')->count();
            $stats['waiting_assigned'] = Ticket::where('assignee_id', $user->id)
                ->where('status', 'Waiting User')->count();
            $stats['my_tickets'] = Ticket::where('user_id', $user->id)->count();
            $stats['critical_assigned'] = Ticket::where('assignee_id', $user->id)
                ->where('priority', 'critical')
                ->where('status', '!=', 'Closed')->count();

            $recentTickets = Ticket::where('assignee_id', $user->id)
                ->with(['user', 'category'])->latest()->take(10)->get();

            return view('dashboard.index', compact('stats', 'recentTickets'));

        } else {
            // Regular user
            $stats['my_total'] = Ticket::where('user_id', $user->id)->count();
            $stats['my_open'] = Ticket::where('user_id', $user->id)->where('status', 'Open')->count();
            $stats['my_in_progress'] = Ticket::where('user_id', $user->id)->where('status', 'In Progress')->count();
            $stats['my_resolved'] = Ticket::where('user_id', $user->id)->where('status', 'Resolved')->count();
            $stats['my_closed'] = Ticket::where('user_id', $user->id)->where('status', 'Closed')->count();

            $recentTickets = Ticket::where('user_id', $user->id)
                ->with(['category', 'assignee'])->latest()->take(10)->get();

            return view('dashboard.index', compact('stats', 'recentTickets'));
        }
    }
}