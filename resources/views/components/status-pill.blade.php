@props(['status' => 'waiting-confirmation'])

@php
    $statuses = [
        'waiting-confirmation' => 'bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400 ring-blue-600/20 dark:ring-blue-500/30',
        'in_progress' => 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 ring-amber-600/20 dark:ring-amber-500/30',
        'completed' => 'bg-success-50 dark:bg-success-500/15 text-success-700 dark:text-success-400 ring-success-600/20 dark:ring-success-500/30',
        'default' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 ring-slate-500/20 dark:ring-slate-400/30',
    ];
    $slug = is_object($status) ? ($status->slug ?? 'default') : $status;
    $label = is_object($status) ? ($status->name ?? ucfirst($status)) : ucfirst(str_replace('_', ' ', $status));
    $classes = $statuses[$slug] ?? $statuses['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold ring-1 ring-inset {$classes}"]) }}>
    {{ $label }}
</span>