@props([
    'priority' => '',
    'size' => 'md',
])

@php
$slug = is_object($priority) ? ($priority->slug ?? strtolower($priority)) : Str::slug(strtolower($priority));
$name = is_object($priority) ? ($priority->name ?? $priority) : $priority;

$priorityMap = [
    'critical' => 'bg-red-100 text-red-800 border border-red-200',
    'high'     => 'bg-orange-100 text-orange-800 border border-orange-200',
    'medium'   => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    'low'      => 'bg-green-100 text-green-800 border border-green-200',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-xs',
    'lg' => 'px-3 py-1 text-sm',
];

$class = $priorityMap[$slug] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
@endphp

<span class="inline-flex items-center font-semibold rounded-full {{ $sizes[$size] }} {{ $class }}">
    @if($slug === 'critical')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    @elseif($slug === 'high')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 6a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
    @endif
    {{ $name }}
</span>