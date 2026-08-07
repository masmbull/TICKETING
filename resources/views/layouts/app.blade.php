<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MITO IT Helpdesk')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true, mobileSidebar: false }">
        <!-- Sidebar (Desktop: fixed, Mobile: overlay) -->
        <div class="hidden lg:block" :class="{ 'lg:w-64': sidebarOpen, 'lg:w-0': !sidebarOpen }" x-transition>
            @include('partials.sidebar')
        </div>

        <!-- Mobile sidebar overlay -->
        <div x-show="mobileSidebar" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileSidebar = false" class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" style="display:none"></div>

        <!-- Mobile sidebar panel -->
        <div x-show="mobileSidebar" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 lg:hidden" style="display:none">
            @include('partials.sidebar')
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            <!-- Navbar -->
            @include('partials.navbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <!-- Flash Messages -->
                    @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3" id="flash-success">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            @include('partials.footer')
        </div>
    </div>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')

    <script>
        // Flash message auto-dismiss
        setTimeout(() => {
            const flash = document.getElementById('flash-success');
            if (flash) {
                flash.style.transition = 'opacity 0.3s';
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>