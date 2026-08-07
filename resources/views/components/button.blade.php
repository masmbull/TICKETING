@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'href' => null,
    'block' => false,
    'class' => '',
])

@php
$base = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$sizes = [
    'xs' => 'px-2.5 py-1 text-xs',
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-2.5 text-base',
];

$variants = [
    'primary'   => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 shadow-sm',
    'secondary' => 'bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-200 border border-gray-300 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 focus:ring-blue-500 shadow-sm',
    'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
    'success'   => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-sm',
    'warning'   => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500 shadow-sm',
    'ghost'     => 'text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-white focus:ring-gray-500',
    'link'      => 'text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline-offset-2 hover:underline focus:ring-blue-500',
];

$blockClass = $block ? 'w-full' : '';
$classes = "$base {$sizes[$size]} {$variants[$variant]} {$blockClass} {$class}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        @endif
        @if($icon && !$loading)
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        @endif
        @if($icon && !$loading)
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
    </button>
@endif