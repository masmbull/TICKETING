<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Sign In - MITO IT Helpdesk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') !== 'light') { document.documentElement.classList.add('dark'); }
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
    <style>
        body { font-family: 'IBM Plex Sans', sans-serif; }
        .login-bg {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .dark .login-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen login-bg flex items-center justify-center p-4 transition-colors duration-200 relative"
      x-data="{
        appReady: sessionStorage.getItem('mito_splash_seen') === '1',
      }"
      x-init="
        if (!appReady) {
            const dismissSplash = () => {
                setTimeout(() => {
                    appReady = true;
                    sessionStorage.setItem('mito_splash_seen', '1');
                }, 600);
            };
            if (document.readyState === 'complete') {
                dismissSplash();
            } else {
                window.addEventListener('load', dismissSplash, { once: true });
                setTimeout(dismissSplash, 900);
            }
        }
      ">

    {{-- Loading / Splash Screen on Initial App Load --}}
    <div x-show="!appReady" x-cloak
         x-transition:leave="transition ease-out duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95 pointer-events-none"
         class="fixed inset-0 z-[100] bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center p-4 transition-colors duration-300">
        
        <div class="relative z-10 flex flex-col items-center gap-5 text-center animate-splash-in">
            <div class="mb-1">
                <img src="{{ asset('assets/mito-electronic-removebg-preview-resize.png') }}"
                     alt="MITO Electronic"
                     class="h-20 sm:h-24 md:h-28 w-auto max-w-[260px] sm:max-w-[300px] object-contain select-none animate-splash-logo mito-logo-adaptive">
            </div>

            <div class="animate-splash-text">
                <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.22em] text-slate-700 dark:text-slate-300">
                    Internal Ticketing System
                </p>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5 font-medium">
                    MITO IT Helpdesk
                </div>
            </div>

            {{-- Subtle CSS Loading Dots --}}
            <div class="flex items-center gap-2 mt-2 animate-splash-dots" aria-label="Loading...">
                <span class="splash-dot w-2.5 h-2.5 rounded-full bg-[#E30613]"></span>
                <span class="splash-dot w-2.5 h-2.5 rounded-full bg-[#E30613]"></span>
                <span class="splash-dot w-2.5 h-2.5 rounded-full bg-[#E30613]"></span>
            </div>
        </div>
    </div>

    {{-- Theme toggle --}}
    <div x-show="appReady" x-cloak class="fixed top-4 right-4 z-50">
        <button onclick="toggleTheme()" type="button" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white border border-slate-200 dark:border-slate-700 shadow-sm transition-all duration-200 cursor-pointer" aria-label="Toggle theme">
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>
    </div>

    {{-- Main Login Content --}}
    <div x-show="appReady" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="w-full max-w-md">
        
        {{-- Branding Area --}}
        <div class="text-center mb-8">
            <div class="mb-4">
                <img src="{{ asset('assets/mito-electronic-removebg-preview-resize.png') }}"
                     alt="MITO Electronic"
                     class="h-20 sm:h-24 md:h-28 mx-auto object-contain select-none mito-logo-adaptive">
            </div>
            <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Internal Ticketing System
            </p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm dark:shadow-none transition-colors duration-200">
            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg">
                @foreach ($errors->all() as $error)
                <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="role" value="{{ $roleSlug ?? '' }}">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email</label>
                    <input type="email" name="email" required autofocus autocomplete="email"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613]/20 transition-colors"
                           placeholder="you@company.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613]/20 transition-colors"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-[#E30613] focus:ring-[#E30613]/20">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Remember me</span>
                    </label>
                    <button type="button" onclick="MITO.forgotPassword()" class="text-sm text-[#E30613] hover:text-[#c4050f] dark:text-[#E30613] dark:hover:text-[#ff4d5a] transition-colors cursor-pointer">Forgot password?</button>
                </div>

                <button type="submit" class="w-full py-3 bg-[#E30613] hover:bg-[#c4050f] text-white font-semibold rounded-lg transition-colors cursor-pointer">
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-400 dark:text-slate-500 mt-6">
            &copy; 2026 MITO IT Helpdesk &middot; Built with ingenuity, powered by the resources we have.
        </p>
    </div>
</body>
</html>
