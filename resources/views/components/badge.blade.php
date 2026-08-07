@props([
    'variant' => 'gray',
    'size' => 'md',
    'dot' => false,
    'removable' => false,
])

@php
$base = 'inline-flex items-center font-medium rounded-full';

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-xs',
    'lg' => 'px-3 py-1 text-sm',
];

$variants = [
    'gray'   => 'bg-gray-100 text-gray-800',
    'blue'   => 'bg-blue-100 text-blue-800',
    'green'  => 'bg-green-100 text-green-800',
    'red'    => 'bg-red-100 text-red-800',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'purple' => 'bg-purple-100 text-purple-800',
    'indigo' => 'bg-indigo-100 text-indigo-800',
    'pink'   => 'bg-pink-100 text-pink-800',
];

$dotColors = [
    'gray' => 'bg-gray-400', 'blue' => 'bg-blue-400', 'green' => 'bg-green-400',
    'red' => 'bg-red-400', 'yellow' => 'bg-yellow-400', 'purple' => 'bg-purple-400',
    'indigo' => 'bg-indigo-400', 'pink' => 'bg-pink-400',
];
@endphp

<span {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}"]) }}>
    @if($dot)
        <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $dotColors[$variant] }}"></span>
    @endif
    {{ $slot }}
    @if($removable)
        <button type="button" class="ml-1 -mr-0.5 h-3.5 w-3.5 rounded-full inline-flex items-center justify-center hover:bg-black/10 transition" x-on:click="$el.parentElement.remove()">
            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>
    @endif
</span>