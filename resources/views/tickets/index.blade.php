@extends('layouts.app')

@section('title', 'Tickets - MITO IT Helpdesk')

@php
    $routeName = request()->route()->getName();
    $isAllTickets = $routeName === 'tickets.all';
    $isAssigned = $routeName === 'tickets.assigned';
    $pageTitle = $isAllTickets ? 'All Tickets' : ($isAssigned ? 'Assigned to Me' : 'My Tickets');
    $pageDescription = $isAllTickets ? 'IT Operations workspace' : ($isAssigned ? 'Tickets assigned to you' : 'Tickets submitted by you');

    $statusColors = [
        'Open' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
        'In Progress' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
        'Waiting User' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
        'Resolved' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
        'Closed' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
        'medium' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
        'critical' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    ];
    $role = auth()->user()->role?->slug ?? 'user';
    $canEdit = in_array($role, ['admin', 'manager']);
@endphp

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $pageTitle }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $pageDescription }}</p>
        </div>
        @if(!$isAllTickets)
        <a href="{{ route('tickets.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create
        </a>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3">
            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $tickets->total() }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total</div>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 border-l-4 border-blue-500">
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $tickets->getCollection()->where('status', 'Open')->count() }}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Open</div>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 border-l-4 border-amber-500">
            <div class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $tickets->getCollection()->where('status', 'In Progress')->count() }}</div>
            <div class="text-xs text-amber-600 dark:text-amber-400 font-medium">In Progress</div>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 border-l-4 border-green-500">
            <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $tickets->getCollection()->where('status', 'Resolved')->count() }}</div>
            <div class="text-xs text-green-600 dark:text-green-400 font-medium">Resolved</div>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 border-l-4 border-red-500">
            <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ $tickets->getCollection()->where('priority', 'critical')->count() }}</div>
            <div class="text-xs text-red-600 dark:text-red-400 font-medium">Critical</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <form action="{{ route($routeName) }}" method="GET" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="input pl-9 h-9 text-sm w-56" />
            </div>
            <select name="status" onchange="this.form.submit()" class="select h-9 text-sm">
                <option value="">Status</option>
                <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Waiting User" {{ request('status') === 'Waiting User' ? 'selected' : '' }}>Waiting</option>
                <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <select name="priority" onchange="this.form.submit()" class="select h-9 text-sm">
                <option value="">Priority</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @if($canEdit && $isAllTickets)
            <select name="assignee" onchange="this.form.submit()" class="select h-9 text-sm">
                <option value="">Assignee</option>
                @foreach($staffUsers ?? [] as $staff)
                <option value="{{ $staff->id }}" {{ request('assignee') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                @endforeach
            </select>
            @endif
            <button type="submit" class="btn-secondary btn-sm">Apply</button>
            @if(request()->hasAny(['search', 'status', 'priority', 'assignee']))
            <a href="{{ route($routeName) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Subject</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Category</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Requester</th>
                        @if($isAllTickets)
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Assignee</th>
                        @endif
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Priority</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">SLA</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Updated</th>
                        @if($canEdit)
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-50 dark:bg-slate-900/50">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors h-[52px]">
                        <td class="px-4 py-2">
                            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ $ticket->ticket_number }}</span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="text-sm font-medium text-slate-900 dark:text-white truncate max-w-xs">{{ $ticket->subject }}</div>
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $ticket->category->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $ticket->user->name ?? '-' }}</span>
                        </td>
                        @if($isAllTickets)
                        <td class="px-4 py-2">
                            @if($canEdit)
                            <select onchange="updateAssignee({{ $ticket->id }}, this.value)" class="text-xs border-0 bg-transparent focus:ring-0 p-0">
                                <option value="">Unassigned</option>
                                @foreach($staffUsers ?? [] as $staff)
                                <option value="{{ $staff->id }}" {{ $ticket->assignee_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-2">
                            @if($canEdit)
                            <select onchange="updatePriority({{ $ticket->id }}, this.value)" class="text-xs border-0 bg-transparent focus:ring-0 p-0 font-medium capitalize {{ $priorityColors[$ticket->priority] ?? '' }}">
                                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ $ticket->priority === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @else
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $priorityColors[$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($canEdit)
                            <select onchange="updateStatus({{ $ticket->id }}, this.value)" class="text-xs border-0 bg-transparent focus:ring-0 p-0 font-medium {{ $statusColors[$ticket->status] ?? '' }}">
                                <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Waiting User" {{ $ticket->status === 'Waiting User' ? 'selected' : '' }}>Waiting</option>
                                <option value="Resolved" {{ $ticket->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="Closed" {{ $ticket->status === 'Closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @else
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $ticket->sla_policy_id ? 'Active' : '-' }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        @if($canEdit)
                        <td class="px-4 py-2">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">View</a>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isAllTickets ? ($canEdit ? 10 : 9) : ($canEdit ? 9 : 8) }}" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">No tickets found</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Showing {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }}
            </div>
            <div>
                {{ $tickets->withQueryString()->links('components.pagination') }}
            </div>
        </div>
        @endif
    </div>
</div>

@if($canEdit)
<script>
function updateAssignee(ticketId, userId) {
    fetch(`/tickets/${ticketId}/assign`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ assignee_id: userId })
    }).then(r => r.json());
}

function updatePriority(ticketId, priority) {
    fetch(`/tickets/${ticketId}/priority`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ priority: priority })
    }).then(r => r.json());
}

function updateStatus(ticketId, status) {
    fetch(`/tickets/${ticketId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: status })
    }).then(r => r.json());
}
</script>
@endif
@endsection