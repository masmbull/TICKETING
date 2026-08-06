<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MITO IT Helpdesk')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            @include('partials.navbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            @include('partials.footer')
        </div>
    </div>
</body>
</html>