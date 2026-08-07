@props(['padding' => true])

<div {{ $attributes->merge(['class' => "bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-sm " . ($padding ? 'p-6' : '')]) }}>
    {{ $slot }}
</div>