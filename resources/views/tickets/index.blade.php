@extends('layouts.app')

@section('title', 'Tickets - MITO IT Helpdesk')

@php
    $routeName = request()->route()->getName();
    $isAllTickets = $routeName === 'tickets.all';
    $isAssigned = $routeName === 'tickets.assigned';
    $pageTitle = $isAllTickets ? 'All Tickets' : ($isAssigned ? 'Assigned to Me' : 'My Tickets');
    $pageDescription = $isAllTickets ? 'IT Operations workspace' : ($isAssigned ? 'Tickets assigned to you' : 'Tickets submitted by you');

    $statusColors = [
        'Waiting Confirmation' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
        'In Progress' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
        'Completed' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
        'medium' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
        'critical' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    ];
    $slaColors = [
        'low' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
        'medium' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
        'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
        'critical' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    ];
    $role = auth()->user()->role?->slug ?? 'user';
    $canEdit = in_array($role, ['admin', 'manager']);
    $canManageTickets = in_array($role, ['admin', 'manager', 'staff']);

    $hasFilters = request()->hasAny(['status', 'priority', 'assignee']);
@endphp

@section('content')
<div class="space-y-6">
    {{-- PAGE HEADER --}}
    <x-page-header :title="$pageTitle" :description="$pageDescription">
        @slot('actions')
            @if(!$isAllTickets)
            <a href="{{ route('tickets.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </a>
            @endif
        @endslot
    </x-page-header>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <x-stat-card label="Total" :value="$tickets->total()"
            icon="<path stroke-linecap='round' stroke-linejoin='round' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'/>"
            accent="from-blue-600 to-blue-500" />
        <x-stat-card label="Waiting Confirmation" :value="$tickets->getCollection()->where('status', 'Waiting Confirmation')->count()"
            icon="<path stroke-linecap='round' stroke-linejoin='round' d='M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z'/>"
            accent="from-sky-500 to-blue-600" />
        <x-stat-card label="In Progress" :value="$tickets->getCollection()->where('status', 'In Progress')->count()"
            icon="<path stroke-linecap='round' stroke-linejoin='round' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/>"
            accent="from-amber-500 to-orange-500" />
        <x-stat-card label="Completed" :value="$tickets->getCollection()->where('status', 'Completed')->count()"
            icon="<path stroke-linecap='round' stroke-linejoin='round' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/>"
            accent="from-emerald-500 to-teal-600" />
        @if($canManageTickets)
        <x-stat-card label="Critical" :value="$tickets->getCollection()->where('priority', 'critical')->count()"
            icon="<path stroke-linecap='round' stroke-linejoin='round' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'/>"
            accent="from-red-500 to-rose-600" />
        @endif
    </div>

    {{-- Toolbar --}}
    <x-toolbar>
        <form action="{{ route($routeName) }}" method="GET" class="flex flex-wrap items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="toolbar-select">
                <option value="">All Status</option>
                <option value="Waiting Confirmation" {{ request('status') === 'Waiting Confirmation' ? 'selected' : '' }}>Waiting Confirmation</option>
                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
            </select>
            @if($canManageTickets)
            <select name="priority" onchange="this.form.submit()" class="toolbar-select">
                <option value="">All Priority</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @endif
            @if($canEdit && $isAllTickets)
            <select name="assignee" onchange="this.form.submit()" class="toolbar-select">
                <option value="">All Assignees</option>
                @foreach($staffUsers ?? [] as $staff)
                <option value="{{ $staff->id }}" {{ request('assignee') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                @endforeach
            </select>
            @endif
            @if($hasFilters)
            <a href="{{ route($routeName) }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reset
            </a>
            @endif
        </form>
    </x-toolbar>

    {{-- Table --}}
    <x-table>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Category</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Requester</th>
                    @if($isAllTickets)
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden xl:table-cell">Assignee</th>
                    @endif
                    @if($canManageTickets)
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Priority</th>
                    @endif
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    @if($canManageTickets)
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">SLA</th>
                    @endif
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden xl:table-cell">Updated</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-2.5 font-mono text-xs font-bold text-slate-900 dark:text-white whitespace-nowrap">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $ticket->ticket_number }}</a>
                        </td>
                        <td class="px-4 py-2.5">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-1">{{ \Illuminate\Support\Str::limit($ticket->description ?? '', 80) }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400 hidden md:table-cell whitespace-nowrap">{{ $ticket->category->name ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-300 hidden lg:table-cell whitespace-nowrap">{{ $ticket->user->name ?? '-' }}</td>
                        @if($isAllTickets)
                        <td class="px-4 py-2.5 hidden xl:table-cell">
                            @if($canEdit)
                            <select onchange="updateAssignee({{ $ticket->id }}, this.value, this)" class="text-xs border-0 bg-transparent focus:ring-0 p-0 font-medium text-slate-600 dark:text-slate-300 cursor-pointer">
                                <option value="">Unassigned</option>
                                @foreach($staffUsers ?? [] as $staff)
                                <option value="{{ $staff->id }}" {{ $ticket->assignee_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                            @endif
                        @if($canManageTickets)
                        <td class="px-4 py-2.5 hidden sm:table-cell">
                            @if($canEdit)
                            <select onchange="updatePriority({{ $ticket->id }}, this.value, this)" class="text-xs border-0 bg-transparent focus:ring-0 p-0 font-medium capitalize cursor-pointer {{ $priorityColors[$ticket->priority] ?? '' }}">
                                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ $ticket->priority === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @else
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $priorityColors[$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-2.5">
                            @if($canManageTickets)
                            <select onchange="updateStatus({{ $ticket->id }}, this.value, this)" class="text-xs border-0 bg-transparent focus:ring-0 p-0 font-medium cursor-pointer {{ $statusColors[$ticket->status] ?? '' }}">
                                <option value="Waiting Confirmation" {{ $ticket->status === 'Waiting Confirmation' ? 'selected' : '' }}>Waiting Confirmation</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Completed" {{ $ticket->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @else
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                            @endif
                        </td>
                        @if($canManageTickets)
                        <td class="px-4 py-2.5 hidden md:table-cell">
                            @if($canEdit && $isAllTickets)
                            <select onchange="updateSla({{ $ticket->id }}, this.value, this)" class="text-xs border-0 bg-transparent focus:ring-0 p-0 font-medium capitalize cursor-pointer {{ $slaColors[$ticket->sla_priority] ?? 'text-slate-400' }}">
                                <option value="">None</option>
                                <option value="low" {{ $ticket->sla_priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $ticket->sla_priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $ticket->sla_priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ $ticket->sla_priority === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @elseif($ticket->sla_priority)
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $slaColors[$ticket->sla_priority] ?? '' }}">Active · {{ ucfirst($ticket->sla_priority) }}</span>
                            @else
                            <span class="text-xs text-slate-400 dark:text-slate-500">-</span>
                            @endif
                        </td>
                        @endif

                        </td>
                        @endif


                        <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap hidden xl:table-cell">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-2.5">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAllTickets ? 10 : ($canManageTickets ? 9 : 7) }}" class="px-4 py-12 text-center">
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

        @if($tickets->hasPages())
        @slot('footer')
            <div class="flex items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Showing {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }}
                </div>
                <div>
                    {{ $tickets->withQueryString()->links('components.pagination') }}
                </div>
            </div>
        @endslot
        @endif
    </x-table>
</div>


@if($canManageTickets)
<script>
function ticketPatch(url, payload) {
    return fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    }).then(r => {
        if (!r.ok) throw new Error('Request failed (' + r.status + ')');
        return r.json();
    });
}

function ticketSelectError(select) {
    if (select.dataset.prev !== undefined && select.dataset.prev !== 'undefined') {
        select.value = select.dataset.prev;
    }
    window.MITO.toast('Could not update — please try again', 'error');
}

function updateAssignee(ticketId, value, select) {
    select.dataset.prev = select.value;
    ticketPatch(`/tickets/${ticketId}/assign`, { assignee_id: value })
        .then(() => window.MITO.toast(value ? 'Assignee updated' : 'Ticket unassigned', 'success'))
        .catch(() => ticketSelectError(select));
}

function updatePriority(ticketId, value, select) {
    select.dataset.prev = select.value;
    ticketPatch(`/tickets/${ticketId}/priority`, { priority: value })
        .then(() => window.MITO.toast('Priority set to ' + value.charAt(0).toUpperCase() + value.slice(1), 'success'))
        .catch(() => ticketSelectError(select));
}

function updateStatus(ticketId, value, select) {
    select.dataset.prev = select.value;
    ticketPatch(`/tickets/${ticketId}/status`, { status: value })
        .then(() => window.MITO.toast('Status changed to ' + value, 'success'))
        .catch(() => ticketSelectError(select));
}

function updateSla(ticketId, value, select) {
    select.dataset.prev = select.value;
    ticketPatch(`/tickets/${ticketId}/sla`, { sla_priority: value })
        .then(() => window.MITO.toast(value ? 'SLA set to ' + value.charAt(0).toUpperCase() + value.slice(1) : 'SLA cleared', 'success'))
        .catch(() => ticketSelectError(select));
}
</script>
@endif
@endsection
