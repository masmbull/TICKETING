@props([
    'label' => '',
    'value' => '',
    'icon' => null,
    'accent' => 'from-blue-600 to-blue-500',
])

<div {{ $attributes->merge(['class' => 'stat-card']) }}>
    <div class="flex items-center gap-4">
        @if($icon)
        <div class="stat-icon bg-gradient-to-br {{ $accent }}">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $icon !!}</svg>
        </div>
        @endif
        <div class="min-w-0">
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white leading-tight tabular-nums">{{ $value }}</div>
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 truncate">{{ $label }}</div>
        </div>
    </div>
</div>
