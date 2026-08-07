@props([
    'type' => 'text',
    'count' => 1,
    'height' => null,
    'width' => null,
])

@if($type === 'text')
    @for($i = 0; $i < $count; $i++)
        <div class="animate-pulse space-y-2 {{ $i > 0 ? 'mt-3' : '' }}">
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded {{ $width ?? 'w-full' }}" {{ $height ? "style='height: $height'" : '' }}></div>
        </div>
    @endfor
@elseif($type === 'heading')
    <div class="animate-pulse space-y-2">
        <div class="h-6 bg-gray-200 dark:bg-slate-700 rounded w-1/3"></div>
        <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-2/3"></div>
    </div>
@elseif($type === 'avatar')
    @for($i = 0; $i < $count; $i++)
        <div class="animate-pulse {{ $i > 0 ? 'mt-3' : '' }}">
            <div class="h-10 w-10 bg-gray-200 dark:bg-slate-700 rounded-full"></div>
        </div>
    @endfor
@elseif($type === 'card')
    <div class="animate-pulse border border-gray-200 dark:border-slate-700 rounded-lg p-4">
        <div class="flex items-center space-x-4 mb-4">
            <div class="h-10 w-10 bg-gray-200 dark:bg-slate-700 rounded-full"></div>
            <div class="space-y-2 flex-1">
                <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/4"></div>
                <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded w-1/3"></div>
            </div>
        </div>
        <div class="space-y-2">
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-full"></div>
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-5/6"></div>
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-4/6"></div>
        </div>
    </div>
@elseif($type === 'table')
    @for($i = 0; $i < $count; $i++)
        <div class="animate-pulse flex items-center space-x-4 py-3 border-b border-gray-100 dark:border-slate-800 {{ $i > 0 ? '' : '' }}">
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/6"></div>
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-2/6"></div>
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/6"></div>
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/6"></div>
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/6"></div>
        </div>
    @endfor
@elseif($type === 'stat')
    <div class="animate-pulse border border-gray-200 dark:border-slate-700 rounded-lg p-4">
        <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded w-1/2 mb-2"></div>
        <div class="h-8 bg-gray-200 dark:bg-slate-700 rounded w-1/3 mb-2"></div>
        <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded w-2/3"></div>
    </div>
@endif