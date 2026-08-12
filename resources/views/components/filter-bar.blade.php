@props([
    'action' => null,
    'method' => 'GET',
])

<div class="card mb-6 p-4 sm:p-5">
    <form @if($action) action="{{ $action }}" @endif method="{{ $method }}">
        {{ $slot }}

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2 pt-4">
                {{ $actions }}
            </div>
        @endisset
    </form>
</div>
