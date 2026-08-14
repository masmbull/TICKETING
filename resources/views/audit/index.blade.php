@extends('layouts.app')

@section('title', 'Audit Logs - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Audit Logs</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">System activity</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                <select name="event" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All Events</option>
                    @foreach($events as $ev)
                    <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>{{ $ev }}</option>
                    @endforeach
                </select>
                <select name="auditable_type" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('auditable_type') === $type ? 'selected' : '' }}>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                <button type="submit" class="px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg">Filter</button>
                @if(request()->all())
                <a href="{{ route('audit.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Actor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Entity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-white whitespace-nowrap">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $evClass = match($log->event) {
                                    'created' => 'bg-emerald-500/10 text-emerald-600 border-emerald-200',
                                    'deleted' => 'bg-red-500/10 text-red-600 border-red-200',
                                    'status_changed', 'priority_changed', 'assigned' => 'bg-blue-500/10 text-blue-600 border-blue-200',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-medium border {{ $evClass }}">{{ $log->event }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">
                            @if($log->old_values || $log->new_values)
                            <div class="space-y-1">
                                @foreach(($log->new_values ?? []) as $key => $val)
                                @if(!is_array($val))
                                <div><span class="text-slate-400">{{ $key }}:</span> <span class="line-through text-slate-500">{{ $log->old_values[$key] ?? '-' }}</span> → <span class="text-slate-700 dark:text-slate-200">{{ $val }}</span></div>
                                @endif
                                @endforeach
                            </div>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">No audit logs found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
