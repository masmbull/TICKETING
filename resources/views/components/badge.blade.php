@props(['variant' => 'default', 'size' => 'sm'])

@php
    $variants = [
        'default' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
        'primary' => 'bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400',
        'success' => 'bg-success-50 dark:bg-success-500/15 text-success-600 dark:text-success-400',
        'warning' => 'bg-warning-50 dark:bg-warning-500/15 text-warning-600 dark:text-warning-400',
        'danger' => 'bg-danger-50 dark:bg-danger-500/15 text-danger-600 dark:text-danger-400',
        'info' => 'bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400',
    ];
    $sizes = [
        'xs' => 'px-1.5 py-0.5 text-[10px]',
        'sm' => 'px-2 py-0.5 text-[11px]',
        'md' => 'px-2.5 py-1 text-xs',
    ];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-bold rounded-full uppercase tracking-wider {$variants[$variant]} {$sizes[$size]}"]) }}>
    {{ $slot }}
</span>