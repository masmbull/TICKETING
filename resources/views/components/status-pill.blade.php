@props(['status' => 'Open'])

@php
$statusConfig = [
    'Open' => [
        'bg' => 'bg-blue-50',
        'text' => 'text-blue-700',
        'border' => 'border-blue-200',
        'dot' => 'bg-blue-500',
    ],
    'In Progress' => [
        'bg' => 'bg-yellow-50',
        'text' => 'text-yellow-700',
        'border' => 'border-yellow-200',
        'dot' => 'bg-yellow-500',
    ],
    'Waiting User' => [
        'bg' => 'bg-orange-50',
        'text' => 'text-orange-700',
        'border' => 'border-orange-200',
        'dot' => 'bg-orange-500',
    ],
    'Resolved' => [
        'bg' => 'bg-green-50',
        'text' => 'text-green-700',
        'border' => 'border-green-200',
        'dot' => 'bg-green-500',
    ],
    'Closed' => [
        'bg' => 'bg-gray-100',
        'text' => 'text-gray-600',
        'border' => 'border-gray-200',
        'dot' => 'bg-gray-400',
    ],
];

$config = $statusConfig[$status] ?? $statusConfig['Open'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full border {$config['bg']} {$config['text']} {$config['border']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
    {{ $status }}
</span>