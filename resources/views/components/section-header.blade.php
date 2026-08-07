@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'actionText' => 'Create',
    'actionIcon' => null,
])

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if($href)
        <div class="mt-4 sm:mt-0">
            <a href="{{ $href }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                @if($actionIcon)
                    <span class="mr-2">{!! $actionIcon !!}</span>
                @else
                    <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                @endif
                {{ $actionText }}
            </a>
        </div>
    @endif
    @if(isset($actions))
        <div class="mt-4 sm:mt-0 flex items-center space-x-2">
            {{ $actions }}
        </div>
    @endif
</div>