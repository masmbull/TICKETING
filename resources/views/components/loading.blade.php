@props([
    'variant' => 'cards', // dashboard | table | cards | form
    'count' => 1,
    'rows' => 5,
    'cols' => 4,
])

@php
    $bar = 'h-3.5 bg-slate-200 dark:bg-slate-800 rounded-full';
    $barSm = 'h-3 bg-slate-200 dark:bg-slate-800 rounded-full';
    $block = 'bg-slate-200 dark:bg-slate-800 rounded-xl';
@endphp

<div class="space-y-6 animate-pulse" role="status" aria-busy="true">
    @if($variant === 'dashboard')
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @for($i = 0; $i < 6; $i++)
                <div class="card p-4 flex items-center gap-3">
                    <div class="w-10 h-10 {{ $block }}"></div>
                    <div class="flex-1 space-y-2">
                        <div class="{{ $bar }} w-12"></div>
                        <div class="{{ $barSm }} w-10"></div>
                    </div>
                </div>
            @endfor
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="card p-5 space-y-4 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div class="{{ $bar }} w-40"></div>
                    <div class="{{ $bar }} w-20"></div>
                </div>
                @for($i = 0; $i < 6; $i++)
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 {{ $block }}"></div>
                        <div class="flex-1 space-y-2">
                            <div class="{{ $barSm }} w-2/3"></div>
                            <div class="{{ $barSm }} w-1/3"></div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="card p-5 space-y-4">
                <div class="{{ $bar }} w-36"></div>
                @for($i = 0; $i < 4; $i++)
                    <div class="{{ $barSm }} w-full"></div>
                @endfor
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 space-y-2">
                    <div class="{{ $barSm }} w-3/4"></div>
                    <div class="{{ $barSm }} w-1/2"></div>
                </div>
            </div>
        </div>
    @elseif($variant === 'table')
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                <div class="{{ $bar }} w-40"></div>
                <div class="{{ $bar }} w-24"></div>
            </div>
            <div class="px-5 py-2">
                @for($i = 0; $i < $rows; $i++)
                    <div class="flex items-center gap-4 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                        <div class="w-10 h-10 {{ $block }} flex-shrink-0"></div>
                        @for($c = 1; $c <= $cols; $c++)
                            <div class="{{ $barSm }} {{ $c === $cols ? 'w-16' : 'flex-1' }}"></div>
                        @endfor
                    </div>
                @endfor
            </div>
        </div>
    @elseif($variant === 'cards')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @for($i = 0; $i < $count; $i++)
                <div class="card p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="{{ $bar }} w-24"></div>
                        <div class="w-8 h-8 {{ $block }}"></div>
                    </div>
                    <div class="{{ $barSm }} w-full"></div>
                    <div class="{{ $barSm }} w-3/4"></div>
                </div>
            @endfor
        </div>
    @else
        <div class="card p-6 space-y-5 max-w-2xl">
            @for($i = 0; $i < 4; $i++)
                <div class="space-y-2">
                    <div class="{{ $bar }} w-28"></div>
                    <div class="h-10 {{ $block }}"></div>
                </div>
            @endfor
            <div class="flex items-center gap-3 pt-2">
                <div class="h-10 {{ $block }} w-28"></div>
                <div class="h-10 {{ $block }} w-24"></div>
            </div>
        </div>
    @endif
</div>
