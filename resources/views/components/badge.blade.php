@props(['variant' => 'gray', 'size' => 'sm'])

@php
$baseClasses = 'inline-flex items-center font-medium rounded-full';

$sizes = [
    'xs' => 'px-1.5 py-0.5 text-[10px]',
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
];

$variants = [
    'gray'     => 'bg-gray-100 text-gray-700',
    'blue'     => 'bg-blue-100 text-blue-700',
    'green'    => 'bg-green-100 text-green-700',
    'red'      => 'bg-red-100 text-red-700',
    'yellow'   => 'bg-yellow-100 text-yellow-700',
    'purple'   => 'bg-purple-100 text-purple-700',
    'indigo'   => 'bg-indigo-100 text-indigo-700',
    'pink'     => 'bg-pink-100 text-phpink-700',
    'orange'   => 'bg-orange-100 text-orange-700',
];

$classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['sm']) . ' ' . ($variants[$variant] ?? $variants['gray']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>