@extends('layouts.app')

@section('title', 'Dashboard - MITO IT Helpdesk')

@php
    $role = Auth::user()->role?->slug ?? 'user';
    $statusColors = [
        'Open' => 'bg-primary-50 text-primary-700 border border-primary-200 dark:bg-primary-500/10 dark:text-primary-400 dark:border-primary-500/30',
        'In Progress' => 'bg-warning-50 text-warning-700 border border-warning-200 dark:bg-warning-500/15 dark:text-warning-400 dark:border-warning-500/30',
        'Waiting User' => 'bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-500/15 dark:text-orange-400 dark:border-orange-500/30',
        'Resolved' => 'bg-success-50 text-success-700 border border-success-200 dark:bg-success-500/15 dark:text-success-400 dark:border-success-500/30',
        'Closed' => 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
        'medium' => 'bg-primary-50 text-primary-700 border border-primary-200 dark:bg-primary-500/10 dark:text-primary-400 dark:border-primary-500/30',
        'high' => 'bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-500/15 dark:text-orange-400 dark:border-orange-500/30',
        'critical' => 'bg-danger-50 text-danger-700 border border-danger-200 dark:bg-danger-500/15 dark:text-danger-400 dark:border-danger-500/30',
    ];
@endphp

@section('content')
<div class="min-h-screen -m-6 p-6 lg:p-8">
    {{-- GREETING BAR --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Good {{ now('Asia/Jakarta')->format('H') < 12 ? 'morning' : (now('Asia/Jakarta')->format('H') < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', Auth::user()->name)[0] }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ now('Asia/Jakarta')->format('l, d F Y') }}
                <span id="live-clock" class="ml-2 font-mono text-slate-400 dark:text-slate-500"></span>
            </p>
        </div>
        <div class="flex items-center gap-3 mt-3 sm:mt-0">
            <a href="{{ route('tickets.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Ticket
            </a>
        </div>
    </div>

    {{-- ADMIN DASHBOARD --}}
    @if($role === 'admin')
        {{-- Compact Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white animated-counter">{{ $stats['total_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 animated-counter">{{ $stats['open_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Open</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 animated-counter">{{ $stats['in_progress_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">In Progress</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-success-600 dark:text-success-400 animated-counter">{{ $stats['resolved_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Resolved</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400 animated-counter">{{ $stats['unassigned_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Unassigned</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 animated-counter">{{ $stats['sla_breach_count'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">SLA Breach</div>
            </div>
        </div>

        {{-- Middle Section: Recent Tickets + Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            {{-- Recent Tickets (2 cols) --}}
            <div class="lg:col-span-2 card">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Recent Tickets</h2>
                    <a href="{{ route('tickets.all') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-400 transition-colors">View all &rarr;</a>
                </div>
                @if($recentTickets->count() > 0)
                <div class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">{{ $ticket->ticket_number }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->status }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->priority }}</span>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300 truncate">{{ $ticket->subject }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-xs text-slate-400 dark:text-slate-500">{{ $ticket->created_at->diffForHumans() }}</div>
                            @if($ticket->assignee)
                            <div class="flex items-center gap-1 mt-1">
                                <div class="w-5 h-5 rounded-lg bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300 flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($ticket->assignee->name, 0, 1)) }}</div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ explode(' ', $ticket->assignee->name)[0] }}</span>
                            </div>
                            @else
                            <div class="text-[11px] text-orange-500 dark:text-orange-400 mt-1 font-medium">Unassigned</div>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-500">No tickets yet.</div>
                @endif
            </div>

            {{-- Right Column: Activity + Quick Actions --}}
            <div class="space-y-4">
                {{-- Recent Activity --}}
                <div class="card">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Recent Activity</h2>
                    </div>
                    <div class="px-5 py-3">
                        @if(isset($recentActivity) && $recentActivity->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentActivity as $activity)
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $activity['color'] === 'green' ? 'bg-success-500' : 'bg-primary-500' }}"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $activity['message'] }}</p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $activity['time']->diffForHumans() }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-3">No recent activity</p>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Quick Actions</h2>
                    </div>
                    <div class="px-5 py-3 space-y-2">
                        <a href="{{ route('tickets.create') }}" class="quick-action">
                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">Create Ticket</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="quick-action">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">Manage Users</span>
                        </a>
                        <a href="{{ route('sla-policies.index') }}" class="quick-action">
                            <svg class="w-4 h-4 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">SLA Policies</span>
                        </a>
                    </div>
                </div>

                {{-- System Health --}}
                @if(isset($systemHealth))
                <div class="card">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">System Health</h2>
                    </div>
                    <div class="px-5 py-3">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($systemHealth as $key => $health)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-600 dark:text-slate-400 capitalize">{{ $key }}</span>
                                <span class="health-badge {{ $health['status'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $health['status'] === 'healthy' ? 'bg-success-500' : ($health['status'] === 'warning' ? 'bg-warning-500' : 'bg-danger-500') }}"></span>
                                    {{ $health['label'] }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Version Info Bar --}}
        @if(isset($metadata))
        <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-400 dark:text-slate-500 pb-4">
            <span>v{{ $metadata['version'] }}</span>
            <span>Build {{ $metadata['build'] }}</span>
            <span class="capitalize">{{ $metadata['environment'] }}</span>
            <span class="font-mono">commit {{ $metadata['git_commit'] }}</span>
            <span>Laravel {{ $metadata['laravel_version'] }}</span>
            <span>PHP {{ $metadata['php_version'] }}</span>
        </div>
        @endif

    {{-- MANAGER DASHBOARD --}}
    @elseif($role === 'manager')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-icon bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white animated-counter">{{ $stats['total_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 animated-counter">{{ $stats['open_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 animated-counter">{{ $stats['in_progress_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 animated-counter">{{ $stats['critical_tickets'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Critical</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 card">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Recent Tickets</h2>
                    <a href="{{ route('tickets.all') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-400 transition-colors">View all &rarr;</a>
                </div>
                @if($recentTickets->count() > 0)
                <div class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">{{ $ticket->ticket_number }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->status }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->priority }}</span>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300 truncate">{{ $ticket->subject }}</p>
                        </div>
                        <div class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0">{{ $ticket->created_at->diffForHumans() }}</div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-500">No tickets yet.</div>
                @endif
            </div>

            <div class="space-y-4">
                {{-- Status Breakdown --}}
                <div class="card">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">By Status</h2>
                    </div>
                    <div class="px-5 py-3 space-y-2.5">
                        @foreach($statusCounts as $sc)
                        @php
                            $pct = $stats['total_tickets'] > 0 ? round(($sc->count / $stats['total_tickets']) * 100) : 0;
                            $barColor = match($sc->status) {
                                'Open' => 'bg-primary-500',
                                'In Progress' => 'bg-warning-500',
                                'Waiting User' => 'bg-orange-500',
                                'Resolved' => 'bg-success-500',
                                'Closed' => 'bg-slate-400',
                                default => 'bg-slate-400',
                            };
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">{{ $sc->status }}</span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $sc->count }}</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill {{ $barColor }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Agent Performance --}}
                <div class="card">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Team Performance</h2>
                    </div>
                    <div class="px-5 py-3 space-y-3">
                        @forelse($agentPerformance as $agent)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ strtoupper(substr($agent->name, 0, 2)) }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $agent->name }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500">{{ $agent->active_count }} active tickets</div>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-2">No agents yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    {{-- STAFF DASHBOARD --}}
    @elseif($role === 'staff')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 animated-counter">{{ $stats['assigned_to_me'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Assigned</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 animated-counter">{{ $stats['in_progress_assigned'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="text-2xl font-bold text-danger-600 dark:text-danger-400 animated-counter">{{ $stats['critical_assigned'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Critical</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-2xl font-bold text-success-600 dark:text-success-400 animated-counter">{{ $stats['resolved_today'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Resolved Today</div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">My Assigned Tickets</h2>
                <a href="{{ route('tickets.assigned') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-400 transition-colors">View all &rarr;</a>
            </div>
            @if($recentTickets->count() > 0)
            <div class="divide-y divide-slate-50 dark:divide-slate-800">
                @foreach($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">{{ $ticket->ticket_number }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->status }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->priority }}</span>
                        </div>
                        <p class="text-sm text-slate-700 dark:text-slate-300 truncate">{{ $ticket->subject }}</p>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0">{{ $ticket->created_at->diffForHumans() }}</div>
                </a>
                @endforeach
            </div>
            @else
            <div class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-500">No assigned tickets.</div>
            @endif
        </div>

    {{-- USER DASHBOARD --}}
    @else
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-icon bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white animated-counter">{{ $stats['my_total'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">My Tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 animated-counter">{{ $stats['my_open'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 animated-counter">{{ $stats['my_in_progress'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-2xl font-bold text-success-600 dark:text-success-400 animated-counter">{{ $stats['my_resolved'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Resolved</div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">My Recent Tickets</h2>
                <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-400 transition-colors">View all &rarr;</a>
            </div>
            @if($recentTickets->count() > 0)
            <div class="divide-y divide-slate-50 dark:divide-slate-800">
                @foreach($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">{{ $ticket->ticket_number }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->status }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->priority }}</span>
                        </div>
                        <p class="text-sm text-slate-700 dark:text-slate-300 truncate">{{ $ticket->subject }}</p>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0">{{ $ticket->created_at->diffForHumans() }}</div>
                </a>
                @endforeach
            </div>
            @else
            <div class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                <p>No tickets yet.</p>
                <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-1 mt-2 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create your first ticket
                </a>
            </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const gmt7 = new Date(utc + (7 * 3600000));
        const hours = String(gmt7.getHours()).padStart(2, '0');
        const minutes = String(gmt7.getMinutes()).padStart(2, '0');
        const seconds = String(gmt7.getSeconds()).padStart(2, '0');
        const el = document.getElementById('live-clock');
        if (el) el.textContent = hours + ':' + minutes + ':' + seconds + ' WIB';
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endpush
@endsection
