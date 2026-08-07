@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $userRole = auth()->user()->role->slug ?? 'user';
@endphp

<aside class="w-64 bg-white border-r border-gray-200 flex flex-col h-screen overflow-y-auto">
    <!-- Brand -->
    <div class="flex items-center h-16 px-6 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-lg mr-3">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <span class="text-lg font-semibold text-gray-900">MITO Ticketing System</span>
    </div>

    <!-- Menu -->
    <nav class="flex-1 py-4 px-3">
        <ul class="space-y-1">
            {{-- ============================================ --}}
            {{-- Dashboard (All roles) --}}
            {{-- ============================================ --}}
            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'dashboard' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a4 4 0 018 0v2H8V5z"/>
                    </svg>
                    Dashboard
                </a>
            </li>

            {{-- ============================================ --}}
            {{-- Tickets Section --}}
            {{-- ============================================ --}}
            <li class="pt-3">
                <div class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tickets</div>
            </li>

            {{-- Admin/Manager: All Tickets --}}
            @if(in_array($userRole, ['admin', 'manager']))
                <li>
                    <a href="{{ route('tickets.all') }}"
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ in_array($currentRoute, ['tickets.all']) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        All Tickets
                    </a>
                </li>
            @endif

            {{-- Staff: Assigned Tickets --}}
            @if($userRole === 'staff')
                <li>
                    <a href="{{ route('tickets.assigned') }}"
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'tickets.assigned' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Assigned to Me
                    </a>
                </li>
            @endif

            {{-- All roles: My Tickets --}}
            <li>
                <a href="{{ route('tickets.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ in_array($currentRoute, ['tickets.index', 'tickets.show']) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 2l2-2-2-2"/>
                    </svg>
                    My Tickets
                </a>
            </li>

            {{-- All roles: Create Ticket --}}
            <li>
                <a href="{{ route('tickets.create') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'tickets.create' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Ticket
                </a>
            </li>

            {{-- ============================================ --}}
            {{-- Administration (Admin only) --}}
            {{-- ============================================ --}}
            @if($userRole === 'admin')
                <li class="pt-3">
                    <div class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</div>
                </li>
                <li>
                    <a href="{{ route('users.index') }}"
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ in_array($currentRoute, ['users.index', 'users.create', 'users.edit', 'users.show']) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Users
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}"
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'categories.index' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M4 7h.01M4 11h.01M4 15h.01M4 19h.01"/>
                        </svg>
                        Categories
                    </a>
                </li>
                <li>
                    <a href="{{ route('sla-policies.index') }}"
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'sla-policies.index' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        SLA Policies
                    </a>
                </li>
            @endif

            {{-- ============================================ --}}
            {{-- Manager: Categories (view only) --}}
            {{-- ============================================ --}}
            @if($userRole === 'manager')
                <li class="pt-3">
                    <div class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management</div>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}"
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'categories.index' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M4 7h.01M4 11h.01M4 15h.01M4 19h.01"/>
                        </svg>
                        Categories
                    </a>
                </li>
            @endif

            {{-- ============================================ --}}
            {{-- Settings (All roles) --}}
            {{-- ============================================ --}}
            <li class="pt-3">
                <div class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Account</div>
            </li>
            <li>
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'profile.edit' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile
                </a>
            </li>
            <li>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === 'settings.index' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.133 12 6.027l1.675-1.894a2.5 2.5 0 013.85 2.122v4.242a2.5 2.5 0 01-.735 1.767l-1.94 1.94a2.5 2.5 0 01-1.767.735h-2.242a2.5 2.5 0 01-1.767-.735l-1.94-1.94a2.5 2.5 0 01-.735-1.767V6.257a2.5 2.5 0 013.85-2.122z"/>
                    </svg>
                    Settings
                </a>
            </li>

            {{-- ============================================ --}}
            {{-- Logout --}}
            {{-- ============================================ --}}
            <li class="pt-3">
                <div class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Session</div>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-700 transition-colors">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Footer: User info -->
    <div class="flex-shrink-0 border-t border-gray-200 p-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-400 capitalize">{{ auth()->user()->role->name ?? 'User' }}</div>
            </div>
        </div>
    </div>
</aside>