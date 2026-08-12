<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Sign in - MITO IT Helpdesk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') !== 'light') { document.documentElement.classList.add('dark'); }
    </script>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 flex items-center justify-center font-sans text-slate-800 dark:text-slate-200 antialiased">
    <main class="w-full max-w-md px-4 py-10">
        {{-- Single centered login card --}}
        <div class="card p-8 sm:p-10">
            {{-- MITO logo on top --}}
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-2xl bg-[#E30613] shadow-lg shadow-[#E30613]/25 flex items-center justify-center select-none" aria-hidden="true">
                    <span class="text-white text-xl font-extrabold tracking-tight">M</span>
                </div>
                <h1 class="mt-5 text-2xl font-bold text-slate-900 dark:text-white tracking-tight">MITO IT Helpdesk</h1>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Internal Ticketing System</p>

                @if(isset($roleName))
                <span class="inline-flex items-center mt-4 px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    Signing in as {{ $roleName }}
                </span>
                @endif
            </div>

            @if($errors->any())
            <div class="mt-6 p-4 bg-danger-50 dark:bg-danger-500/15 border border-danger-200 dark:border-danger-500/30 rounded-xl" role="alert">
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
                <input type="hidden" name="role" value="{{ $roleSlug ?? '' }}">

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

                <div class="flex items-center justify-between gap-3">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-[#E30613] border-slate-300 dark:border-slate-600 rounded focus:ring-[#E30613]/20" {{ old('remember') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Remember me</span>
                    </label>

                    <button type="button" disabled title="Coming soon"
                            class="text-sm font-medium text-slate-400 dark:text-slate-500 cursor-not-allowed select-none">
                        Forgot password?
                    </button>
                </div>

                <button type="submit" class="btn-primary w-full justify-center items-center gap-2 py-2.5">
                    Sign in
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>
        </div>

        <p class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">
            &copy; {{ date('Y') }} MITO IT Helpdesk &middot; Internal use only
        </p>
    </main>
</body>
</html>
