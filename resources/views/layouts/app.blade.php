<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') !== 'light') { document.documentElement.classList.add('dark'); }
    </script>
    @stack('styles')
</head>
<body class="bg-slate-100 dark:bg-slate-900 min-h-screen font-['IBM_Plex_Sans'] text-slate-800 dark:text-slate-200"
      x-data="{
        sidebarExpanded: localStorage.getItem('sidebarExpanded') === '1',
        mobileMenuOpen: false,
        notifOpen: false,
        userMenuOpen: false,
        darkMode: localStorage.getItem('theme') !== 'light',
        appReady: false,
      }"
      x-init="
        $watch('sidebarExpanded', val => localStorage.setItem('sidebarExpanded', val ? '1' : '0'));
        $watch('mobileMenuOpen', val => { document.body.style.overflow = val ? 'hidden' : ''; });
        $watch('darkMode', val => {
            localStorage.setItem('theme', val ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', val);
        });
        document.documentElement.classList.toggle('dark', darkMode);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') { mobileMenuOpen = false; notifOpen = false; userMenuOpen = false; } });
        setTimeout(() => appReady = true, 800);
      ">
    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    {{-- MITO loading screen: professional branded splash shown until app ready --}}
    <div x-show="!appReady" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] bg-slate-50 dark:bg-slate-950 flex items-center justify-center">
        <div class="flex flex-col items-center gap-6">
            <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[#E30613] to-[#c4050f] text-white font-bold text-2xl shadow-lg shadow-red-500/30">
                <span>M</span>
                <span class="absolute -inset-1 rounded-full bg-[#E30613]/20 animate-ping"></span>
            </div>
            <div class="text-center">
                <div class="font-bold text-slate-900 dark:text-white text-xl tracking-tight">MITO</div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest">IT HELPDESK</div>
            </div>
            <div class="w-5 h-5 border-2 border-[#E30613]/20 border-t-[#E30613] rounded-full animate-spin"></div>
        </div>
    </div>

    {{-- Main content fades in together with the splash hiding --}}
    <div x-show="appReady" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
    <div class="min-h-screen flex">
        {{-- SIDEBAR --}}
        <aside x-cloak
               class="fixed inset-y-0 left-0 z-50 flex flex-col bg-[#0f172a] dark:bg-slate-950 transition-all duration-300 ease-out"
               :class="mobileMenuOpen ? 'w-72 translate-x-0' : (sidebarExpanded ? 'w-64 -translate-x-full lg:translate-x-0' : 'w-20 -translate-x-full lg:translate-x-0')">
            {{-- Logo Area --}}
            <div class="h-16 flex items-center px-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#E30613] to-[#c4050f] flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-red-500/20 flex-shrink-0">
                        M
                    </div>
                    <div x-show="sidebarExpanded || mobileMenuOpen" x-transition class="overflow-hidden">
                        <div class="font-bold text-white text-lg tracking-tight">MITO</div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">IT Helpdesk</div>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @php
                    $user = auth()->user();
                    $role = $user->role?->slug ?? 'user';

                    $baseNav = [
                        'link' => [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z'],
                        ],
                        'section' => [
                            [
                                'label' => 'Tickets',
                                'items' => [
                                    ['label' => 'All Tickets', 'route' => 'tickets.all', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                                    ['label' => 'My Tickets', 'route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                    ['label' => 'Create Ticket', 'route' => 'tickets.create', 'icon' => 'M12 4v16m8-8H4'],
                                ],
                            ],
                            [
                                'label' => 'Management',
                                'items' => [
                                    ['label' => 'Users', 'route' => 'users.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                                    ['label' => 'Categories', 'route' => 'categories.index', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                                    ['label' => 'SLA Policies', 'route' => 'sla-policies.index', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                                ],
                            ],
                        ],
                    ];

                    // Admin gets everything; manager/staff/user get subsets.
                    if ($role === 'admin') {
                        $nav = $baseNav;
                    } elseif ($role === 'manager') {
                        $nav = [
                            'link' => $baseNav['link'],
                            'section' => [
                                ['label' => 'Tickets', 'items' => [
                                    ['label' => 'All Tickets', 'route' => 'tickets.all', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                                    ['label' => 'My Tickets', 'route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                    ['label' => 'Create Ticket', 'route' => 'tickets.create', 'icon' => 'M12 4v16m8-8H4'],
                                ]],
                                ['label' => 'Management', 'items' => [
                                    ['label' => 'Categories', 'route' => 'categories.index', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                                    ['label' => 'SLA Policies', 'route' => 'sla-policies.index', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                                ]],
                            ],
                        ];
                    } elseif ($role === 'staff') {
                        $nav = [
                            'link' => $baseNav['link'],
                            'section' => [
                                ['label' => 'Tickets', 'items' => [
                                    ['label' => 'Assigned', 'route' => 'tickets.assigned', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                    ['label' => 'My Tickets', 'route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                ]],
                            ],
                        ];
                    } else { // user
                        $nav = [
                            'link' => $baseNav['link'],
                            'section' => [
                                ['label' => 'Tickets', 'items' => [
                                    ['label' => 'My Tickets', 'route' => 'tickets.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                    ['label' => 'Create Ticket', 'route' => 'tickets.create', 'icon' => 'M12 4v16m8-8H4'],
                                ]],
                            ],
                        ];
                    }
                @endphp

                {{-- Render top-level links --}}
                @foreach($nav['link'] as $link)
                    @php $active = request()->routeIs($link['route']); @endphp
                    <a href="{{ route($link['route']) }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ $active ? 'bg-[#E30613]/10 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $link['icon'] }}"/></svg>
                        <span x-show="sidebarExpanded || mobileMenuOpen" x-transition class="text-sm font-medium whitespace-nowrap">{{ $link['label'] }}</span>
                    </a>
                @endforeach

                {{-- Render sections --}}
                @foreach($nav['section'] as $section)
                    <div class="pt-2">
                        <div x-show="sidebarExpanded || mobileMenuOpen" x-transition class="px-3 mb-2">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ $section['label'] }}</span>
                        </div>
                        @foreach($section['items'] as $item)
                            @php $active = request()->routeIs($item['route']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ $active ? 'bg-[#E30613]/10 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                                <span x-show="sidebarExpanded || mobileMenuOpen" x-transition class="text-sm font-medium whitespace-nowrap">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            {{-- Sidebar toggle button --}}
            <button @click="sidebarExpanded = !sidebarExpanded"
                    class="hidden lg:flex items-center justify-center w-6 h-6 rounded-full bg-slate-700 hover:bg-slate-600 transition-colors absolute -right-3 top-20 border border-slate-600 shadow-lg z-50"
                    aria-label="Toggle sidebar">
                <svg class="w-3 h-3 text-slate-300 transition-transform duration-200"
                     :class="sidebarExpanded ? 'rotate-0' : 'rotate-180'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 lg:ml-64"
             :class="sidebarExpanded ? 'lg:ml-64' : 'lg:ml-20'">
            {{-- Top Navbar --}}
            <header class="sticky top-0 z-40 h-14 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800 flex items-center px-4 gap-4">
                <button @click="mobileMenuOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="flex-1 min-w-0">
                    @php
                        $routeName = request()->route()?->getName() ?? '';
                        $titles = [
                            'dashboard' => 'Dashboard',
                            'tickets.index' => 'My Tickets',
                            'tickets.all' => 'All Tickets',
                            'tickets.assigned' => 'Assigned to Me',
                            'tickets.create' => 'Create Ticket',
                            'tickets.show' => 'Ticket Details',
                            'users.index' => 'User Management',
                            'users.create' => 'Create User',
                            'users.edit' => 'Edit User',
                            'users.show' => 'User Profile',
                            'categories.index' => 'Categories',
                            'sla-policies.index' => 'SLA Policies',
                            'settings.index' => 'Settings',
                            'profile.edit' => 'My Profile',
                            'audit.index' => 'Audit Logs',
                        ];
                    @endphp
                    <h1 class="text-base font-semibold text-slate-900 dark:text-white truncate">{{ $titles[$routeName] ?? 'MITO IT Helpdesk' }}</h1>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="darkMode = !darkMode" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Toggle theme">
                        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>

                    <div class="relative">
                        <button @click="notifOpen = !notifOpen" class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-[#E30613] rounded-full"></span>
                        </button>
                    </div>

                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#E30613] to-[#c4050f] flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <svg class="w-4 h-4 text-slate-400" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50" style="display: none;">
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700">
                                <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                            </div>
                            <div class="p-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    My Profile
                                </a>
                                @if(in_array($user->role?->slug, ['admin', 'manager']))
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                    Settings
                                </a>
                                @endif
                            </div>
                            <div class="border-t border-slate-100 dark:border-slate-700 p-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" class="flex-1 p-4 lg:p-6">
                @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="mb-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</span>
                    <button @click="show = false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="mb-4 flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</span>
                    <button @click="show = false" class="ml-auto text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif

                @yield('content')
            </main>

            <footer class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span>&copy; {{ date('Y') }} MITO IT Helpdesk</span>
                    <span class="font-mono">v1.0.0</span>
                </div>
            </footer>
         </div>
    </div>

    {{-- Close appReady wrapper --}}
    </div>

    <div x-show="mobileMenuOpen" x-cloak x-transition @click="mobileMenuOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"></div>

    @stack('scripts')
</body>
</html>
