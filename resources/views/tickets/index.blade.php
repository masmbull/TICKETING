@extends('layouts.app')

@section('title', 'My Tickets - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Tickets</h1>
            <p class="mt-1 text-sm text-gray-500">Manage and track your IT support requests</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Ticket
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('tickets.index') }}" class="space-y-4">
            <!-- Search Row -->
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by ticket number or subject..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Search
                </button>
                @if(request('search') || request('status') || request('priority'))
                <a href="{{ route('tickets.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                    Clear
                </a>
                @endif
            </div>

            <!-- Filter Row -->
            <div class="flex gap-3 flex-wrap">
                <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <select name="priority" onchange="this.form.submit()" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>

                <span class="self-center text-sm text-gray-500">
                    {{ $tickets->total() }} {{ Str::plural('ticket', $tickets->total()) }} found
                </span>
            </div>
        </form>
    </div>

    <!-- Active Filters -->
    @if(request('search') || request('status') || request('priority'))
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-sm text-gray-500">Active filters:</span>
        @if(request('search'))
        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full">
            Search: "{{ request('search') }}"
            <a href="{{ route('tickets.index', array_filter(['status' => request('status'), 'priority' => request('priority')])) }}" class="hover:text-blue-900">&times;</a>
        </span>
        @endif
        @if(request('status'))
        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full">
            Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}
            <a href="{{ route('tickets.index', array_filter(['search' => request('search'), 'priority' => request('priority')])) }}" class="hover:text-blue-900">&times;</a>
        </span>
        @endif
        @if(request('priority'))
        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full">
            Priority: {{ ucfirst(request('priority')) }}
            <a href="{{ route('tickets.index', array_filter(['search' => request('search'), 'status' => request('status')])) }}" class="hover:text-blue-900">&times;</a>
        </span>
        @endif
    </div>
    @endif

    <!-- Ticket List -->
    @if($tickets->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 divide-y divide-gray-200">
        @foreach($tickets as $ticket)
        <a href="{{ route('tickets.show', $ticket->id) }}" class="block hover:bg-gray-50 transition-colors p-4">
            <div class="flex items-start gap-4">
                <!-- Status Dot -->
                <div class="flex-shrink-0 mt-1">
                    @if($ticket->status === 'open')
                    <span class="block w-3 h-3 rounded-full bg-blue-500"></span>
                    @elseif($ticket->status === 'in_progress')
                    <span class="block w-3 h-3 rounded-full bg-yellow-500"></span>
                    @elseif($ticket->status === 'pending')
                    <span class="block w-3 h-3 rounded-full bg-orange-500"></span>
                    @elseif($ticket->status === 'resolved')
                    <span class="block w-3 h-3 rounded-full bg-green-500"></span>
                    @else
                    <span class="block w-3 h-3 rounded-full bg-gray-400"></span>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-mono text-gray-500">{{ $ticket->ticket_number }}</span>
                        @if($ticket->category)
                        <span class="inline-flex items-center bg-gray-100 text-gray-700 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ $ticket->category->name }}
                        </span>
                        @endif
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 mt-1">{{ $ticket->subject }}</h3>
                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($ticket->description), 150) }}</p>
                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                        <span>{{ $ticket->created_at->diffForHumans() }}</span>
                        @if($ticket->comments_count > 0)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ $ticket->comments_count }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Priority & Status -->
                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    @if($ticket->priority === 'critical')
                    <span class="inline-flex items-center bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                    @elseif($ticket->priority === 'high')
                    <span class="inline-flex items-center bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                    @elseif($ticket->priority === 'medium')
                    <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                    @else
                    <span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                    @endif

                    <span class="text-xs font-medium
                        @if($ticket->status === 'open') text-blue-700
                        @elseif($ticket->status === 'in_progress') text-yellow-700
                        @elseif($ticket->status === 'pending') text-orange-700
                        @elseif($ticket->status === 'resolved') text-green-700
                        @else text-gray-700 @endif">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($tickets->hasPages())
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets
        </div>
        <div class="flex gap-1">
            @if($tickets->onFirstPage())
            <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-200 rounded-lg cursor-not-allowed">Previous</span>
            @else
            <a href="{{ $tickets->appends(request()->query())->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Previous</a>
            @endif

            @foreach($tickets->getUrlRange(max(1, $tickets->currentPage() - 2), min($tickets->lastPage(), $tickets->currentPage() + 2)) as $page => $url)
            <a href="{{ $url }}"
               class="px-3 py-2 text-sm border rounded-lg {{ $page === $tickets->currentPage() ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 bg-white border-gray-200 hover:bg-gray-50' }}">
                {{ $page }}
            </a>
            @endforeach

            @if($tickets->hasMorePages())
            <a href="{{ $tickets->appends(request()->query())->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Next</a>
            @else
            <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-200 rounded-lg cursor-not-allowed">Next</span>
            @endif
        </div>
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 2l2-2-2-2m8 4a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No tickets found</h3>
        <p class="mt-2 text-sm text-gray-500">
            @if(request('search') || request('status') || request('priority'))
                Try adjusting your search or filter criteria.
            @else
                Get started by creating your first support ticket.
            @endif
        </p>
        @if(!request('search') && !request('status') && !request('priority'))
        <a href="{{ route('tickets.create') }}" class="mt-4 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Ticket
        </a>
        @endif
    </div>
    @endif
</div>
@endsection