@extends('layouts.app')

@section('title', 'My Tickets - MITO IT Helpdesk')

@php
    $statusColors = [
        'Open' => 'bg-blue-100 text-blue-700',
        'In Progress' => 'bg-amber-100 text-amber-700',
        'Waiting User' => 'bg-orange-100 text-orange-700',
        'Resolved' => 'bg-green-100 text-green-700',
        'Closed' => 'bg-gray-100 text-gray-500',
    ];
    $priorityColors = [
        'low' => 'bg-gray-100 text-gray-600',
        'medium' => 'bg-blue-100 text-blue-700',
        'high' => 'bg-orange-100 text-orange-700',
        'critical' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="min-h-screen bg-gray-50 -m-6 p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">My Tickets</h1>
            <p class="text-sm text-gray-500 mt-1">Tickets submitted by you</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Ticket
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-4">
        <div class="px-4 py-3 flex flex-col sm:flex-row gap-3">
            <form action="{{ route('tickets.index') }}" method="GET" class="flex gap-2 flex-1">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <select name="status" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <select name="priority" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <button type="submit" class="px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
        <div class="bg-white rounded-lg border border-gray-200 px-3 py-2 text-center">
            <div class="text-lg font-bold text-gray-900">{{ $tickets->total() }}</div>
            <div class="text-xs text-gray-500">Total</div>
        </div>
        <div class="bg-white rounded-lg border border-blue-200 px-3 py-2 text-center">
            <div class="text-lg font-bold text-blue-600">{{ $tickets->getCollection()->where('status', 'Open')->count() }}</div>
            <div class="text-xs text-blue-600">Open</div>
        </div>
        <div class="bg-white rounded-lg border border-amber-200 px-3 py-2 text-center">
            <div class="text-lg font-bold text-amber-600">{{ $tickets->getCollection()->where('status', 'In Progress')->count() }}</div>
            <div class="text-xs text-amber-600">In Progress</div>
        </div>
        <div class="bg-white rounded-lg border border-green-200 px-3 py-2 text-center">
            <div class="text-lg font-bold text-green-600">{{ $tickets->getCollection()->where('status', 'Resolved')->count() }}</div>
            <div class="text-xs text-green-600">Resolved</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 px-3 py-2 text-center">
            <div class="text-lg font-bold text-gray-500">{{ $tickets->getCollection()->where('status', 'Closed')->count() }}</div>
            <div class="text-xs text-gray-500">Closed</div>
        </div>
    </div>

    {{-- Tickets Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket->id) }}'">
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold text-blue-600">{{ $ticket->ticket_number }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 max-w-xs truncate">{{ $ticket->subject }}</div>
                            <div class="text-xs text-gray-400 truncate max-w-xs">{{ Str::limit($ticket->description, 50) }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-600">{{ $ticket->category->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $statusColors[$ticket->status] ?? '' }}">{{ $ticket->status }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded capitalize {{ $priorityColors[$ticket->priority] ?? '' }}">{{ $ticket->priority }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                            {{ $ticket->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm text-gray-500 font-medium">No tickets found</p>
                            <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $tickets->withQueryString()->links('components.pagination') }}
        </div>
        @endif
    </div>
</div>
@endsection