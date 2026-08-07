@props(['variant' => 'default', 'size' => 'sm'])

@php
    $variants = [
        'default' => 'bg-slate-100 text-slate-700',
        'primary' => 'bg-primary-50 text-primary-600',
        'success' => 'bg-success-50 text-success-600',
        'warning' => 'bg-warning-50 text-warning-600',
        'danger' => 'bg-danger-50 text-danger-600',
        'info' => 'bg-primary-50 text-primary-600',
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