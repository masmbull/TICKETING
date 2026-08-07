@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'compact' => false,
    'noPadding' => false,
    'hover' => false,
])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden' . ($hover ? ' hover:shadow-md transition-shadow duration-200' : '')
]) }}>
    @if($title || $subtitle || $headerActions)
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            @if($headerActions)
                <div class="flex items-center space-x-2">
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    @endif
    <div class="{{ $noPadding ? '' : ($compact ? 'p-4' : 'p-5') }}">
        {{ $slot }}
    </div>
</div>