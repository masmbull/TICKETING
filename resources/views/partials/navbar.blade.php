<header class="navbar">
    <div class="flex items-center gap-3">
        <button @click="mobileSidebar = !mobileSidebar" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-200 hover:scale-105 active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="hidden sm:flex items-center">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" placeholder="Search tickets, users..."
                       class="w-64 lg:w-80 pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl placeholder:text-slate-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:shadow-md focus:shadow-primary-500/5 focus:w-96" />
            </div>
        </div>
    </div>

    <div class="flex items-center gap-1">
        {{-- Notifications --}}
        <div class="relative">
            <button @click="notifOpen = !notifOpen" @keydown.escape="notifOpen = false"
                    class="relative p-2.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-200 hover:scale-105 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-2 right-2 w-2 h-2 bg-danger-500 rounded-full ring-2 ring-white" style="animation: pulseSoft 2s ease-in-out infinite;"></span>
            </button>

            <div x-show="notifOpen" @click.away="notifOpen = false"
                 x-transition:enter="dropdown-enter" x-transition:leave="dropdown-leave"
                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg shadow-slate-200/60 border border-slate-100 overflow-hidden z-50" style="display:none;">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
                    <span class="text-[10px] font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-full">3 new</span>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <div class="px-5 py-3.5 hover:bg-slate-50 transition-colors cursor-pointer border-b border-slate-50">
                        <p class="text-sm text-slate-700">New ticket assigned: <span class="font-semibold text-slate-900">HD-20260807-000001</span></p>
                        <p class="text-xs text-slate-400 mt-1">2 minutes ago</p>
                    </div>
                    <div class="px-5 py-3.5 hover:bg-slate-50 transition-colors cursor-pointer border-b border-slate-50">
                        <p class="text-sm text-slate-700">SLA breach warning for ticket <span class="font-semibold text-danger-600">HD-20260807-000003</span></p>
                        <p class="text-xs text-slate-400 mt-1">15 minutes ago</p>
                    </div>
                    <div class="px-5 py-3.5 hover:bg-slate-50 transition-colors cursor-pointer">
                        <p class="text-sm text-slate-700">Ticket resolved by <span class="font-semibold">John Doe</span></p>
                        <p class="text-xs text-slate-400 mt-1">1 hour ago</p>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-100 text-center">
                    <button class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors">View all notifications</button>
                </div>
            </div>
        </div>

        <div class="w-px h-6 bg-slate-200 mx-1"></div>

        {{-- User Dropdown --}}
        <div class="relative">
            <button @click="userDropdownOpen = !userDropdownOpen" @keydown.escape="userDropdownOpen = false"
                    class="flex items-center gap-2.5 p-1.5 pr-3 rounded-xl hover:bg-slate-100 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold shadow-sm ring-2 ring-white transition-all duration-200 group-hover:ring-primary-100 group-hover:shadow-md">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-slate-700 hidden sm:block">{{ auth()->user()->name }}</span>
                <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="userDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="userDropdownOpen" @click.away="userDropdownOpen = false"
                 x-transition:enter="dropdown-enter" x-transition:leave="dropdown-leave"
                 class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg shadow-slate-200/60 border border-slate-100 py-1 z-50 overflow-hidden" style="display:none;">
                <div class="px-5 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-bold shadow-md shadow-primary-500/20">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ auth()->user()->email }}</div>
                            @if(auth()->user()->role)
                            <span class="inline-flex items-center mt-1.5 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider bg-primary-50 text-primary-600">{{ auth()->user()->role->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="py-1.5">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm text-slate-600 hover:bg-primary-50 hover:text-primary-700 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        My Profile
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm text-slate-600 hover:bg-primary-50 hover:text-primary-700 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </a>
                </div>
                <div class="border-t border-slate-100 py-1.5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full gap-3 px-5 py-2.5 text-sm text-danger-600 hover:bg-danger-50 transition-all duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
