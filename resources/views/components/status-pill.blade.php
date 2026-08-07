@props(['status' => 'open'])

@php
    $statuses = [
        'open' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'in_progress' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'waiting' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'resolved' => 'bg-success-50 text-success-700 ring-success-600/20',
        'closed' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        'default' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    ];
    $slug = is_object($status) ? ($status->slug ?? 'default') : $status;
    $label = is_object($status) ? ($status->name ?? ucfirst($status)) : ucfirst(str_replace('_', ' ', $status));
    $classes = $statuses[$slug] ?? $statuses['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold ring-1 ring-inset {$classes}"]) }}>
    {{ $label }}
</span>