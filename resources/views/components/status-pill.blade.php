@props([
    'status' => '',
    'size' => 'md',
])

@php
$slug = is_object($status) ? ($status->slug ?? '') : Str::slug($status);
$name = is_object($status) ? ($status->name ?? '') : $status;

$statusMap = [
    'open' => 'bg-blue-100 text-blue-800',
    'in-progress' => 'bg-yellow-100 text-yellow-800',
    'waiting-user' => 'bg-purple-100 text-purple-800',
    'waiting_user' => 'bg-purple-100 text-purple-800',
    'waiting user' => 'bg-purple-100 text-purple-800',
    'resolved' => 'bg-green-100 text-green-800',
    'closed' => 'bg-gray-100 text-gray-600',
];

$dotMap = [
    'open' => 'bg-blue-400',
    'in-progress' => 'bg-yellow-400',
    'waiting-user' => 'bg-purple-400',
    'waiting_user' => 'bg-purple-400',
    'waiting user' => 'bg-purple-400',
    'resolved' => 'bg-green-400',
    'closed' => 'bg-gray-400',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-xs',
    'lg' => 'px-3 py-1 text-sm',
];

$class = $statusMap[$slug] ?? 'bg-gray-100 text-gray-800';
$dot = $dotMap[$slug] ?? 'bg-gray-400';
@endphp

<span class="inline-flex items-center font-medium rounded-full {{ $sizes[$size] }} {{ $class }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} mr-1.5"></span>
    {{ $name }}
</span>