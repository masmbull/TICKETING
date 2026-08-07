@props([
    'name',
    'label' => null,
    'value' => '',
    'options' => [],
    'placeholder' => 'Select...',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
    'includeEmpty' => false,
    'keyBy' => null,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
            {{ $label }}
            @if($required)<span class="text-red-500 ml-0.5">*</span>@endif
        </label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 sm:text-sm ' . ($error ? 'border-red-300 dark:border-red-500 text-red-900 dark:text-red-400 focus:border-red-500 focus:ring-red-500' : '')
        ]) }}
    >
        @if($includeEmpty)
            <option value="">{{ $placeholder }}</option>
        @endif
        @if(is_array($options) || $options instanceof \Illuminate\Support\Collection)
            @foreach($options as $key => $option)
                @if(is_array($option))
                    <option value="{{ $option[$keyBy] ?? $key }}" {{ (old($name, $value) == ($option[$keyBy] ?? $key)) ? 'selected' : '' }}>
                        {{ $option['name'] ?? $option['label'] ?? $option['title'] ?? $key }}
                    </option>
                @else
                    <option value="{{ $key }}" {{ (old($name, $value) == $key) ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endif
            @endforeach
        @endif
        {{ $slot }}
    </select>
    @if($error)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
    @if($help && !$error)
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ $help }}</p>
    @endif
</div>