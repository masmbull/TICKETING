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
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
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
            'class' => 'select ' . ($error ? 'select-error' : '')
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