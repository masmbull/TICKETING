@extends('layouts.app')

@section('title', 'Dashboard - MITO IT Helpdesk')

@php
    $role = Auth::user()->role?->slug ?? 'user';
    $statusColors = [
        'Waiting Confirmation' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200',
        'In Progress' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200',
        'Completed' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        'medium' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200',
        'high' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-200',
        'critical' => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-200',
    ];
@endphp

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="stat-card flex items-center gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ now()->format('l, d M Y') }}</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    @if($role === 'admin' || $role === 'manager')
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-tight tabular-nums">{{ $stats['total_tickets'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 text-lg font-bold flex-shrink-0">
                    📋
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Waiting</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 leading-tight tabular-nums">{{ $stats['waiting_tickets'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-500 text-lg font-bold flex-shrink-0">
                    ⏳
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">In Progress</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 leading-tight tabular-nums">{{ $stats['in_progress_tickets'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-500 text-lg font-bold flex-shrink-0">
                    ⚡
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Completed</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 leading-tight tabular-nums">{{ $stats['completed_tickets'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-lg font-bold flex-shrink-0">
                    ✓
                </div>
            </div>
        </div>

        @if($role === 'admin')
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Unassigned</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1 leading-tight tabular-nums">{{ $stats['unassigned_tickets'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center text-orange-500 text-lg font-bold flex-shrink-0">
                    👤
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Breached</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1 leading-tight tabular-nums">{{ $stats['sla_breach_count'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-500 text-lg font-bold flex-shrink-0">
                    ⚠️
                </div>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Critical</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1 leading-tight tabular-nums">{{ $stats['critical_tickets'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-500 text-lg font-bold flex-shrink-0">
                    ⚠️
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Staff/User KPI --}}
    @if($role === 'staff')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Assigned</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 leading-tight tabular-nums">{{ $stats['assigned_to_me'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-500 text-lg font-bold flex-shrink-0">
                    👤
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">In Progress</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 leading-tight tabular-nums">{{ $stats['in_progress_assigned'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-500 text-lg font-bold flex-shrink-0">
                    ⚡
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Critical</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1 leading-tight tabular-nums">{{ $stats['critical_assigned'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-500 text-lg font-bold flex-shrink-0">
                    ⚠️
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Completed Today</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 leading-tight tabular-nums">{{ $stats['completed_today'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-lg font-bold flex-shrink-0">
                    ✓
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($role === 'user')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">My Tickets</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-tight tabular-nums">{{ $stats['my_total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 text-lg font-bold flex-shrink-0">
                    📋
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Waiting</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 leading-tight tabular-nums">{{ $stats['my_waiting'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-500 text-lg font-bold flex-shrink-0">
                    ⏳
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">In Progress</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 leading-tight tabular-nums">{{ $stats['my_in_progress'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-500 text-lg font-bold flex-shrink-0">
                    ⚡
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="stat-card flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Completed</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 leading-tight tabular-nums">{{ $stats['my_completed'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-lg font-bold flex-shrink-0">
                    ✓
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Recent Tickets --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Tickets</h2>
                <a href="{{ $role === 'admin' || $role === 'manager' ? route('tickets.all') : ($role === 'staff' ? route('tickets.assigned') : route('tickets.index')) }}" class="text-xs font-medium text-[#E30613] hover:text-[#c4050f]">View all</a>
            </div>
            @if($recentTickets->count() > 0)
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-semibold text-slate-900 dark:text-white">{{ $ticket->ticket_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium border {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $ticket->status }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium border {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-600' }}">{{ $ticket->priority }}</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 truncate">{{ Str::limit($ticket->description, 60) }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-slate-400">{{ $ticket->created_at->diffForHumans() }}</p>
                        @if($ticket->assignee)
                        <p class="text-xs text-slate-500 mt-1 leading-tight tabular-nums">{{ $ticket->assignee->name }}</p>
                        @else
                        <p class="text-xs text-orange-500 mt-1 leading-tight tabular-nums">Unassigned</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="px-4 py-8 text-center">
                <div class="text-4xl mx-auto text-slate-300 dark:text-slate-600">📋</div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">No tickets yet</p>
                @if($role === 'user')
                <a href="{{ route('tickets.create') }}" class="inline-block mt-3 text-sm font-medium text-[#E30613] hover:text-[#c4050f]">Create your first ticket</a>
                @endif
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            @if(($role === 'admin' || $role === 'manager') && isset($recentActivity) && $recentActivity->count() > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Activity</h2>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($recentActivity->take(5) as $activity)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full mt-1.5 bg-[#E30613] flex-shrink-0"></div>
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-300">{{ $activity['message'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $activity['time']->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Links --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Quick Links</h2>
                </div>
                <div class="p-3 flex items-start justify-center gap-5">
                    <a href="{{ route('tickets.create') }}" title="Create Ticket" class="flex flex-col items-center gap-1.5 w-16 group">
                        <span class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-[#E30613] group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 text-center leading-tight">New Ticket</span>
                    </a>
                    @if($role === 'admin' || $role === 'manager')
                    <a href="{{ route('users.index') }}" title="Users" class="flex flex-col items-center gap-1.5 w-16 group">
                        <span class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-purple-500 group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2m-8 5H4a3 3 0 01-3-3v-1a3 3 0 013-3h2m8-4a4 4 0 11-8 0 4 4 0 018 0zM16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </span>
                        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 text-center leading-tight">Users</span>
                    </a>
                    <a href="{{ route('categories.index') }}" title="Categories" class="flex flex-col items-center gap-1.5 w-16 group">
                        <span class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-emerald-500 group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                        </span>
                        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 text-center leading-tight">Categories</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection