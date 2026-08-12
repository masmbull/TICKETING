@props([
    'title' => '',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    @isset($header)
        <div class="px-4 sm:px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                @if($title)
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if($description)
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
                @endif
            </div>
            {{ $header }}
        </div>
    @endisset

    <div class="overflow-x-auto">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-4 sm:px-5 py-3 border-t border-slate-200 dark:border-slate-700">
            {{ $footer }}
        </div>
    @endisset
</div>
