@extends('layouts.app')

@section('title', 'Audit Log - MITO IT Helpdesk')

@push('skeleton')
<x-loading variant="cards" :count="3" />
@endpush

@section('content')
<div class="space-y-6">
    <x-page-header title="Audit Log" description="Record of changes to tickets, users, categories and subcategories" />

    {{-- Filters --}}
    <div class="card p-3">
        <form method="GET" action="{{ route('audit.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="form-label">Event</label>
                <select name="event" class="select">
                    <option value="">All</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>{{ $ev }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Entity</label>
                <select name="auditable_type" class="select">
                    <option value="">All</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('auditable_type') === $type ? 'selected' : '' }}>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="search..." class="input" />
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input" />
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input" />
            </div>
            <div class="flex items-center gap-1.5">
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                <a href="{{ route('audit.index') }}" class="btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="text-left font-semibold px-3 py-2.5 text-xs">Time</th>
                        <th class="text-left font-semibold px-3 py-2.5 text-xs">Actor</th>
                        <th class="text-left font-semibold px-3 py-2.5 text-xs">Event</th>
                        <th class="text-left font-semibold px-3 py-2.5 text-xs">Entity</th>
                        <th class="text-left font-semibold px-3 py-2.5 text-xs">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap text-xs">
                                {{ $log->created_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="px-3 py-2.5 text-slate-900 dark:text-white whitespace-nowrap text-xs">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @php
                                    $evClass = match($log->event) {
                                        'created' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                                        'deleted' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/15 dark:text-danger-400',
                                        'status_changed', 'priority_changed', 'assigned', 'reassigned', 'unassigned', 'role_changed' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-400',
                                        'activated', 'deactivated' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                                        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                    };
                                @endphp
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $evClass }}">{{ $log->event }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-slate-600 dark:text-slate-300 whitespace-nowrap text-xs">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                            <td class="px-3 py-2.5 text-slate-600 dark:text-slate-300">
                                @if($log->old_values || $log->new_values)
                                    <div class="space-y-0.5">
                                        @foreach(($log->new_values ?? []) as $key => $value)
                                            @if(!is_array($value))
                                                <div class="text-[10px]">
                                                    <span class="text-slate-400">{{ $key }}:</span>
                                                    <span class="text-slate-400 line-through">{{ $log->old_values[$key] ?? '-' }}</span>
                                                    <span class="text-slate-500 dark:text-slate-400"> → </span>
                                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $value }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                        @if(empty($log->new_values) && !empty($log->old_values))
                                            <div class="text-[10px] text-slate-400">removed</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">No audit entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3 py-2.5 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
