@extends('layouts.app')

@section('title', 'Dashboard - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

    @php
        $role = Auth::user()->role?->slug ?? 'user';
        $statusColors = [
            'Open' => 'bg-blue-100 text-blue-800',
            'In Progress' => 'bg-yellow-100 text-yellow-800',
            'Waiting User' => 'bg-orange-100 text-orange-800',
            'Resolved' => 'bg-green-100 text-green-800',
            'Closed' => 'bg-gray-100 text-gray-800',
        ];
        $priorityColors = [
            'low' => 'bg-gray-100 text-gray-700',
            'medium' => 'bg-blue-100 text-blue-700',
            'high' => 'bg-orange-100 text-orange-700',
            'critical' => 'bg-red-100 text-red-700',
        ];
    @endphp

    {{-- Admin Dashboard --}}
    @if($role === 'admin')
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Total Tickets</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Open</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['open_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">In Progress</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Resolved</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['resolved_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Unassigned</div>
            <div class="text-2xl font-bold text-orange-600">{{ $stats['unassigned_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">SLA Breaches</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['sla_breach_count'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Status Distribution --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Tickets by Status</h2>
            <div class="space-y-3">
                @foreach($statusCounts as $sc)
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$sc->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $sc->status }}</span>
                    <span class="text-sm font-bold text-gray-900">{{ $sc->count }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Agent Performance --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Agent Performance</h2>
            <div class="space-y-3">
                @forelse($agentPerformance as $agent)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">{{ $agent->name }}</span>
                    <span class="text-sm font-bold text-gray-900">{{ $agent->ticket_count }} tickets</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No agents found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Manager Dashboard --}}
    @elseif($role === 'manager')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Total Tickets</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Open</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['open_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">In Progress</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress_tickets'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Critical</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['critical_tickets'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Tickets by Status</h2>
            <div class="space-y-3">
                @foreach($statusCounts as $sc)
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$sc->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $sc->status }}</span>
                    <span class="text-sm font-bold text-gray-900">{{ $sc->count }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Team Performance</h2>
            <div class="space-y-3">
                @forelse($agentPerformance as $agent)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">{{ $agent->name }}</span>
                    <span class="text-sm font-bold text-gray-900">{{ $agent->ticket_count }} tickets</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No agents found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Staff Dashboard --}}
    @elseif($role === 'staff')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Assigned to Me</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['assigned_to_me'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Open</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['open_assigned'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">In Progress</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress_assigned'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Critical</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['critical_assigned'] }}</div>
        </div>
    </div>

    {{-- User Dashboard --}}
    @else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">My Tickets</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['my_total'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Open</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['my_open'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">In Progress</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['my_in_progress'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Resolved</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['my_resolved'] }}</div>
        </div>
    </div>
    @endif

    {{-- Recent Tickets --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Recent Tickets</h2>
        @if($recentTickets->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentTickets as $ticket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="text-blue-600 hover:underline font-medium">{{ $ticket->ticket_number }}</a>
                        </td>
                        <td class="px-3 py-2 text-gray-700">{{ Str::limit($ticket->subject, 50) }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $ticket->status }}</span>
                        </td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($ticket->priority) }}</span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ $ticket->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-500">No tickets yet.</p>
        @endif
    </div>
</div>
@endsection