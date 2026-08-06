<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index(): View
    {
        $userId = auth()->id();

        $stats = [
            'open'          => Ticket::where('user_id', $userId)->where('status', 'Open')->count(),
            'in_progress'   => Ticket::where('user_id', $userId)->where('status', 'In Progress')->count(),
            'waiting_user'  => Ticket::where('user_id', $userId)->where('status', 'Waiting User')->count(),
            'closed_today'  => Ticket::where('user_id', $userId)->where('status', 'Closed')->whereDate('updated_at', today())->count(),
        ];

        $recentTickets = Ticket::where('user_id', $userId)
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        $myOpenTickets = Ticket::where('user_id', $userId)
            ->whereIn('status', ['Open', 'In Progress', 'Waiting User'])
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentTickets', 'myOpenTickets'));
    }
}