<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') !== 'light') { document.documentElement.classList.add('dark'); }
    </script>
    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen font-sans text-slate-800 dark:text-slate-200"
      x-data="{
        sidebarOpen: localStorage.getItem('sidebarOpen') !== '0',
        mobileSidebar: false,
        notifOpen: false,
        userDropdownOpen: false,
        darkMode: localStorage.getItem('theme') !== 'light',
        appReady: false,
        appProgress: 0,
      }"
      x-init="
        $watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val ? '1' : '0'));
        $watch('mobileSidebar', val => { document.body.style.overflow = val ? 'hidden' : ''; });
        $watch('darkMode', val => {
            localStorage.setItem('theme', val ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', val);
        });
        document.documentElement.classList.toggle('dark', darkMode);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') mobileSidebar = false; });
        setTimeout(() => appProgress = 35, 40);
        setTimeout(() => appProgress = 75, 260);
        setTimeout(() => appProgress = 100, 500);
        setTimeout(() => appReady = true, 1050);
      ">
    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    {{-- Premium loading experience: MITO wordmark + thin progress bar only (no spinner, no circular animation, no text) --}}
    <div x-show="!appReady" x-cloak
         x-transition:leave="transition-opacity duration-300 ease-out"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[999] flex items-center justify-center bg-white dark:bg-slate-950"
         aria-hidden="true">
        <div class="flex flex-col items-center">
            {{-- MITO wordmark: fills from white to MITO red as progress advances --}}
            <div class="relative select-none text-2xl font-extrabold tracking-tight" aria-label="MITO">
                <span class="text-slate-100 dark:text-slate-800">MITO</span>
                <span class="absolute inset-0 overflow-hidden whitespace-nowrap text-[#E30613] transition-[width] duration-300 ease-out"
                      :style="`width: ${appProgress}%`">MITO</span>
            </div>
            {{-- Thin loading bar 0% -> 100% --}}
            <div class="mt-3 h-0.5 w-28 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-[#E30613] rounded-full transition-[width] duration-300 ease-out"
                     :style="`width: ${appProgress}%`"></div>
            </div>
        </div>
    </div>

    <div class="min-h-screen">
        @include('partials.sidebar')

        <div class="flex flex-col min-h-screen transition-[padding-left] duration-300 ease-in-out"
             :class="sidebarOpen ? 'lg:pl-[240px]' : 'lg:pl-[72px]'">
            @include('partials.navbar')

            <main id="main-content" class="flex-1 w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition:enter="toast" x-transition:leave="toast-exit" class="mb-6 flex items-center gap-3 px-5 py-3.5 bg-white dark:bg-slate-800 border border-green-200 dark:border-green-500/30 rounded-xl shadow-md shadow-green-500/5">
                    <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-500/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ session('success') }}</span>
                    <button @click="show = false" class="ml-auto text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition:enter="toast" x-transition:leave="toast-exit" class="mb-6 flex items-center gap-3 px-5 py-3.5 bg-white dark:bg-slate-800 border border-red-200 dark:border-red-500/30 rounded-xl shadow-md shadow-red-500/5">
                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-500/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ session('error') }}</span>
                    <button @click="show = false" class="ml-auto text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif

                <div x-data="{ pageLoaded: false }" x-init="setTimeout(() => pageLoaded = true, 1100)">
                    <div x-show="pageLoaded" x-cloak
                         x-transition:enter="transition-opacity duration-300 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @yield('content')
                    </div>
                </div>
            </main>

            <footer class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
                <div class="flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
                    <span>&copy; {{ date('Y') }} MITO IT Helpdesk</span>
                    <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
                </div>
            </footer>
        </div>
    </div>

    <div x-show="mobileSidebar" x-cloak x-transition:enter="transition-opacity duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileSidebar = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden"></div>

    @stack('scripts')
</body>
</html>

