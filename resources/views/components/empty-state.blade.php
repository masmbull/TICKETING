@props([
    'icon' => null,
    'title' => 'No data found',
    'description' => '',
    'actionHref' => null,
    'actionText' => 'Create',
])

<div class="text-center py-12 px-4">
    @if($icon)
        <div class="mx-auto h-16 w-16 text-gray-300 mb-4">
            {!! $icon !!}
        </div>
    @else
        <div class="mx-auto h-16 w-16 text-gray-300 mb-4">
            <svg class="h-16 w-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
        </div>
    @endif
    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">{{ $description }}</p>
    @else
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Get started by creating your first one.</p>
    @endif
    @if($actionHref)
        <a href="{{ $actionHref }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            {{ $actionText }}
        </a>
    @endif
</div>