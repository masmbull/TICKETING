@props(['title' => '', 'description' => ''])

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
    @if($description)
        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
    @endif
    @if(isset($actions))
        <div class="mt-4">
            {{ $actions }}
        </div>
    @endif
</div>