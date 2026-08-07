@extends('layouts.app')

@section('title', 'Tickets - MITO IT Helpdesk')

@php
    $routeName = request()->route()->getName();
    if ($routeName === 'tickets.all') {
        $pageTitle = 'All Tickets';
        $pageDescription = 'All support tickets across the organization';
    } elseif ($routeName === 'tickets.assigned') {
        $pageTitle = 'Assigned to Me';
        $pageDescription = 'Tickets assigned to you';
    } else {
        $pageTitle = 'My Tickets';
        $pageDescription = 'Tickets submitted by you';
    }

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
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $pageTitle }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $pageDescription }}</p>
        </div>
        @if($routeName !== 'tickets.all')
        <a href="{{ route('tickets.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Ticket
        </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="px-4 py-3 flex flex-col sm:flex-row gap-3">
            <form action="{{ route($routeName === 'tickets.all' ? 'tickets.all' : ($routeName === 'tickets.assigned' ? 'tickets.assigned' : 'tickets.index')) }}" method="GET" class="flex gap-2 flex-1">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="input pl-9" />
                </div>
                <select name="status" onchange="this.form.submit()" class="select">
                    <option value="">All Status</option>
                    <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <select name="priority" onchange="this.form.submit()" class="select">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <button type="submit" class="btn-secondary btn-sm">Filter</button>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
        <div class="stat-card">
            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $tickets->total() }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total</div>
        </div>
        <div class="stat-card">
            <div class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $tickets->getCollection()->where('status', 'Open')->count() }}</div>
            <div class="text-xs text-primary-600 dark:text-primary-400 font-medium">Open</div>
        </div>
        <div class="stat-card">
            <div class="text-lg font-bold text-warning-600 dark:text-warning-400">{{ $tickets->getCollection()->where('status', 'In Progress')->count() }}</div>
            <div class="text-xs text-warning-600 dark:text-warning-400 font-medium">In Progress</div>
        </div>
        <div class="stat-card">
            <div class="text-lg font-bold text-success-600 dark:text-success-400">{{ $tickets->getCollection()->where('status', 'Resolved')->count() }}</div>
            <div class="text-xs text-success-600 dark:text-success-400 font-medium">Resolved</div>
        </div>
        <div class="stat-card">
            <div class="text-lg font-bold text-slate-500 dark:text-slate-400">{{ $tickets->getCollection()->where('status', 'Closed')->count() }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Closed</div>
        </div>
    </div>

    {{-- Tickets Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ticket</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket->id) }}'">
                        <td class="px-4 py-3">
                            <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ $ticket->ticket_number }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-semibold text-slate-900 dark:text-white max-w-xs truncate">{{ $ticket->subject }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-xs">{{ Str::limit($ticket->description, 50) }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-600 dark:text-slate-400">{{ $ticket->category->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded capitalize {{ $priorityColors[$ticket->priority] ?? '' }}">{{ $ticket->priority }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            {{ $ticket->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="empty-state">
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">No tickets found</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $tickets->withQueryString()->links('components.pagination') }}
        </div>
        @endif
    </div>
</div>
@endsection
