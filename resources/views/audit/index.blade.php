@extends('layouts.app')

@section('title', 'Audit Logs - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Audit Logs</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Read-only record of all system changes</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('audit.export.csv', request()->query()) }}"
               class="px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Export CSV
            </a>
            <a href="{{ route('audit.export.excel', request()->query()) }}"
               class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                Export Excel
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700">
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[180px]">
                    <input type="text" name="target" value="{{ request('target') }}"
                           class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white"
                           placeholder="Target ID (e.g. ITSUP-20260818-00001)">
                </div>
                <div class="flex-1 min-w-[180px]">
                    <input type="text" name="actor" value="{{ request('actor') }}"
                           class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white"
                           placeholder="Actor (name or email)">
                </div>
                <div class="min-w-[140px]">
                    <select name="event" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                        <option value="">All Actions</option>
                        @foreach($events as $ev)
                        <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>{{ \App\Http\Controllers\AuditLogController::eventLabel($ev) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <select name="auditable_type" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                        <option value="">All Modules</option>
                        @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('auditable_type') === $type ? 'selected' : '' }}>{{ \App\Http\Controllers\AuditLogController::moduleLabel($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[120px]">
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div class="min-w-[120px]">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                </div>
                <button type="submit" class="px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg">Search</button>
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Module</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Target</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Details</th>
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
                                    'login_success' => 'bg-green-500/10 text-green-600 border-green-200',
                                    'login_failed' => 'bg-red-500/10 text-red-600 border-red-200',
                                    'status_changed', 'priority_changed', 'assigned', 'reassigned', 'unassigned', 'assign_to_me' => 'bg-blue-500/10 text-blue-600 border-blue-200',
                                    'problem_analysis_submitted', 'ticket_completed' => 'bg-indigo-500/10 text-indigo-600 border-indigo-200',
                                    'sla_manually_assigned', 'sla_cleared', 'sla_policy_created', 'sla_mapping_created' => 'bg-purple-500/10 text-purple-600 border-purple-200',
                                    'comment_added' => 'bg-teal-500/10 text-teal-600 border-teal-200',
                                    'password_changed' => 'bg-yellow-500/10 text-yellow-600 border-yellow-200',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-medium border {{ $evClass }}">{{ \App\Http\Controllers\AuditLogController::eventLabel($log->event) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ \App\Http\Controllers\AuditLogController::moduleLabel($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $log->target ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500 max-w-xs truncate">{{ $log->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('audit.show', $log->id) }}"
                               class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 font-medium">Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-500">No audit logs found</td>
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
