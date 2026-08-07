<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - MITO IT Helpdesk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') !== 'light') { document.documentElement.classList.add('dark'); }
    </script>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 flex">
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="#ffffff" fill-opacity="0.4"><path d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/></g></g></svg>
        </div>
        <div class="relative z-10 flex flex-col justify-center px-12 xl:px-16">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-white tracking-tight">MITO</div>
                    <div class="text-xs font-medium text-white/60 uppercase tracking-widest">IT Helpdesk</div>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-white leading-tight mb-4">
                Streamline your<br>IT support workflow
            </h1>
            <p class="text-base text-white/70 max-w-md leading-relaxed">
                Efficient ticket management, SLA tracking, and team collaboration — all in one place.
            </p>

            <div class="mt-12 flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm text-white/60">SLA Tracking</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-sm text-white/60">Team Collaboration</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="text-sm text-white/60">Analytics</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-bold text-slate-900 dark:text-white">MITO IT Helpdesk</div>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Sign in</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Enter your credentials to access the helpdesk
            </p>

            @if($errors->any())
            <div class="mt-6 p-4 bg-danger-50 dark:bg-danger-500/15 border border-danger-200 dark:border-danger-500/30 rounded-xl">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-danger-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-sm text-danger-700 dark:text-danger-400">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="input"
                           placeholder="you@mito.local">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="input"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary-500 dark:text-primary-400 border-slate-300 dark:border-slate-600 rounded focus:ring-primary-500/20" {{ old('remember') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary w-full flex justify-center items-center gap-2">
                    Sign in
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">
                &copy; 2026 MITO IT Helpdesk
            </p>
        </div>
    </div>
</body>
</html>
