@extends('layouts.app')

@section('title', 'Dashboard - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <!-- Page Title -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Open Tickets -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Open Tickets</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['open'] }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-blue-100 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 2l2-2-2-2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">In Progress</p>
                    <p class="text-3xl font-bold text-amber-600 mt-1">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-amber-100 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17h2M12 7v6l4 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Waiting User -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Waiting User</p>
                    <p class="text-3xl font-bold text-purple-600 mt-1">{{ $stats['waiting_user'] }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-purple-100 text-purple-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Closed Today -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Closed Today</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['closed_today'] }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-green-100 text-green-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Tickets -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Recent Tickets</h3>
                <a href="{{ route('tickets.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $ticket->subject }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500 font-mono">{{ $ticket->ticket_number }}</span>
                                @if($ticket->category)
                                <span class="text-xs text-gray-400">·</span>
                                <span class="text-xs text-gray-500">{{ $ticket->category->name }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $ticket->status === 'Open' ? 'bg-blue-100 text-blue-800' :
                               ($ticket->status === 'In Progress' ? 'bg-amber-100 text-amber-800' :
                               ($ticket->status === 'Waiting User' ? 'bg-purple-100 text-purple-800' :
                               ($ticket->status === 'Resolved' ? 'bg-indigo-100 text-indigo-800' :
                               'bg-green-100 text-green-800'))) }}">
                            {{ $ticket->status }}
                        </span>
                    </div>
                </a>
                @empty
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 2l2-2-2-2m5-3l-4 4 4 4"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No tickets yet</p>
                    <a href="{{ route('tickets.create') }}" class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                        Create your first ticket →
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- My Open Tickets -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">My Open Tickets</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($myOpenTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $ticket->subject }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500 font-mono">{{ $ticket->ticket_number }}</span>
                                @if($ticket->priority)
                                <span class="text-xs text-gray-400">·</span>
                                <span class="text-xs
                                    {{ $ticket->priority === 'critical' ? 'text-red-600 font-semibold' :
                                       ($ticket->priority === 'high' ? 'text-orange-600 font-semibold' :
                                       ($ticket->priority === 'medium' ? 'text-yellow-600' : 'text-gray-500')) }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                </a>
                @empty
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No open tickets — you're all caught up!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection