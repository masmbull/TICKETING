@extends('layouts.app')

@section('title', 'Tickets - MITO IT Helpdesk')

@php
    $routeName = request()->route()->getName();
    $isAllTickets = $routeName === 'tickets.all';
    $isAssigned = $routeName === 'tickets.assigned';
    $pageTitle = $isAllTickets ? 'All Tickets' : ($isAssigned ? 'Assigned to Me' : 'My Tickets');
    $pageDesc = $isAllTickets ? 'IT Operations workspace' : ($isAssigned ? 'Tickets assigned to you' : 'Your tickets');

    $statusColors = [
        'Waiting Confirmation' => 'bg-blue-500/10 text-blue-600 border-blue-200',
        'In Progress' => 'bg-amber-500/10 text-amber-600 border-amber-200',
        'Completed' => 'bg-emerald-500/10 text-emerald-600 border-emerald-200',
    ];
    $priorityColors = [
        'low' => 'bg-slate-100 text-slate-600 border-slate-200',
        'medium' => 'bg-blue-500/10 text-blue-600 border-blue-200',
        'high' => 'bg-orange-500/10 text-orange-600 border-orange-200',
        'critical' => 'bg-red-500/10 text-red-600 border-red-200',
    ];
    $slaColors = [
        'low' => 'bg-emerald-500/10 text-emerald-600 border-emerald-200',
        'medium' => 'bg-blue-500/10 text-blue-600 border-blue-200',
        'high' => 'bg-orange-500/10 text-orange-600 border-orange-200',
        'critical' => 'bg-red-500/10 text-red-600 border-red-200',
    ];
    $role = auth()->user()->role?->slug ?? 'user';
    $canEdit = in_array($role, ['admin', 'manager']);
    $canManage = in_array($role, ['admin', 'manager', 'staff']);
@endphp

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $pageTitle }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $pageDesc }}</p>
        </div>
        @if(!$isAllTickets)
        <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Ticket
        </a>
        @endif
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-[#E30613]/20 w-48">
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="Waiting Confirmation" {{ request('status') === 'Waiting Confirmation' ? 'selected' : '' }}>Waiting</option>
                    <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                </select>
                @if($canManage)
                <select name="priority" onchange="this.form.submit()" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                @endif
                @if($canEdit && $isAllTickets)
                <select name="assignee" onchange="this.form.submit()" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All Assignees</option>
                    @foreach($staffUsers ?? [] as $staff)
                    <option value="{{ $staff->id }}" {{ request('assignee') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
                @endif
                @if(request()->hasAny(['search', 'status', 'priority', 'assignee']))
                <a href="{{ route($routeName) }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ticket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Requester</th>
                        @if($isAllTickets)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden xl:table-cell">Assignee</th>
                        @endif
                        @if($canManage)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Priority</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        @if($canManage)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">SLA</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden xl:table-cell">Updated</th>
                        <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-mono font-semibold text-slate-900 dark:text-white">{{ $ticket->ticket_number }}</span>
                                <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-[200px]">{{ Str::limit($ticket->description, 40) }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 hidden md:table-cell">{{ $ticket->category->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 hidden lg:table-cell">{{ $ticket->user->name ?? '-' }}</td>
                        @if($isAllTickets)
                        <td class="px-4 py-3 hidden xl:table-cell">
                            @if($canEdit)
                            <select onchange="updateAssignee({{ $ticket->id }}, this.value)" class="text-xs bg-transparent border-0 text-slate-600 dark:text-slate-300 focus:ring-0">
                                <option value="">Unassigned</option>
                                @foreach($staffUsers ?? [] as $staff)
                                <option value="{{ $staff->id }}" {{ $ticket->assignee_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                            @endif
                        </td>
                        @endif
                        @if($canManage)
                        <td class="px-4 py-3 hidden sm:table-cell">
                            @if($canEdit)
                            <select onchange="updatePriority({{ $ticket->id }}, this.value)" class="text-xs bg-transparent border-0 focus:ring-0 capitalize {{ $priorityColors[$ticket->priority] ?? '' }}">
                                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ $ticket->priority === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @else
                            <span class="px-2 py-1 rounded text-xs font-medium border {{ $priorityColors[$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-3">
                            @if($canManage)
                            <select onchange="updateStatus({{ $ticket->id }}, this.value)" class="text-xs bg-transparent border-0 focus:ring-0 {{ $statusColors[$ticket->status] ?? '' }}">
                                <option value="Waiting Confirmation" {{ $ticket->status === 'Waiting Confirmation' ? 'selected' : '' }}>Waiting</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Completed" {{ $ticket->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @else
                            <span class="px-2 py-1 rounded text-xs font-medium border {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                            @endif
                        </td>
                        @if($canManage)
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($ticket->sla_priority)
                            <span class="px-2 py-1 rounded text-xs font-medium border {{ $slaColors[$ticket->sla_priority] ?? '' }}">{{ ucfirst($ticket->sla_priority) }}</span>
                            @else
                            <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-3 text-xs text-slate-400 hidden xl:table-cell">{{ $ticket->updated_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="text-xs font-medium text-[#E30613] hover:text-[#c4050f]">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isAllTickets ? 10 : ($canManage ? 9 : 7) }}" class="px-4 py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">No tickets found</p>
                    </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <p class="text-xs text-slate-500">Showing {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} of {{ $tickets->total() }}</p>
            <div class="flex items-center gap-1">
                @if($tickets->currentPage() > 1)
                <a href="{{ $tickets->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Previous</a>
                @endif
                @for($i = max(1, $tickets->currentPage() - 2); $i <= min($tickets->lastPage(), $tickets->currentPage() + 2); $i++)
                <a href="{{ $tickets->url($i) }}" class="px-3 py-1.5 text-sm rounded-lg {{ $i === $tickets->currentPage() ? 'bg-[#E30613] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">{{ $i }}</a>
                @endfor
                @if($tickets->currentPage() < $tickets->lastPage())
                <a href="{{ $tickets->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Next</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@if($canManage)
<script>
function updateAssignee(id, value) {
    fetch(`/tickets/${id}/assign`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ assignee_id: value })
    }).then(r => r.ok ? location.reload() : alert('Failed'));
}
function updatePriority(id, value) {
    fetch(`/tickets/${id}/priority`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ priority: value })
    }).then(r => r.ok ? location.reload() : alert('Failed'));
}
function updateStatus(id, value) {
    fetch(`/tickets/${id}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status: value })
    }).then(r => r.ok ? location.reload() : alert('Failed'));
}
</script>
@endif
@endsection
