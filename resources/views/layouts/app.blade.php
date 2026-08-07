<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - MITO IT Helpdesk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="bg-[#f8fafc] min-h-screen font-sans text-slate-800" x-data="{ sidebarOpen: true, mobileSidebar: false }">

    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        @include('partials.sidebar')

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-out"
             :class="sidebarOpen ? 'lg:ml-[260px]' : 'lg:ml-[72px]'">

            {{-- NAVBAR --}}
            @include('partials.navbar')

            {{-- PAGE CONTENT --}}
            <main class="flex-1 p-6 lg:p-8 max-w-[1600px] mx-auto w-full">
                {{-- Flash Messages with Alpine.js --}}
                @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)"
                     x-show="show"
                     x-transition:enter="toast-enter"
                     x-transition:leave="toast-leave"
                     class="mb-6 flex items-center gap-3 px-5 py-3.5 bg-white border border-success-200 rounded-2xl shadow-lg shadow-success-500/5">
                    <div class="w-8 h-8 rounded-xl bg-success-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ session('success') }}</span>
                    <button @click="show = false" class="ml-auto text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)"
                     x-show="show"
                     x-transition:enter="toast-enter"
                     x-transition:leave="toast-leave"
                     class="mb-6 flex items-center gap-3 px-5 py-3.5 bg-white border border-danger-200 rounded-2xl shadow-lg shadow-danger-500/5">
                    <div class="w-8 h-8 rounded-xl bg-danger-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ session('error') }}</span>
                    <button @click="show = false" class="ml-auto text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif

                @yield('content')
            </main>

            {{-- FOOTER --}}
            <footer class="px-6 py-4 border-t border-slate-100 bg-white/50">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span>&copy; {{ date('Y') }} MITO IT Helpdesk</span>
                    <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
                </div>
            </footer>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div x-show="mobileSidebar" x-transition:enter="transition-opacity duration-300 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="mobileSidebar = false"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden"></div>

    @yield('scripts')
</body>
</html>