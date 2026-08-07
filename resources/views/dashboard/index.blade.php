@extends('layouts.app')

@section('title', 'Dashboard - MITO IT Helpdesk')

@push('styles')
<style>
    /* Live clock */
    #live-clock { font-variant-numeric: tabular-nums; }
    
    /* Stat cards */
    .stat-card {
        @apply bg-white rounded-xl border border-gray-200 p-4 transition-all duration-200 hover:shadow-md;
    }
    .stat-card .stat-icon {
        @apply w-10 h-10 rounded-lg flex items-center justify-center;
    }
    .stat-card .stat-value {
        @apply text-2xl font-bold tracking-tight;
    }
    .stat-card .stat-label {
        @apply text-xs font-medium text-gray-500 uppercase tracking-wider;
    }
    
    /* Progress bars */
    .progress-bar {
        @apply h-1.5 rounded-full bg-gray-100 overflow-hidden;
    }
    .progress-bar .progress-fill {
        @apply h-full rounded-full transition-all duration-500;
    }
    
    /* Activity timeline */
    .activity-dot {
        @apply w-2.5 h-2.5 rounded-full ring-2 ring-white;
    }
    
    /* System health badge */
    .health-badge {
        @apply inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium;
    }
    .health-badge.healthy { @apply bg-green-50 text-green-700; }
    .health-badge.warning { @apply bg-yellow-50 text-yellow-700; }
    .health-badge.error { @apply bg-red-50 text-red-700; }
    .health-badge.sync { @apply bg-blue-50 text-blue-700; }
    
    /* Quick action cards */
    .quick-action {
        @apply flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-150 cursor-pointer;
    }
</style>
@endpush

@php
    $role = Auth::user()->role?->slug ?? 'user';
    $statusColors = [
        'Open' => 'bg-blue-100 text-blue-700 border border-blue-200',
        'In Progress' => 'bg-amber-100 text-amber-700 border border-amber-200',
        'Waiting User' => 'bg-orange-100 text-orange-700 border border-orange-200',
        'Resolved' => 'bg-green-100 text-green-700 border border-green-200',
        'Closed' => 'bg-gray-100 text-gray-500 border border-gray-200',
    ];
    $priorityColors = [
        'low' => 'bg-gray-100 text-gray-600 border border-gray-200',
        'medium' => 'bg-blue-100 text-blue-700 border border-blue-200',
        'high' => 'bg-orange-100 text-orange-700 border border-orange-200',
        'critical' => 'bg-red-100 text-red-700 border border-red-200',
    ];

@section('content')
<div class="min-h-screen bg-gray-50 -m-6 p-6">
    {{-- ============================================ --}}
    {{-- GREETING BAR --}}
    {{-- ============================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Good {{ now('Asia/Jakarta')->format('H') < 12 ? 'morning' : (now('Asia/Jakarta')->format('H') < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', Auth::user()->name)[0] }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ now('Asia/Jakarta')->format('l, d F Y') }}
                <span id="live-clock" class="ml-2 font-mono text-gray-400"></span>
            </p>
        </div>
        <div class="flex items-center gap-3 mt-3 sm:mt-0">
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Ticket
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- ADMIN DASHBOARD --}}
    {{-- ============================================ --}}
    @if($role === 'admin')
        {{-- Compact Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            {{-- Total --}}
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-gray-100 text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="stat-value text-gray-900">{{ $stats['total_tickets'] }}</div>
                <div class="stat-label">Total</div>
            </div>

            {{-- Open --}}
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-blue-50 text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-value text-blue-600">{{ $stats['open_tickets'] }}</div>
                <div class="stat-label">Open</div>
            </div>

            {{-- In Progress --}}
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-amber-50 text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
                <div class="stat-value text-amber-600">{{ $stats['in_progress_tickets'] }}</div>
                <div class="stat-label">In Progress</div>
            </div>

            {{-- Resolved --}}
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-green-50 text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-value text-green-600">{{ $stats['resolved_tickets'] }}</div>
                <div class="stat-label">Resolved</div>
            </div>

            {{-- Unassigned --}}
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-orange-50 text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
                <div class="stat-value text-orange-600">{{ $stats['unassigned_tickets'] }}</div>
                <div class="stat-label">Unassigned</div>
            </div>

            {{-- SLA Breach --}}
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="stat-icon bg-red-50 text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="stat-value text-red-600">{{ $stats['sla_breach_count'] }}</div>
                <div class="stat-label">SLA Breach</div>
            </div>
        </div>

        {{-- Middle Section: Recent Tickets + Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            {{-- Recent Tickets (2 cols) --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Recent Tickets</h2>
                    <a href="{{ route('tickets.all') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">View all →</a>
                </div>
                @if($recentTickets->count() > 0)
                <div class="divide-y divide-gray-50">
                    @foreach($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-mono font-semibold text-gray-900">{{ $ticket->ticket_number }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->status }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->priority }}</span>
                            </div>
                            <p class="text-sm text-gray-700 truncate">{{ $ticket->subject }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</div>
                            @if($ticket->assignee)
                            <div class="flex items-center gap-1 mt-1">
                                <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($ticket->assignee->name, 0, 1)) }}</div>
                                <span class="text-[11px] text-gray-500">{{ explode(' ', $ticket->assignee->name)[0] }}</span>
                            </div>
                            @else
                            <div class="text-[11px] text-orange-500 mt-1">Unassigned</div>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-8 text-center text-sm text-gray-400">No tickets yet.</div>
                @endif
            </div>

            {{-- Right Column: Activity + Quick Actions --}}
            <div class="space-y-4">
                {{-- Recent Activity --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Recent Activity</h2>
                    </div>
                    <div class="px-5 py-3">
                        @if(isset($recentActivity) && $recentActivity->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentActivity as $activity)
                            <div class="flex items-start gap-3">
                                <div class="activity-dot mt-1.5 flex-shrink-0 {{ $activity['color'] === 'green' ? 'bg-green-500' : 'bg-blue-500' }}"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-700 leading-relaxed">{{ $activity['message'] }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $activity['time']->diffForHumans() }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-gray-400 text-center py-3">No recent activity</p>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="px-5 py-3 space-y-2">
                        <a href="{{ route('tickets.create') }}" class="quick-action">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-sm text-gray-700">Create Ticket</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="quick-action">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span class="text-sm text-gray-700">Manage Users</span>
                        </a>
                        <a href="{{ route('sla-policies.index') }}" class="quick-action">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm text-gray-700">SLA Policies</span>
                        </a>
                    </div>
                </div>

                {{-- System Health --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">System Health</h2>
                    </div>
                    <div class="px-5 py-3">
                        @if(isset($systemHealth))
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($systemHealth as $key => $health)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-600 capitalize">{{ $key }}</span>
                                <span class="health-badge {{ $health['status'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $health['status'] === 'healthy' ? 'bg-green-500' : ($health['status'] === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                                    {{ $health['label'] }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Version Info Bar --}}
        @if(isset($metadata))
        <div class="flex flex-wrap items-center gap-4 text-[11px] text-gray-400 pb-4">
            <span>v{{ $metadata['version'] }}</span>
            <span>Build {{ $metadata['build'] }}</span>
            <span class="capitalize">{{ $metadata['environment'] }}</span>
            <span class="font-mono">commit {{ $metadata['git_commit'] }}</span>
            <span>Laravel {{ $metadata['laravel_version'] }}</span>
            <span>PHP {{ $metadata['php_version'] }}</span>
        </div>
        @endif

    {{-- ============================================ --}}
    {{-- MANAGER DASHBOARD --}}
    {{-- ============================================ --}}
    @elseif($role === 'manager')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-icon bg-gray-100 text-gray-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="stat-value text-gray-900">{{ $stats['total_tickets'] }}</div>
                <div class="stat-label">Total Tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-blue-50 text-blue-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="stat-value text-blue-600">{{ $stats['open_tickets'] }}</div>
                <div class="stat-label">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-amber-50 text-amber-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="stat-value text-amber-600">{{ $stats['in_progress_tickets'] }}</div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red-50 text-red-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="stat-value text-red-600">{{ $stats['critical_tickets'] }}</div>
                <div class="stat-label">Critical</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Recent Tickets</h2>
                    <a href="{{ route('tickets.all') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">View all →</a>
                </div>
                @if($recentTickets->count() > 0)
                <div class="divide-y divide-gray-50">
                    @foreach($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-mono font-semibold text-gray-900">{{ $ticket->ticket_number }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->status }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->priority }}</span>
                            </div>
                            <p class="text-sm text-gray-700 truncate">{{ $ticket->subject }}</p>
                        </div>
                        <div class="text-xs text-gray-400 flex-shrink-0">{{ $ticket->created_at->diffForHumans() }}</div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-8 text-center text-sm text-gray-400">No tickets yet.</div>
                @endif
            </div>

            <div class="space-y-4">
                {{-- Status Breakdown --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">By Status</h2>
                    </div>
                    <div class="px-5 py-3 space-y-2.5">
                        @foreach($statusCounts as $sc)
                        @php
                            $pct = $stats['total_tickets'] > 0 ? round(($sc->count / $stats['total_tickets']) * 100) : 0;
                            $barColor = match($sc->status) {
                                'Open' => 'bg-blue-500',
                                'In Progress' => 'bg-amber-500',
                                'Waiting User' => 'bg-orange-500',
                                'Resolved' => 'bg-green-500',
                                'Closed' => 'bg-gray-400',
                                default => 'bg-gray-400',
                            };
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-600">{{ $sc->status }}</span>
                                <span class="text-xs font-semibold text-gray-900">{{ $sc->count }}</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill {{ $barColor }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Agent Performance --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Team Performance</h2>
                    </div>
                    <div class="px-5 py-3 space-y-3">
                        @forelse($agentPerformance as $agent)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ strtoupper(substr($agent->name, 0, 2)) }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">{{ $agent->name }}</div>
                                <div class="text-[11px] text-gray-400">{{ $agent->active_count }} active tickets</div>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-gray-400 text-center py-2">No agents yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    {{-- ============================================ --}}
    {{-- STAFF DASHBOARD --}}
    {{-- ============================================ --}}
    @elseif($role === 'staff')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-icon bg-blue-50 text-blue-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="stat-value text-blue-600">{{ $stats['assigned_to_me'] }}</div>
                <div class="stat-label">Assigned</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-amber-50 text-amber-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="stat-value text-amber-600">{{ $stats['in_progress_assigned'] }}</div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red-50 text-red-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="stat-value text-red-600">{{ $stats['critical_assigned'] }}</div>
                <div class="stat-label">Critical</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-50 text-green-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="stat-value text-green-600">{{ $stats['resolved_today'] ?? 0 }}</div>
                <div class="stat-label">Resolved Today</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">My Assigned Tickets</h2>
                <a href="{{ route('tickets.assigned') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">View all →</a>
            </div>
            @if($recentTickets->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-mono font-semibold text-gray-900">{{ $ticket->ticket_number }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->status }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->priority }}</span>
                        </div>
                        <p class="text-sm text-gray-700 truncate">{{ $ticket->subject }}</p>
                    </div>
                    <div class="text-xs text-gray-400 flex-shrink-0">{{ $ticket->created_at->diffForHumans() }}</div>
                </a>
                @endforeach
            </div>
            @else
            <div class="px-5 py-8 text-center text-sm text-gray-400">No assigned tickets.</div>
            @endif
        </div>

    {{-- ============================================ --}}
    {{-- USER DASHBOARD --}}
    {{-- ============================================ --}}
    @else
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="stat-card">
                <div class="stat-icon bg-gray-100 text-gray-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="stat-value text-gray-900">{{ $stats['my_total'] }}</div>
                <div class="stat-label">My Tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-blue-50 text-blue-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="stat-value text-blue-600">{{ $stats['my_open'] }}</div>
                <div class="stat-label">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-amber-50 text-amber-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="stat-value text-amber-600">{{ $stats['my_in_progress'] }}</div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-50 text-green-600 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="stat-value text-green-600">{{ $stats['my_resolved'] }}</div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">My Recent Tickets</h2>
                <a href="{{ route('tickets.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">View all →</a>
            </div>
            @if($recentTickets->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-mono font-semibold text-gray-900">{{ $ticket->ticket_number }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->status }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-500' }}">{{ $ticket->priority }}</span>
                        </div>
                        <p class="text-sm text-gray-700 truncate">{{ $ticket->subject }}</p>
                    </div>
                    <div class="text-xs text-gray-400 flex-shrink-0">{{ $ticket->created_at->diffForHumans() }}</div>
                </a>
                @endforeach
            </div>
            @else
            <div class="px-5 py-8 text-center text-sm text-gray-400">
                <p>No tickets yet.</p>
                <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-1 mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium">
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
    // Live Clock GMT+7
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