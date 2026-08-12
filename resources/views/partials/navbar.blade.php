@php
    $routeName = request()->route()?->getName() ?? '';
    $user = auth()->user();
    $navMap = [
        'dashboard'          => ['title' => 'Dashboard',       'crumbs' => []],
        'tickets.index'      => ['title' => 'My Tickets',      'crumbs' => [['Tickets', null]]],
        'tickets.all'        => ['title' => 'All Tickets',     'crumbs' => [['Tickets', null]]],
        'tickets.assigned'   => ['title' => 'Assigned to Me',  'crumbs' => [['Tickets', null]]],
        'tickets.create'     => ['title' => 'Create Ticket',   'crumbs' => [['Tickets', 'tickets.index']]],
        'tickets.show'       => ['title' => isset($ticket) ? $ticket->ticket_number : 'Ticket Detail', 'crumbs' => [['Tickets', 'tickets.index']]],
        'users.index'        => ['title' => 'User Management', 'crumbs' => []],
        'users.create'       => ['title' => 'Create User',     'crumbs' => [['Users', 'users.index']]],
        'users.edit'         => ['title' => 'Edit User',       'crumbs' => [['Users', 'users.index']]],
        'users.show'         => ['title' => 'User Profile',    'crumbs' => [['Users', 'users.index']]],
        'categories.index'   => ['title' => 'Categories',      'crumbs' => []],
        'sla-policies.index' => ['title' => 'SLA Policies',    'crumbs' => [['Settings', 'settings.index']]],
        'settings.index'     => ['title' => 'Settings',        'crumbs' => []],
        'profile.edit'       => ['title' => 'My Profile',      'crumbs' => []],
    ];
    $nav = $navMap[$routeName] ?? ['title' => 'MITO IT Helpdesk', 'crumbs' => []];
    $roleSlug = $user->role?->slug ?? 'user';
    // Global search lands on the most relevant ticket list for the role.
    $searchAction = in_array($roleSlug, ['admin', 'manager'])
        ? route('tickets.all')
        : (in_array($roleSlug, ['staff']) ? route('tickets.assigned') : route('tickets.index'));
@endphp

<header class="sticky top-0 z-30 h-16 bg-white/85 dark:bg-slate-900/85 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 flex items-center gap-2 sm:gap-3 px-4 sm:px-6">
    <button @click="mobileSidebar = true" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Open menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <div class="min-w-0 hidden md:block">
        @if(count($nav['crumbs']))
        <nav class="hidden sm:flex items-center gap-1.5 text-[11px] font-medium text-slate-400 dark:text-slate-500 leading-none mb-0.5" aria-label="Breadcrumb">
            @foreach($nav['crumbs'] as [$crumbLabel, $crumbRoute])
                @if($crumbRoute)
                    <a href="{{ route($crumbRoute) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $crumbLabel }}</a>
                @else
                    <span>{{ $crumbLabel }}</span>
                @endif
                @if(!$loop->last)
                    <svg class="w-3 h-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @endif
            @endforeach
        </nav>
        @endif
        <h1 class="text-[15px] sm:text-base font-bold text-slate-900 dark:text-white tracking-tight truncate">{{ $nav['title'] }}</h1>
    </div>

    <div class="flex-1 flex justify-center min-w-0 px-2">
        <form action="{{ $searchAction }}" method="GET" class="relative w-full flex items-center"
              x-data="{
                q: @json(request('search') ?? ''),
                open: false,
                loading: false,
                results: { tickets: [], users: [], categories: [] },
                doSearch() {
                    const t = this.q.trim();
                    if (t.length < 2) { this.results = { tickets: [], users: [], categories: [] }; this.open = false; return; }
                    const s = this;
                    s.loading = true;
                    fetch('{{ route('api.search') }}?q=' + encodeURIComponent(t), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                        .then(d => { s.results = d; s.open = true; })
                        .catch(() => { s.results = { tickets: [], users: [], categories: [] }; })
                        .finally(() => { s.loading = false; });
                },
                hasResults() { return this.results.tickets.length + this.results.users.length + this.results.categories.length > 0; }
              }">
            <div class="relative w-full">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" x-model="q" placeholder="Search tickets, users, categories..." autocomplete="off"
                       @input.debounce.250ms="doSearch()"
                       @focus="q.trim().length >= 2 ? open = true : open = false"
                       @keydown.escape="open = false"
                       class="w-full h-10 pl-10 pr-9 text-sm bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-xl placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 md:max-w-[280px] lg:max-w-[360px]" />
                <svg x-show="loading" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>

            <div x-show="open && !loading && hasResults()" x-cloak
                 x-transition:enter="dropdown-enter" x-transition:leave="dropdown-leave"
                 @click.outside="open = false"
                 class="absolute left-0 top-12 w-full sm:w-[480px] max-w-[calc(100vw-2rem)] bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden z-50">
                <div class="max-h-96 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                    <template x-if="results.tickets.length">
                        <div class="py-1.5">
                            <div class="px-4 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tickets</div>
                            <a x-for="t in results.tickets" :key="t.id" :href="t.url" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white truncate"><span x-text="t.ticket_number"></span><span class="text-slate-400"> · </span><span x-text="t.description"></span></span>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500"><span x-text="t.status"></span><template x-if="t.assignee"><span> · <span x-text="t.assignee"></span></span></template></span>
                                </span>
                            </a>
                        </div>
                    </template>

                    <template x-if="results.users.length">
                        <div class="py-1.5">
                            <div class="px-4 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Users</div>
                            <a x-for="u in results.users" :key="u.id" :href="u.url || '#'" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <span class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-[11px] font-bold flex-shrink-0" x-text="u.name.substring(0,1).toUpperCase()"></span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white truncate" x-text="u.name"></span>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500" x-text="u.email + (u.role ? ' · ' + u.role : '')"></span>
                                </span>
                            </a>
                        </div>
                    </template>

                    <template x-if="results.categories.length">
                        <div class="py-1.5">
                            <div class="px-4 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Categories</div>
                            <a x-for="c in results.categories" :key="c.id" :href="c.url" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white truncate" x-text="c.name"></span>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500">Category</span>
                                </span>
                            </a>
                        </div>
                    </template>
                </div>
                <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-700 text-center text-xs text-slate-400 dark:text-slate-500">
                    Press <kbd class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-700 font-mono text-[10px]">Enter</kbd> to view all results
                </div>
            </div>

            <div x-show="open && !loading && !hasResults()" x-cloak
                 x-transition:enter="dropdown-enter" x-transition:leave="dropdown-leave"
                 class="absolute left-0 top-12 w-full sm:w-[480px] max-w-[calc(100vw-2rem)] bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden z-50">
                <div class="px-4 py-6 text-center text-sm text-slate-400 dark:text-slate-500">No results for "<span x-text="q" class="font-medium text-slate-600 dark:text-slate-300"></span>"</div>
            </div>
        </form>
    </div>

    <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
        <span id="live-clock" class="hidden sm:inline-flex items-center px-2.5 py-1.5 text-xs font-mono font-semibold tabular-nums text-slate-600 dark:text-slate-300 bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-lg whitespace-nowrap"></span>

        <button @click="darkMode = !darkMode" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-all duration-200" aria-label="Toggle theme">
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </button>

        <div class="relative">
            <button @click="notifOpen = !notifOpen" @keydown.escape="notifOpen = false"
                    class="relative p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-all duration-200" aria-label="Notifications">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
            </button>
            <div x-show="notifOpen" @click.away="notifOpen = false"
                 x-transition:enter="dropdown-enter" x-transition:leave="dropdown-leave"
                 class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden z-50" style="display:none;">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notifications</h3>
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 rounded-full">3 new</span>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <div class="px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer border-b border-slate-50 dark:border-slate-700/50">
                        <p class="text-sm text-slate-700 dark:text-slate-300">New ticket assigned: <span class="font-semibold">MIT-260810-000001</span></p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">2 minutes ago</p>
                    </div>
                    <div class="px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer border-b border-slate-50 dark:border-slate-700/50">
                        <p class="text-sm text-slate-700 dark:text-slate-300">SLA breach warning for <span class="font-semibold text-red-600 dark:text-red-400">MIT-260810-000003</span></p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">15 minutes ago</p>
                    </div>
                    <div class="px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer">
                        <p class="text-sm text-slate-700 dark:text-slate-300">Ticket resolved by <span class="font-semibold">John Doe</span></p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">1 hour ago</p>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700 text-center">
                    <button class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 transition-colors">View all notifications</button>
                </div>
            </div>
        </div>


        <div class="relative">
            <button @click="userDropdownOpen = !userDropdownOpen" @keydown.escape="userDropdownOpen = false"
                    class="flex items-center gap-2 p-1.5 pr-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300 hidden sm:block">{{ $user->name }}</span>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="userDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="userDropdownOpen" @click.away="userDropdownOpen = false"
                 x-transition:enter="dropdown-enter" x-transition:leave="dropdown-leave"
                 class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 py-1 z-50 overflow-hidden" style="display:none;">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white text-sm font-bold shadow-md">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $user->email }}</div>
                            @if($user->role)
                            <span class="inline-flex items-center mt-1.5 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">{{ $user->role->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="py-1.5">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-500/10 hover:text-blue-700 dark:hover:text-blue-400 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        My Profile
                    </a>
                    @if(in_array($user->role?->slug, ['admin', 'manager']))
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-500/10 hover:text-blue-700 dark:hover:text-blue-400 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </a>
                    @endif
                </div>
                <div class="border-t border-slate-100 dark:border-slate-700 py-1.5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full gap-3 px-5 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
(function () {
    function updateClock() {
        const now = new Date();
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const gmt7 = new Date(utc + (7 * 3600000));
        const pad = n => String(n).padStart(2, '0');
        const el = document.getElementById('live-clock');
        if (el) el.textContent = pad(gmt7.getHours()) + ':' + pad(gmt7.getMinutes()) + ':' + pad(gmt7.getSeconds()) + ' WIB';
    }
    updateClock();
    setInterval(updateClock, 1000);
})();
</script>

