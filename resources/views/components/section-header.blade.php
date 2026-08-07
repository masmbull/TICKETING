@props(['title' => '', 'description' => ''])

<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-900">{{ $title }}</h2>
    @if($description)
    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    @endif
</div>