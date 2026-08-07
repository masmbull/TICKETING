@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$sizes = [
    'xs' => 'px-2 py-1 text-xs gap-1',
    'sm' => 'px-3 py-1.5 text-sm gap-1.5',
    'md' => 'px-4 py-2 text-sm gap-2',
    'lg' => 'px-5 py-2.5 text-base gap-2',
];

$variants = [
    'primary'   => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 shadow-sm',
    'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-blue-500',
    'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
    'success'   => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-sm',
    'ghost'     => 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:ring-gray-500',
];

$classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif