<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Change Password - MITO IT Helpdesk</title>
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
<body class="min-h-screen login-bg flex items-center justify-center p-4 transition-colors duration-200">

    {{-- Theme toggle --}}
    <div class="fixed top-4 right-4 z-50">
        <button onclick="toggleTheme()" type="button" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white border border-slate-200 dark:border-slate-700 shadow-sm transition-all duration-200 cursor-pointer" aria-label="Toggle theme">
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>
    </div>

    <div class="w-full max-w-md">

        {{-- Branding Area (same as login) --}}
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
            @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg">
                @foreach ($errors->all() as $error)
                <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <div class="text-center mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Change Password</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">You must change your password before continuing</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">New Password <span class="text-[#E30613]">*</span></label>
                    <div class="relative" x-data="{ show: false }">
                        <input id="password" :type="show ? 'text' : 'password'" name="password" required minlength="8"
                               autocomplete="new-password"
                               class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613]/20 transition-colors"
                               placeholder="Min. 8 characters">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                :aria-label="show ? 'Hide password' : 'Show password'" aria-label="Toggle password visibility">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Confirm Password <span class="text-[#E30613]">*</span></label>
                    <div class="relative" x-data="{ show: false }">
                        <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required
                               autocomplete="new-password"
                               class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613]/20 transition-colors"
                               placeholder="Re-enter your new password">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                :aria-label="show ? 'Hide password' : 'Show password'" aria-label="Toggle password visibility">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-[#E30613] hover:bg-[#c4050f] text-white font-semibold rounded-lg transition-colors cursor-pointer">
                    Change Password
                </button>
            </form>
        </div>

        {{-- Footer: exactly 2 lines --}}
        <div class="text-center mt-6 space-y-1">
            <p class="text-xs text-slate-400 dark:text-slate-500">&copy; 2026 MITO IT Helpdesk</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500">Built with ingenuity, powered by the resources we have.</p>
        </div>
    </div>
</body>
</html>