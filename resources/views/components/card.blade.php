@props(['padding' => true])

<div {{ $attributes->merge(['class' => "bg-white rounded-xl border border-slate-200/80 shadow-sm " . ($padding ? 'p-6' : '')]) }}>
    {{ $slot }}
</div>