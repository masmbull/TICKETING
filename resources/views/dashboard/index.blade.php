@extends('layouts.app')

@section('title', 'Dashboard - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <!-- Page Title -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Overview of your IT helpdesk</p>
    </div>

    <!-- Welcome Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900">Welcome back, {{ auth()->user()->name }}</h2>
        <p class="text-sm text-gray-500 mt-1">Here is your IT helpdesk overview.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Open Tickets -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Open Tickets</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">0</p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 2l2-2-2-2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">In Progress</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">0</p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 bg-amber-100 text-amber-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17h2M12 7v6l4 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Waiting User -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Waiting User</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">0</p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 bg-purple-100 text-purple-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Closed Today -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Closed Today</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">0</p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 text-green-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
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
- [x] Create partials (navbar, sidebar, footer)
- [x] Create new dashboard view
- [ ] Update routes and controller
- [ ] Test layout system
- [ ] Commit and push TASK-002
</task_progress>
</write_to_file>

</final_file_content>

IMPORTANT: For any future changes to this file, use the final_file_content shown above as your reference. This content reflects the current state of the file, including any auto-formatting (e.g., if you used single quotes but the formatter converted them to double quotes). Always base your SEARCH/REPLACE operations on this final version to ensure accuracy.


<environment_details>
# Visual Studio Code Visible Files
app/resources/views/dashboard/index.blade.php

# Visual Studio Code Open Tabs
c:/Program/Program Files/PHP/current/php.ini
c:/Users/ShohibulAnwar/composer/composer.bat
app/.env
app/.env.example
app/README.md
app/CONTRIBUTING.md
app/LICENSE.md
app/docs/database/01-requirements-analysis.md
app/docs/database/02-entity-definitions.md
app/docs/database/03-erd.md
app:/docs/database/04-data-dictionary.md
app:/docs/database/05-indexes-and-foreign-keys.md
app:/docs/database/06-enum-values.md
app:/docs/database/07-audit-strategy.md
app:/docs/database/README.md
app:/docs/database/08-mvp-database-summary.md
app:/app/Http/Controllers/AuthController.php
app:/app/Http/Controllers/DashboardController.php
app:/routes/web.php
app:/resources/views/auth/login.blade.php
app:/resources/views/dashboard.blade.php
app:/database/seeders/DatabaseSeeder.php
app:/resources/views/layouts/app.blade.php
app:/resources/views/partials/sidebar.blade.php
app:/app:/resources/views/partials/navbar.blade.php
app:/resources/views/partials/footer.blade.php
app:/resources/views/dashboard/index.blade.php

# Actively Running Terminals
## Original Command: `cd app; php artisan serve --port=8000`
### New Output
  2026-08-06 14:56:25 /login .................................................................................................................................................. ~ 501.10ms
  2026-08-06 14:56:26 /favicon.ico .............................................................................................................................................. ~ 0.14ms
  2026-08-06 14:56:46 /login .................................................................................................................................................. ~ 504.87ms
  20-08-06 14:56:47 /dashboard ................................................................................................................................................ ~ 0.18ms
  2026-08-06 14:57:01 /logout ................................................................................................................................................... ~ 0.57ms
  2026-08-06 06 14:57:02 /login .................................................................................................................................................... ~ 0.99ms
  2026-08-06 14:57:02 /favicon.ico .............................................................................................................................................. ~ 0.22ms
  2026-08-06 14:57:05 /dashboard ................................................................................................................................................ ~ 0.15ms
  2026-08-06 14:57:05 /login .................................................................................................................................................... ~ 0.0.12ms
  2026-08-06 14:57:06 /favicon.ico .............................................................................................................................................. ~ 0.13ms

# Current Time
 8/6/2026, 3:03:01 PM (Asia/Jakarta, UTC 7)

# Current Context Window
 189,868 / 262.144K tokens tokens used (72%)

# Current Mode
ACT MODE</arg_value></tool_call>