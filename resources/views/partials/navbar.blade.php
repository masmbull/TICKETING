<nav class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6">
    <!-- Left: Hamburger -->
    <div class="flex items-center">
        <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg p-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span class="sr-only">Toggle sidebar</span>
        </button>
    </div>

    <!-- Right: User Info -->
    <div class="flex items-center space-x-4">
        <!-- Profile Icon -->
        <div class="flex items-center justify-center w-9 h-9 bg-blue-100 text-blue-600 rounded-full">
            <span class="text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
        </div>

        <!-- User Name -->
        <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</nav>