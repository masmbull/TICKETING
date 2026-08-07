@props([
    'name',
    'title' => '',
    'subtitle' => '',
    'maxWidth' => 'md',
    'closeButton' => true,
])

@php
$maxWidths = [
    'sm' => 'max-w-md',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
];
@endphp

<div x-data="{ open: false }"
     x-on:open-modal.window="$event.detail === '{{ $name }}' ? open = true : null"
     x-on:close-modal.window="$event.detail === '{{ $name }}' ? open = false : null"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="relative z-50">

    <!-- Backdrop -->
    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="open = false"></div>

    <!-- Panel -->
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-xl bg-white shadow-xl transition-all {{ $maxWidths[$maxWidth] }} w-full">

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                        @if($subtitle)
                            <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($closeButton)
                        <button @click="open = false" type="button" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    @endif
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                @if(isset($footer))
                    <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-end space-x-3">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>