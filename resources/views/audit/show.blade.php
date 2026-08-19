@extends('layouts.app')

@section('title', 'Audit Detail - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('audit.index') }}" class="p-1 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Audit Detail</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Event #{{ $log->id }}</p>
            </div>
        </div>
        <span class="px-2.5 py-1 rounded text-xs font-medium border {{ match($log->event) {
            'created' => 'bg-emerald-500/10 text-emerald-600 border-emerald-200',
            'deleted', 'login_failed' => 'bg-red-500/10 text-red-600 border-red-200',
            'login_success', 'activated' => 'bg-green-500/10 text-green-600 border-green-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        } }}">
            {{ \App\Http\Controllers\AuditLogController::eventLabel($log->event) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Event Information</h2>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Timestamp</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $log->created_at?->format('d M Y, H:i:s') ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Actor</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $log->user?->name ?? 'System' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Action</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ \App\Http\Controllers\AuditLogController::eventLabel($log->event) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Module</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ \App\Http\Controllers\AuditLogController::moduleLabel($log->auditable_type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Target</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $log->target ?? ('#' . $log->auditable_id) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Target ID</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $log->auditable_id }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</h2>
            </div>
            <div class="p-4">
                <p class="text-sm text-slate-700 dark:text-slate-300 break-words">{{ $log->description ?? 'No description available.' }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Old Values</h2>
            </div>
            <div class="p-4">
                @if($log->old_values)
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($log->old_values as $key => $val)
                        @if(!is_array($val))
                        <tr>
                            <td class="py-1.5 text-slate-500 pr-2">{{ $key }}</td>
                            <td class="py-1.5 text-slate-400 break-all">{{ $val ?? 'null' }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-sm text-slate-400">No old values</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">New Values</h2>
            </div>
            <div class="p-4">
                @if($log->new_values)
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($log->new_values as $key => $val)
                        @if(!is_array($val))
                        <tr>
                            <td class="py-1.5 text-slate-500 pr-2">{{ $key }}</td>
                            <td class="py-1.5 text-slate-900 dark:text-white break-all">{{ $val ?? 'null' }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-sm text-slate-400">No new values</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Request Info</h2>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">IP Address</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $log->ip_address ?? 'Unknown' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">User Agent</span>
                    <span class="text-slate-400 font-mono text-xs break-all">{{ $log->user_agent ?? 'Unknown' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Security Verification</h2>
            </div>
            <div class="p-4 space-y-2">
                @php
                    $rawOld = $log->old_values;
                    $rawNew = $log->new_values;
                    $hasPassword = false;
                    if (is_array($rawOld) && array_key_exists('password', $rawOld)) $hasPassword = true;
                    if (is_array($rawNew) && array_key_exists('password', $rawNew)) $hasPassword = true;
                @endphp
                @if($hasPassword)
                <div class="flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg">
                    <svg class="w-4 h-4 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9V3m0 0l-3 3m3-3l3 3M12 12v9m-6-3l3-3m-3 3l3-3"/></svg>
                    <span class="text-xs text-red-700 dark:text-red-300">Sensitive field detected — ensure password values are stripped.</span>
                </div>
                @else
                <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs text-emerald-700 dark:text-emerald-300">No sensitive fields (password, tokens) stored in this entry.</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
