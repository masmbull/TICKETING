@php
    $user = auth()->user();
    $role = $user->role?->slug ?? 'user';
    $initials = strtoupper(substr($user->name, 0, 1));
@endphp

<aside class="sidebar z-40"
       :class="sidebarOpen ? 'w-[260px]' : 'w-[72px]'"
       x-data="{ open: true }">

    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-slate-100 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-sm transition-all duration-200 group-hover:shadow-md group-hover:scale-105 flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="overflow-hidden transition-all duration-200" :class="sidebarOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0'">
                <div class="text-sm font-bold text-slate-900 whitespace-nowrap tracking-tight">MITO</div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.15em] whitespace-nowrap">Helpdesk</div>
            </div>
        </a>
    </div>

    {{-- Toggle button --}}
    <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex absolute -right-3 top-[72px] z-50 w-6 h-6 bg-white border border-slate-200 rounded-full items-center justify-center shadow-sm hover:shadow-md hover:scale-110 active:scale-95 transition-all duration-200">
        <svg class="w-3 h-3 text-slate-500 transition-transform duration-200" :class="sidebarOpen ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 sidebar-scroll">
        {{-- ADMIN SECTION --}}
        @if($role === 'admin')
        <div x-data="{ adminOpen: open }" x-init="adminOpen = open">
            <button @click="adminOpen = !adminOpen" class="w-full flex items-center justify-between px-3 py-1.5" :class="sidebarOpen ? '' : 'justify-center'">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400" x-show="sidebarOpen" x-transition>Administration</span>
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="adminOpen ? 'rotate-0' : '-rotate-90'" x-show="sidebarOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="adminOpen || !sidebarOpen" x-collapse x-cloak class="space-y-0.5">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}" title="Dashboard">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('tickets.all') }}" class="sidebar-item {{ request()->routeIs('tickets.all') ? 'sidebar-item-active' : '' }}" title="All Tickets">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">All Tickets</span>
                </a>
                <a href="{{ route('users.index') }}" class="sidebar-item {{ request()->routeIs('users.*') ? 'sidebar-item-active' : '' }}" title="Users">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Users</span>
                </a>
                <a href="{{ route('categories.index') }}" class="sidebar-item {{ request()->routeIs('categories.*') || request()->routeIs('subcategories.*') ? 'sidebar-item-active' : '' }}" title="Categories">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Categories</span>
                </a>
                <a href="{{ route('sla-policies.index') }}" class="sidebar-item {{ request()->routeIs('sla-policies.*') ? 'sidebar-item-active' : '' }}" title="SLA Policies">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">SLA Policies</span>
                </a>
            </div>
        </div>
        @endif

        {{-- MANAGER SECTION --}}
        @if($role === 'manager')
        <div x-data="{ managerOpen: open }" x-init="managerOpen = open">
            <button @click="managerOpen = !managerOpen" class="w-full flex items-center justify-between px-3 py-1.5" :class="sidebarOpen ? '' : 'justify-center'">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400" x-show="sidebarOpen" x-transition>Support</span>
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="managerOpen ? 'rotate-0' : '-rotate-90'" x-show="sidebarOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="managerOpen || !sidebarOpen" x-collapse x-cloak class="space-y-0.5">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}" title="Dashboard">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('tickets.all') }}" class="sidebar-item {{ request()->routeIs('tickets.all') ? 'sidebar-item-active' : '' }}" title="All Tickets">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">All Tickets</span>
                </a>
                <a href="{{ route('categories.index') }}" class="sidebar-item {{ request()->routeIs('categories.*') || request()->routeIs('subcategories.*') ? 'sidebar-item-active' : '' }}" title="Categories">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Categories</span>
                </a>
            </div>
        </div>
        @endif

        {{-- STAFF SECTION --}}
        @if($role === 'staff')
        <div x-data="{ staffOpen: open }" x-init="staffOpen = open">
            <button @click="staffOpen = !staffOpen" class="w-full flex items-center justify-between px-3 py-1.5" :class="sidebarOpen ? '' : 'justify-center'">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400" x-show="sidebarOpen" x-transition>Support</span>
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="staffOpen ? 'rotate-0' : '-rotate-90'" x-show="sidebarOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="staffOpen || !sidebarOpen" x-collapse x-cloak class="space-y-0.5">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}" title="Dashboard">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('tickets.assigned') }}" class="sidebar-item {{ request()->routeIs('tickets.assigned') ? 'sidebar-item-active' : '' }}" title="Assigned to Me">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Assigned to Me</span>
                </a>
                <a href="{{ route('tickets.create') }}" class="sidebar-item {{ request()->routeIs('tickets.create') ? 'sidebar-item-active' : '' }}" title="Create Ticket">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Create Ticket</span>
                </a>
            </div>
        </div>
        @endif

        {{-- USER SECTION --}}
        @if($role === 'user')
        <div x-data="{ userOpen: open }" x-init="userOpen = open">
            <button @click="userOpen = !userOpen" class="w-full flex items-center justify-between px-3 py-1.5" :class="sidebarOpen ? '' : 'justify-center'">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400" x-show="sidebarOpen" x-transition>Tickets</span>
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="userOpen ? 'rotate-0' : '-rotate-90'" x-show="sidebarOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="userOpen || !sidebarOpen" x-collapse x-cloak class="space-y-0.5">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}" title="Dashboard">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="sidebar-item {{ request()->routeIs('tickets.index') && !request()->query('assignee') ? 'sidebar-item-active' : '' }}" title="My Tickets">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">My Tickets</span>
                </a>
                <a href="{{ route('tickets.create') }}" class="sidebar-item {{ request()->routeIs('tickets.create') ? 'sidebar-item-active' : '' }}" title="Create Ticket">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Create Ticket</span>
                </a>
            </div>
        </div>
        @endif

        {{-- MANAGEMENT SECTION (Admin + Manager) --}}
        @if(in_array($role, ['admin', 'manager']))
        <div x-data="{ mgmtOpen: false }" class="mt-4 pt-4 border-t border-slate-100">
            <button @click="mgmtOpen = !mgmtOpen" class="w-full flex items-center justify-between px-3 py-1.5" :class="sidebarOpen ? '' : 'justify-center'">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400" x-show="sidebarOpen" x-transition>Management</span>
                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="mgmtOpen ? 'rotate-0' : '-rotate-90'" x-show="sidebarOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="mgmtOpen || !sidebarOpen" x-collapse x-cloak class="space-y-0.5">
                <a href="{{ route('categories.index') }}" class="sidebar-item {{ request()->routeIs('categories.*') || request()->routeIs('subcategories.*') ? 'sidebar-item-active' : '' }}" title="Categories">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Categories</span>
                </a>
                @if($role === 'admin')
                <a href="{{ route('sla-policies.index') }}" class="sidebar-item {{ request()->routeIs('sla-policies.*') ? 'sidebar-item-active' : '' }}" title="SLA Policies">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">SLA Policies</span>
                </a>
                @endif
                <a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings.*') ? 'sidebar-item-active' : '' }}" title="Settings">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="whitespace-nowrap">Settings</span>
                </a>
            </div>
        </div>
        @endif
    </nav>

    {{-- User Card --}}
    <div class="border-t border-slate-100 p-3 flex-shrink-0">
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" class="w-full flex items-center gap-3 rounded-xl hover:bg-slate-50 transition-all duration-200 cursor-pointer group" :class="sidebarOpen ? 'px-2 py-2' : 'justify-center px-0 py-2'">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm ring-2 ring-white transition-all duration-200 group-hover:ring-primary-100 group-hover:shadow-md">
                    {{ $initials }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0" x-show="sidebarOpen" x-transition.opacity.duration.200ms>
                    <div class="text-sm font-semibold text-slate-800 truncate">{{ $user->name }}</div>
                    <div class="text-[11px] font-medium text-slate-400 truncate">{{ $user->role->name ?? 'User' }}</div>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''" x-show="sidebarOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="open" x-transition:enter="dropdown-enter" x-transition:leave="dropdown-leave" x-cloak
                 class="absolute bottom-full left-0 mb-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg shadow-slate-200/60 overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-slate-100">
                    <div class="text-sm font-semibold text-slate-900 truncate">{{ $user->name }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                </div>
                <div class="py-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-primary-50 hover:text-primary-700 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        My Profile
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-primary-50 hover:text-primary-700 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </a>
                    <div class="border-t border-slate-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-danger-600 hover:bg-danger-50 transition-all duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
