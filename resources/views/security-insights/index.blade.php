@extends('layouts.app')

@section('title', 'Security Insights - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Security Insights</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Monitor security findings and configuration recommendations</p>
        </div>
        <a href="{{ route('security-insights.export.csv', request()->query()) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Export Insights
        </a>
    </div>

    {{-- Summary / insight counts --}}
    @php
        $critical = $severityCounts['critical'] ?? 0;
        $high = $severityCounts['high'] ?? 0;
        $moderate = $severityCounts['moderate'] ?? 0;
        $low = $severityCounts['low'] ?? 0;
        $summary = [
            'Total Insights' => ['count' => $activeCount, 'accent' => 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            'Critical' => ['count' => $critical, 'accent' => 'text-danger-600 dark:text-danger-400 bg-danger-50 dark:bg-danger-500/10', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            'High' => ['count' => $high, 'accent' => 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-500/10', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            'Moderate' => ['count' => $moderate, 'accent' => 'text-warning-600 dark:text-warning-400 bg-warning-50 dark:bg-warning-500/10', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            'Low' => ['count' => $low, 'accent' => 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700', 'icon' => 'M9 12l2 2 4-4'],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($summary as $label => $info)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 {{ $info['accent'] }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white leading-tight tabular-nums">{{ $info['count'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
    {{-- Main card (filters + tabs + table) --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        {{-- Filters --}}
        <form method="GET" action="{{ route('security-insights.index') }}" class="p-4 border-b border-slate-200 dark:border-slate-700">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                {{-- Search --}}
                <div class="relative lg:col-span-4">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search insights..."
                           class="w-full h-9 pl-9 pr-3 text-sm bg-slate-100/70 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60 rounded-lg placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#E30613]/20 focus:border-[#E30613] focus:bg-white dark:focus:bg-slate-900">
                </div>


                {{-- Severity --}}
                <div class="lg:col-span-3">
                    <select name="severity"
                            class="w-full h-9 px-3 text-sm bg-slate-100/70 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60 rounded-lg text-slate-700 dark:text-slate-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#E30613]/20 focus:border-[#E30613] focus:bg-white dark:focus:bg-slate-900">
                        <option value="">All Severities</option>
                        @foreach(['low' => 'Low', 'moderate' => 'Moderate', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                            <option value="{{ $val }}" {{ request('severity') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Insight type --}}
                <div class="lg:col-span-2">
                    <select name="insight_type"
                            class="w-full h-9 px-3 text-sm bg-slate-100/70 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60 rounded-lg text-slate-700 dark:text-slate-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#E30613]/20 focus:border-[#E30613] focus:bg-white dark:focus:bg-slate-900">
                        <option value="">All Types</option>
                        @foreach($insightTypes as $type)
                            <option value="{{ $type }}" {{ request('insight_type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Source --}}
                <div class="lg:col-span-3">
                    <input type="text" name="scan_source" value="{{ request('scan_source') }}"
                           placeholder="Source..."
                           class="w-full h-9 px-3 text-sm bg-slate-100/70 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60 rounded-lg text-slate-700 dark:text-slate-300 placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#E30613]/20 focus:border-[#E30613] focus:bg-white dark:focus:bg-slate-900">
                </div>
            </div>

            {{-- Filter actions --}}
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 h-9 text-sm font-medium bg-[#E30613] hover:bg-[#c4050f] text-white rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Apply Filters
                </button>
                @if(count($activeFilters))
                    <a href="{{ route('security-insights.index', ['tab' => $tab]) }}" class="inline-flex items-center gap-1 px-3 h-9 text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear Filters
                    </a>
                @endif

                {{-- Active filter chips --}}
                @if(count($activeFilters))
                    <div class="flex flex-wrap items-center gap-2 ml-1">
                        <span class="text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">Active:</span>
                        @foreach($activeFilters as $f)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-full">
                                {{ $f['label'] }}
                                <a href="{{ route('security-insights.index', array_merge(request()->except($f['key']), ['tab' => $tab])) }}" class="text-blue-400 hover:text-blue-600 dark:hover:text-blue-200" title="Remove filter">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </form>

        {{-- Tabs --}}
        <div class="px-4 pt-3 border-b border-slate-200 dark:border-slate-700 flex items-center gap-1 bg-slate-50/50 dark:bg-slate-800/40">
            <a href="{{ route('security-insights.index', array_merge(request()->except('tab'), ['tab' => 'active'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold border-b-2 -mb-px transition-colors {{ $tab === 'active' ? 'text-[#E30613] border-[#E30613]' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-200' }}">
                Active
                <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $tab === 'active' ? 'bg-[#E30613]/10 text-[#E30613]' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">{{ $activeCount }}</span>
            </a>
            <a href="{{ route('security-insights.index', array_merge(request()->except('tab'), ['tab' => 'archived'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold border-b-2 -mb-px transition-colors {{ $tab === 'archived' ? 'text-[#E30613] border-[#E30613]' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-200' }}">
                Archived
                <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $tab === 'archived' ? 'bg-[#E30613]/10 text-[#E30613]' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">{{ $archivedCount }}</span>
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">

            <table class="w-full min-w-[840px]">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="w-10 px-4 py-3 text-left">
                            <input type="checkbox" class="rounded border-slate-300 dark:border-slate-600 text-[#E30613] focus:ring-[#E30613]/30" aria-label="Select all">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap w-28">Severity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-[34%]">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Scanned On</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                    @forelse($insights as $insight)
                    <tr class="group hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                        {{-- Checkbox --}}
                        <td class="px-4 py-3 align-middle">
                            <input type="checkbox" class="rounded border-slate-300 dark:border-slate-600 text-[#E30613] focus:ring-[#E30613]/30" aria-label="Select insight">
                        </td>

                        {{-- Severity --}}
                        <td class="px-4 py-3 align-middle">
                            @php
                                $sev = strtolower($insight->severity);
                                $sevClass = match($sev) {
                                    'critical' => 'bg-danger-50 dark:bg-danger-500/15 text-danger-700 dark:text-danger-400 ring-danger-600/20 dark:ring-danger-500/30',
                                    'high'     => 'bg-orange-50 dark:bg-orange-500/15 text-orange-700 dark:text-orange-400 ring-orange-600/25 dark:ring-orange-500/30',
                                    'moderate' => 'bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-400 ring-warning-600/20 dark:ring-warning-500/30',
                                    default    => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 ring-slate-500/20 dark:ring-slate-400/30',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold capitalize ring-1 ring-inset whitespace-nowrap {{ $sevClass }}">
                                @if($sev === 'critical' || $sev === 'high' || $sev === 'moderate')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                @endif
                                {{ $insight->severity }}
                            </span>
                        </td>

                        {{-- Description --}}
                        <td class="px-4 py-3 align-middle">
                            <p class="text-sm text-slate-700 dark:text-slate-200 leading-snug line-clamp-2">{{ $insight->description }}</p>
                        </td>

                        {{-- Subject --}}
                        <td class="px-4 py-3 align-middle">
                            <span class="text-sm font-medium text-slate-900 dark:text-white line-clamp-1 max-w-[220px]">{{ $insight->subject }}</span>
                        </td>

                        {{-- Insight type --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ ucfirst($insight->insight_type) }}</span>
                        </td>

                        {{-- Scan performed on --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $insight->scan_performed_on?->format('M d, Y H:i') ?? '-' }}</span>
                            @if($insight->scan_source)
                                <span class="block text-[11px] text-slate-400 dark:text-slate-500">{{ $insight->scan_source }}</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('security-insights.show', $insight->id) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:text-[#E30613] dark:text-slate-400 dark:hover:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                    Details
                                </a>

                                {{-- Kebab menu --}}
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button type="button" @click="open = !open"
                                            class="inline-flex items-center justify-center w-8 h-8 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors"
                                            aria-label="More actions" aria-haspopup="true" :aria-expanded="open">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </button>

                                    <div x-show="open" x-cloak x-transition.opacity.duration.100
                                         class="absolute right-0 top-9 z-30 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-1">
                                        @if($tab === 'active')
                                            <form method="POST" action="{{ route('security-insights.archive', $insight->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                                    <span class="inline-flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                                        Archive
                                                    </span>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('security-insights.restore', $insight->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                                    <span class="inline-flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                        Restore
                                                    </span>
                                                </button>
                                            </form>
                                        @endif

                                        <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                                        <form method="POST" action="{{ route('security-insights.destroy', $insight->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete this security insight permanently?')" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">
                                                <span class="inline-flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Delete
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <p class="mt-3 text-sm font-medium text-slate-900 dark:text-white">No security insights found</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">There are currently no {{ $tab === 'active' ? 'active' : 'archived' }} findings matching the selected filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination / result count --}}
        @if($insights->hasPages() || $insights->total() > 0)
            @php $label = $tab === 'active' ? 'active' : 'archived'; @endphp
            @if($insights->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ $insights->firstItem() }}–{{ $insights->lastItem() }} of {{ $insights->total() }} {{ $label }} insight{{ $insights->total() === 1 ? '' : 's' }}</p>
                    <div class="pagination">{{ $insights->links() }}</div>
                </div>
            @else
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400">
                    Showing {{ $insights->firstItem() }}–{{ $insights->lastItem() }} of {{ $insights->total() }} {{ $label }} insight{{ $insights->total() === 1 ? '' : 's' }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

