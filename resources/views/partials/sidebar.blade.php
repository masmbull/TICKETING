@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

<aside class="w-64 bg-white border-r border-gray-200 flex flex-col h-screen overflow-y-auto">
    <!-- Brand -->
    <div class="flex items-center h-16 px-6 border-b border-gray-200">
        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-lg mr-3">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <span class="text-lg font-semibold text-gray-900">MITO IT Helpdesk</span>
    </div>

    <!-- Menu -->
    <nav class="flex-1 py-4">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-6 py-2.5 text-sm font-medium rounded-r-lg transition-colors {{ $currentRoute === 'dashboard' ? 'bg-blue-50 text-blue-700 border-l-3 border-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a4 4 0 018 0v2H8V5z"/>
                    </svg>
                    Dashboard
                </a>
            </li>

            <!-- Divider -->
            <li class="px-6 my-2">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- Ticket Management -->
            <li class="px-6 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Ticket Management
            </li>
            <li>
                <a href="{{ route('tickets.index') }}"
                   class="flex items-center px-6 py-2.5 text-sm font-medium rounded-r-lg transition-colors {{ $currentRoute === 'tickets.index' ? 'bg-blue-50 text-blue-700 border-l-3 border-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 2l2-2-2-2"/>
                    </svg>
                    My Tickets
                </a>
            </li>
            <li>
                <a href="{{ route('tickets.create') }}"
                   class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Ticket
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h9.5a2.5 2.5 0 012.5 2.5v2.5"/>
                    </svg>
                    All Tickets
                </a>
            </li>

            <!-- Divider -->
            <li class="px-6 my-2">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- Administration -->
            <li class="px-6 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Administration
            </li>
            <li>
                <a href="#" class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354A9.93 9.93 0 002.5 12c0 1.87.46 3.64 1.28 5.18l-.01-.02L8 17l3 3 4-3 4 3 4-3 4 3 4-3"/>
                    </svg>
                    Users
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2"/>
                    </svg>
                    Departments
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M4 7h.01M4 11h.01M4 15h.01M4 19h.01"/>
                    </svg>
                    Categories
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    SLA
                </a>
            </li>

            <!-- Divider -->
            <li class="px-6 my-2">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- Reports -->
            <li>
                <a href="#" class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.1.9 2 2 2v0h10c1.1 0 2-.9 2-2V6l-2-1"/>
                    </svg>
                    Reports
                </a>
            </li>

            <!-- Settings -->
            <li>
                <a href="#" class="flex items-center px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.133 12 6.027l1.675-1.894a2.5 2.5 0 013.85 2.122v4.242a2.5 2.5 0 01-.735 1.767l-1.94 1.94a2.5 2.5 0 01-1.767.735h-2.242a2.5 2.5 0 01-1.767-.735l-1.94-1.94a2.5 2.5 0 01-.735-1.767V6.257a2.5 2.5 0 013.85-2.122z"/>
                    </svg>
                    Settings
                </a>
            </li>

            <!-- Divider -->
            <li class="px-6 my-2">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- Logout -->
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-6 py-2.5 text-sm font-medium text-gray-700 rounded-r-lg hover:bg-red-50 hover:text-red-700 transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</aside>
</arg_value>
<task_progress>
- [x] Analyze current auth setup
- [x] Create AuthController
- [x] Create DashboardController
- [x] Create routes
- [x] Create login page view
- [x] Create dashboard view
- [x] Create admin user seeder
- [x] Run seeder
- [x] Verify admin user created
- [x] Test login flow (302 to /dashboard)
- [x] Test authenticated dashboard access
- [x] Test logout (302 to /login)
- [x] Verify session destroyed after logout
- [x] Commit and push TASK-001
- [x] Create layout system (TASK-002)
- [ ] Create partials (navbar, sidebar, footer)
- [ ] Create new dashboard view
- [ ] Update routes and controller
- [ ] Test layout system
- [ ] Commit and push TASK-002
</task_progress>
</write_to_file>