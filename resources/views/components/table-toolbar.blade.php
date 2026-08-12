@props([
    'searchAction' => null,
    'searchPlaceholder' => 'Search...',
    'searchName' => 'search',
    'searchValue' => null,
    'showSearch' => true,
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <div class="min-w-0">
        {{ $slot }}
    </div>
    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
        @if($showSearch && $searchAction)
            <form action="{{ $searchAction }}" method="GET" class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 dark:text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="{{ $searchName }}" value="{{ $searchValue }}" placeholder="{{ $searchPlaceholder }}"
                       class="w-full sm:w-56 h-9 pl-8 pr-3 text-sm bg-slate-100/70 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60 rounded-lg placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900" />
            </form>
        @endif
        {{ $actions ?? '' }}
    </div>
</div>
