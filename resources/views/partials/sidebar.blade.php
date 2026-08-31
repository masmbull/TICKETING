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
                'Security Insights' => ['route' => 'security-insights.index', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
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

<aside x-cloak
       class="fixed inset-y-0 left-0 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-[width,transform] duration-300 ease-in-out"
       :class="mobileSidebar ? 'w-[280px] translate-x-0 z-[70]' : (sidebarOpen ? 'w-[240px] -translate-x-full lg:translate-x-0' : 'w-[72px] -translate-x-full lg:translate-x-0')">

    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-800 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm shadow-blue-500/30">M</div>
            <span x-show="sidebarOpen || mobileSidebar" x-transition.opacity.duration.150 class="text-sm font-bold text-slate-900 dark:text-white whitespace-nowrap tracking-tight">MITO Helpdesk</span>
        </a>
    </div>

    {{-- Desktop collapse toggle --}}
    <button @click="sidebarOpen = !sidebarOpen"
            class="hidden lg:flex absolute -right-3 top-[68px] z-10 w-6 h-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full items-center justify-center shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-500/50 transition-all duration-200"
            :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'"
            aria-label="Toggle sidebar">
        <svg class="w-3 h-3 text-slate-500 dark:text-slate-400 transition-transform duration-300" :class="sidebarOpen ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>

    <nav @click="mobileSidebar = false" class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 sidebar-scroll space-y-1">

        @foreach($menu as $label => $item)
            @if(is_array($item) && isset($item['route']))
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   :title="sidebarOpen || mobileSidebar ? '' : '{{ $label }}'"
                   :class="sidebarOpen || mobileSidebar ? '' : 'justify-center'"
                   class="sidebar-item {{ $active ? 'sidebar-item-active' : '' }}">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/></svg>
                    <span x-show="sidebarOpen || mobileSidebar" x-transition.opacity.duration.150 class="sidebar-label whitespace-nowrap">{{ $label }}</span>
                </a>
            @elseif(is_array($item))
                @php $groupActive = request()->routeIs(array_column($item, 'route')); @endphp

                {{-- Expanded view: dropdown group --}}
                <div x-show="sidebarOpen || mobileSidebar" x-data="{ expanded: {{ $groupActive ? 'true' : 'false' }} }" x-transition.opacity.duration.150>
                    <button @click="expanded = !expanded" class="sidebar-group {{ $groupActive ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $label }}</span>
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-0' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="expanded" x-collapse x-cloak class="ml-3 mt-0.5 space-y-0.5 border-l-2 border-slate-200 dark:border-slate-700">
                        @foreach($item as $subLabel => $subItem)
                            @php $subActive = request()->routeIs($subItem['route']); @endphp
                            <a href="{{ route($subItem['route']) }}" class="sidebar-item {{ $subActive ? 'sidebar-item-active' : '' }} pl-3">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $subItem['icon'] }}"/></svg>
                                <span class="sidebar-label whitespace-nowrap">{{ $subLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Collapsed view: icon-only sub-items --}}
                <div x-show="!sidebarOpen && !mobileSidebar" x-transition.opacity.duration.150 class="space-y-1">
                    @foreach($item as $subLabel => $subItem)
                        @php $subActive = request()->routeIs($subItem['route']); @endphp
                        <a href="{{ route($subItem['route']) }}" :title="'{{ $subLabel }}'" class="sidebar-item justify-center {{ $subActive ? 'sidebar-item-active' : '' }}">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $subItem['icon'] }}"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    {{-- Footer --}}
    <div class="flex-shrink-0 border-t border-slate-200 dark:border-slate-800 px-4 py-3.5">
        <div x-show="sidebarOpen || mobileSidebar" x-transition.opacity.duration.150 class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
            <div class="font-semibold text-slate-500 dark:text-slate-400">Version 1.0.0</div>
            <div>&copy; MITO IT Helpdesk</div>
        </div>
        <div x-show="!sidebarOpen && !mobileSidebar" x-transition.opacity.duration.150 class="text-center" :title="'Version 1.0.0'">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">v1.0</span>
        </div>
    </div>
</aside>

