<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MITO Ticketing System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') !== 'light') { document.documentElement.classList.add('dark'); }
    </script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(3deg); }
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes cardReveal {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 8s ease-in-out 2s infinite; }
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient-shift 12s ease infinite;
        }
        .card-enter {
            opacity: 0;
            animation: cardReveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .btn-arrow { transition: transform 0.25s ease; }
        .group:hover .btn-arrow { transform: translateX(4px); }

        .role-card {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 0.25s ease,
                        border-color 0.25s ease;
        }
        .role-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
            border-color: var(--color-primary-500);
        }
        .dark .role-card:hover {
            border-color: var(--color-primary-400);
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .glass-nav {
            background: rgba(15, 23, 42, 0.72);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-slate-900 dark:text-white tracking-tight text-[15px]">MITO Ticketing System</span>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Version Badge -->
                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        v1.0.0
                    </span>

                    <!-- Light/Dark Toggle -->
                    <button id="theme-toggle" type="button" class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors duration-200" aria-label="Toggle theme">
                        <svg id="sun-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg id="moon-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    <!-- Company Logo -->
                    <div class="w-8 h-8 rounded-lg bg-slate-900 dark:bg-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white dark:text-slate-900" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden pt-16">
        <!-- Animated Background -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/20 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 animate-gradient"></div>
            <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-400/20 dark:bg-blue-600/10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute top-1/3 -right-20 w-80 h-80 bg-indigo-400/20 dark:bg-indigo-600/10 rounded-full blur-3xl animate-float-delayed"></div>
            <div class="absolute bottom-1/4 left-1/4 w-72 h-72 bg-purple-400/15 dark:bg-purple-600/10 rounded-full blur-3xl animate-float" style="animation-delay: 4s;"></div>
            <div class="absolute -bottom-20 right-1/4 w-64 h-64 bg-cyan-400/15 dark:bg-cyan-600/10 rounded-full blur-3xl animate-float-delayed"></div>
        </div>

        <!-- Hero Content -->
        <div class="relative z-10 text-center px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-4 tracking-wide uppercase">Welcome to</p>
            <h1 class="text-[42px] sm:text-[48px] font-bold text-slate-900 dark:text-white mb-4 tracking-tight leading-[1.1]">
                MITO Ticketing System
            </h1>
            <p class="text-lg sm:text-[18px] text-slate-600 dark:text-slate-300 mb-2 font-light">Internal IT Service Desk</p>
            <p class="text-sm text-slate-500 dark:text-slate-400">Choose your role to continue</p>
        </div>

        <!-- Role Cards -->
        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14 w-full">

            <!-- Administrator -->
            <a href="{{ route('login', ['role' => 'admin']) }}"
               class="role-card card-enter group block p-6 rounded-2xl bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-slate-200 dark:border-slate-800 shadow-sm"
               style="animation-delay: 0.1s;">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-700 text-white flex items-center justify-center mb-5 shadow-lg shadow-purple-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1.5">Administrator</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">Manage users, SLA, categories, departments and all tickets.</p>
                    <ul class="text-left text-xs text-slate-600 dark:text-slate-400 space-y-1.5 mb-5 w-full">
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-purple-500 flex-shrink-0"></span>
                            User & role management
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-purple-500 flex-shrink-0"></span>
                            SLA policy configuration
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-purple-500 flex-shrink-0"></span>
                            System-wide ticket oversight
                        </li>
                    </ul>
                    <div class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-all duration-250 shadow-sm shadow-purple-600/10">
                        Continue
                        <svg class="btn-arrow w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- IT Manager -->
            <a href="{{ route('login', ['role' => 'manager']) }}"
               class="role-card card-enter group block p-6 rounded-2xl bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-slate-200 dark:border-slate-800 shadow-sm"
               style="animation-delay: 0.2s;">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center mb-5 shadow-lg shadow-blue-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1.5">IT Manager</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">Monitor team performance, SLA, reports and assignments.</p>
                    <ul class="text-left text-xs text-slate-600 dark:text-slate-400 space-y-1.5 mb-5 w-full">
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>
                            Team performance dashboard
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>
                            SLA compliance tracking
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>
                            Advanced analytics & reports
                        </li>
                    </ul>
                    <div class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all duration-250 shadow-sm shadow-blue-600/10">
                        Continue
                        <svg class="btn-arrow w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- IT Support -->
            <a href="{{ route('login', ['role' => 'support']) }}"
               class="role-card card-enter group block p-6 rounded-2xl bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-slate-200 dark:border-slate-800 shadow-sm"
               style="animation-delay: 0.3s;">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center mb-5 shadow-lg shadow-emerald-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1.5">IT Support</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">Handle incidents, update ticket status and resolve requests.</p>
                    <ul class="text-left text-xs text-slate-600 dark:text-slate-400 space-y-1.5 mb-5 w-full">
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            Ticket queue & triage
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            Status updates & comments
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            Resolution & escalation
                        </li>
                    </ul>
                    <div class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-all duration-250 shadow-sm shadow-emerald-600/10">
                        Continue
                        <svg class="btn-arrow w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Employee -->
            <a href="{{ route('login', ['role' => 'employee']) }}"
               class="role-card card-enter group block p-6 rounded-2xl bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-slate-200 dark:border-slate-800 shadow-sm"
               style="animation-delay: 0.4s;">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center mb-5 shadow-lg shadow-amber-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1.5">Employee</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">Create support tickets and monitor ticket progress.</p>
                    <ul class="text-left text-xs text-slate-600 dark:text-slate-400 space-y-1.5 mb-5 w-full">
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-amber-500 flex-shrink-0"></span>
                            Submit new support tickets
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-amber-500 flex-shrink-0"></span>
                            Track ticket progress
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-amber-500 flex-shrink-0"></span>
                            Add comments & attachments
                        </li>
                    </ul>
                    <div class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl transition-all duration-250 shadow-sm shadow-amber-600/10">
                        Continue
                        <svg class="btn-arrow w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-200 dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">MITO Ticketing System</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">Version 1.0.0</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">All systems operational</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            html.classList.remove('dark');
            sunIcon.classList.remove('hidden');
            moonIcon.classList.add('hidden');
        } else {
            html.classList.add('dark');
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
        }

        themeToggleBtn.addEventListener('click', () => {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
