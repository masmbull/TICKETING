@props(['title' => '', 'description' => ''])

<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
    @if($description)
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
</div>