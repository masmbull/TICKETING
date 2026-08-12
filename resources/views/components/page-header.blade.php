@props([
    'title' => '',
    'description' => '',
    'icon' => null,
    'breadcrumb' => [],
])

<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            @if(count($breadcrumb))
                <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                    @foreach($breadcrumb as $label => $route)
                        @if($route)
                            <a href="{{ $route }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $label }}</a>
                        @else
                            <span>{{ $label }}</span>
                        @endif
                        @if(!$loop->last)
                            <svg class="w-3 h-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        @endif
                    @endforeach
                </nav>
            @endif

            <div class="flex items-center gap-3">
                @if($icon)
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icon !!}</svg>
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight leading-tight">{{ $title }}</h1>
                    @if($description)
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
                    @endif
                </div>
            </div>
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @isset($toolbar)
        <div class="mt-4">
            {{ $toolbar }}
        </div>
    @endisset
</div>
