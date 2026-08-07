@props(['priority' => 'medium'])

@php
$priorityConfig = [
    'critical' => [
        'bg' => 'bg-red-100',
        'text' => 'text-red-800',
        'border' => 'border-red-300',
        'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
    ],
    'high' => [
        'bg' => 'bg-orange-100',
        'text' => 'text-orange-800',
        'border' => 'border-orange-300',
        'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    ],
    'medium' => [
        'bg' => 'bg-yellow-100',
        'text' => 'text-yellow-800',
        'border' => 'border-yellow-300',
        'icon' => 'M5 12h14',
    ],
    'low' => [
        'bg' => 'bg-blue-100',
        'text' => 'text-blue-800',
        'border' => 'border-blue-300',
        'icon' => 'M19 14l-7 7m0 0l-7-7m7 7V3',
    ],
];

$config = $priorityConfig[$priority] ?? $priorityConfig['medium'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded border {$config['bg']} {$config['text']} {$config['border']}"]) }}>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
    </svg>
    {{ ucfirst($priority) }}
</span>