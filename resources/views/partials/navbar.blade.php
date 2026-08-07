@php
    $currentUser = auth()->user();
@endphp

<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 flex-shrink-0">
    <!-- Left: Mobile menu toggle + Search -->
    <div class="flex items-center gap-3 flex-1 min-w-0">
        <!-- Mobile: Hamburger -->
        <button @click="mobileSidebar = !mobileSidebar" class="lg:hidden p-1.5 text-gray-500 hover:text-gray-700 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Search Bar -->
        <form action="{{ route('tickets.all') }}" method="GET" class="hidden sm:flex items-center w-full max-w-md">
            <div class="relative w-full">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors" />
            </div>
        </form>
    </div>

    <!-- Right: User dropdown -->
    <div class="flex items-center gap-3">
        <!-- User Info -->
        <div class="hidden sm:flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                {{ strtoupper(substr($currentUser->name, 0, 2)) }}
            </div>
            <div class="text-right">
                <div class="text-sm font-medium text-gray-900">{{ $currentUser->name }}</div>
                <div class="text-xs text-gray-400 capitalize">{{ $currentUser->role->name ?? 'User' }}</div>
            </div>
        </div>
    </div>
</header>