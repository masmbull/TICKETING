@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 shadow-sm ' . ($padding ? 'p-6' : '')]) }}>
    {{ $slot }}
</div>