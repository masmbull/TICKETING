@extends('layouts.app')

@section('title', 'Service Status - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Service Status"
        description="Status layanan & API yang dipakai aplikasi ini"
        :breadcrumb="['Settings' => route('settings.index'), 'Service Status' => null]">
        <x-slot name="actions">
            <a href="{{ route('settings.services') }}" class="btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </a>
            <a href="{{ route('settings.services', ['probe' => 0]) }}" class="btn-secondary btn-sm" title="Skip the Microsoft Graph network probes">
                Fast load (no API probe)
            </a>
        </x-slot>
    </x-page-header>

    {{-- Summary --}}
    <div class="card p-5 flex items-center gap-4 flex-wrap
                {{ $summary['healthy'] ? 'border-l-4 border-l-success-500' : 'border-l-4 border-l-danger-500' }}">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                    {{ $summary['healthy'] ? 'bg-success-50 dark:bg-success-500/10' : 'bg-danger-50 dark:bg-danger-500/10' }}">
            @if($summary['healthy'])
                <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @else
                <svg class="w-6 h-6 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            @endif
        </div>
        <div class="min-w-0">
            <div class="text-base font-bold text-slate-900 dark:text-white">
                {{ $summary['healthy'] ? 'All systems operational' : $summary['fail'].' service(s) need attention' }}
            </div>
            <div class="text-sm text-slate-500 dark:text-slate-400">
                {{ $summary['ok'] }}/{{ $summary['total'] }} checks passed
                @unless($probeGraph) &middot; Graph network probe skipped @endunless
            </div>
        </div>
    </div>

    @php
        $tone = [
            'ok'   => ['dot' => 'bg-success-500', 'text' => 'text-success-600 dark:text-success-400', 'label' => 'Online'],
            'warn' => ['dot' => 'bg-warning-500', 'text' => 'text-warning-600 dark:text-warning-400', 'label' => 'Warning'],
            'fail' => ['dot' => 'bg-danger-500', 'text' => 'text-danger-600 dark:text-danger-400', 'label' => 'Offline'],
            'skip' => ['dot' => 'bg-slate-400', 'text' => 'text-slate-500 dark:text-slate-400', 'label' => 'Skipped'],
        ];
    @endphp

    @foreach($groups as $groupName => $checks)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $groupName }}</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($checks as $check)
                    @php $c = $tone[$check['status']] ?? $tone['skip']; @endphp
                    <div class="px-4 py-3 flex items-start gap-3">
                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $c['dot'] }} {{ $check['status'] === 'ok' ? 'animate-pulse' : '' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $check['label'] }}</span>
                                <span class="text-xs font-bold {{ $c['text'] }}">{{ $c['label'] }}</span>
                                @if($check['ms'] > 0)
                                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $check['ms'] }} ms</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 break-words mt-0.5">{{ $check['detail'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <p class="text-xs text-slate-400 dark:text-slate-500">
        Secrets, tokens, dan kredensial tidak pernah ditampilkan di halaman ini — hanya status konfigurasi.
        Untuk diagnosa lengkap dari CLI: <code class="font-mono">php artisan mito:diagnose</code>.
    </p>
</div>
@endsection