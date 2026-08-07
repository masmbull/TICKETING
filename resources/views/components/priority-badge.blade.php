@props(['priority' => 'medium'])

@php
    $priorities = [
        'critical' => 'bg-danger-50 text-danger-600 ring-danger-500/20',
        'high' => 'bg-orange-50 text-orange-600 ring-orange-500/20',
        'medium' => 'bg-warning-50 text-warning-600 ring-warning-500/20',
        'low' => 'bg-success-50 text-success-600 ring-success-500/20',
    ];
    $icons = [
        'critical' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        'high' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>',
        'medium' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L11 6.414V13a1 1 0 11-2 0V6.414L7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-7 9a1 1 0 011 1v3a1 1 0 102 0v-3a1 1 0 112 0v3a1 1 0 102 0v-3a1 1 0 112 0v3a1 1 0 102 0v-3a1 1 0 112 0v-1a1 1 0 112 0v1a3 3 0 01-3 3H5a3 3 0 01-3-3v-1a1 1 0 112 0z" clip-rule="evenodd"/></svg>',
        'low' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>',
    ];
    $slug = is_object($priority) ? ($priority->slug ?? 'medium') : strtolower($priority);
    $label = is_object($priority) ? ($priority->name ?? ucfirst($priority)) : ucfirst($priority);
    $classes = $priorities[$slug] ?? $priorities['medium'];
    $icon = $icons[$slug] ?? $icons['medium'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 ring-inset {$classes}"]) }}>
    {!! $icon !!}
    {{ $label }}
</span>