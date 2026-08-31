@extends('layouts.app')

@section('title', 'Security Insight - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Security Insight Details</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">#{{ $insight->id }}</p>
        </div>
        <a href="{{ route('security-insights.index') }}" class="inline-flex items-center gap-2 px-3 h-9 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Insights
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-5 sm:p-6">
            @php
                $sev = strtolower($insight->severity);
                $sevClass = match($sev) {
                    'critical' => 'bg-danger-50 dark:bg-danger-500/15 text-danger-700 dark:text-danger-400 ring-danger-600/20 dark:ring-danger-500/30',
                    'high'     => 'bg-orange-50 dark:bg-orange-500/15 text-orange-700 dark:text-orange-400 ring-orange-600/25 dark:ring-orange-500/30',
                    'moderate' => 'bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-400 ring-warning-600/20 dark:ring-warning-500/30',
                    default    => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 ring-slate-500/20 dark:ring-slate-400/30',
                };
            @endphp
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize ring-1 ring-inset {{ $sevClass }}">{{ $insight->severity }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ ucfirst($insight->insight_type) }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 capitalize">{{ $insight->status }}</span>
                    </div>
                    <h2 class="mt-3 text-lg font-bold text-slate-900 dark:text-white">{{ $insight->subject }}</h2>
                </div>
                <div class="text-right text-sm text-slate-500 dark:text-slate-400 flex-shrink-0">
                    <p>Scan: <span class="font-medium text-slate-700 dark:text-slate-200">{{ $insight->scan_performed_on?->format('Y-m-d H:i') ?? '-' }}</span></p>
                    @if($insight->scan_source)
                    <p class="mt-0.5">Source: <span class="font-medium text-slate-700 dark:text-slate-200">{{ $insight->scan_source }}</span></p>
                    @endif
                </div>
            </div>

            <div class="mt-6 border-t border-slate-100 dark:border-slate-700 pt-5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Description</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $insight->description }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
