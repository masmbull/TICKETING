@props([
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
            {{ $label }}
            @if($required)<span class="text-red-500 ml-0.5">*</span>@endif
        </label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 sm:text-sm ' . ($error ? 'border-red-300 dark:border-red-500 text-red-900 dark:text-red-400 placeholder-red-300 dark:placeholder-red-400 focus:border-red-500 focus:ring-red-500' : '')
        ]) }}
    >{{ old($name, $value) }}</textarea>
    @if($error)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
    @if($help && !$error)
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ $help }}</p>
    @endif
</div>