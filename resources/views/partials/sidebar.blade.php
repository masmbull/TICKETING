@php
    $user = auth()->user();
    $role = $user->role?->slug ?? 'user';
    $routes = [
        'admin' => [
            'Dashboard' => ['route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z'],
            'Tickets' => [
                'All Tickets' => ['route' => 'tickets.all', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                'My Tickets' => ['route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                'Create Ticket' => ['route' => 'tickets.create', 'icon' => 'M12 4v16m8-8H4'],
            ],
            'Management' => [
                'Users' => ['route' => 'users.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                'Categories' => ['route' => 'categories.index', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                'SLA Policies' => ['route' => 'sla-policies.index', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                'Settings' => ['route' => 'settings.index', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ],
        ],
        'manager' => [
            'Dashboard' => ['route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z'],
            'Tickets' => [
                'All Tickets' => ['route' => 'tickets.all', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                'My Tickets' => ['route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                'Create Ticket' => ['route' => 'tickets.create', 'icon' => 'M12 4v16m8-8H4'],
            ],
            'Management' => [
                'Categories' => ['route' => 'categories.index', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                'SLA Policies' => ['route' => 'sla-policies.index', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                'Settings' => ['route' => 'settings.index', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ],
        ],
        'staff' => [
            'Dashboard' => ['route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z'],
            'Tickets' => [
                'Assigned To Me' => ['route' => 'tickets.assigned', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                'My Tickets' => ['route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ],
        ],
        'user' => [
            'Dashboard' => ['route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z'],
            'Tickets' => [
                'My Tickets' => ['route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                'Create Ticket' => ['route' => 'tickets.create', 'icon' => 'M12 4v16m8-8H4'],
            ],
        ],
    ];
    $menu = $routes[$role] ?? $routes['user'];
@endphp

<aside x-data="{ open: true }" class="fixed top-0 left-0 z-40 h-screen bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-200" :class="open ? 'w-[240px]' : 'w-[72px]'">
    <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">M</div>
            <span x-show="open" class="text-sm font-semibold text-slate-900 dark:text-white whitespace-nowrap">MITO Helpdesk</span>
        </a>
    </div>

    <button @click="open = !open" class="absolute -right-3 top-[72px] w-6 h-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full flex items-center justify-center shadow-sm hover:shadow transition-all duration-200">
        <svg class="w-3 h-3 text-slate-500 dark:text-slate-400 transition-transform duration-200" :class="open ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>

    <nav class="h-[calc(100vh-4rem)] overflow-y-auto px-3 py-4 space-y-1">
        @foreach($menu as $label => $item)
            @if(is_array($item) && isset($item['route']))
                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all {{ request()->routeIs($item['route']) ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium' : '' }}" title="{{ $label }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/></svg>
                    <span x-show="open" x-transition.opacity.duration.200ms class="whitespace-nowrap">{{ $label }}</span>
                </a>
            @elseif(is_array($item))
                <div x-data="{ expanded: {{ request()->routeIs(array_column($item, 'route')) ? 'true' : 'false' }} }">
                    <button @click="expanded = !expanded" class="flex items-center justify-between w-full px-3 py-2 rounded-lg text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <span class="flex items-center gap-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $label }}</span>
                        </span>
                        <svg x-show="open" class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-0' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="expanded || !open" x-collapse x-cloak class="ml-2 space-y-0.5 border-l-2 border-slate-200 dark:border-slate-700">
                        @foreach($item as $subLabel => $subItem)
                            <a href="{{ route($subItem['route']) }}" class="flex items-center gap-3 pl-6 pr-3 py-1.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all rounded-lg {{ request()->routeIs($subItem['route']) ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium' : '' }}" title="{{ $subLabel }}">
                                <svg class="w-[16px] h-[16px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $subItem['icon'] }}"/></svg>
                                <span x-show="open" x-transition.opacity.duration.200ms class="whitespace-nowrap">{{ $subLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-slate-200 dark:border-slate-800">
        <div x-show="open" x-transition.opacity.duration.200ms class="text-xs text-slate-400 dark:text-slate-500">
            <div>Version 1.0.0</div>
            <div>© MITO IT Helpdesk</div>
        </div>
    </div>
</aside>