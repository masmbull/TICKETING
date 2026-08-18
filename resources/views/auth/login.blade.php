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
    </script>
    <style>
        body { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 dark:bg-[#0f172a] flex items-center justify-center p-4 transition-colors duration-200">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[#E30613] to-[#c4050f] text-white font-bold text-2xl shadow-lg shadow-red-500/20 mb-4">M</div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">MITO</h1>
            <p class="text-[11px] uppercase tracking-widest text-slate-500 dark:text-slate-400">IT Helpdesk</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Internal Ticketing System</p>
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
                    <a href="{{ route('password.change') }}" class="text-sm text-[#E30613] hover:text-[#c4050f] dark:text-[#E30613] dark:hover:text-[#ff4d5a] transition-colors">Forgot password?</a>
                </div>

                <button type="submit" class="w-full py-3 bg-[#E30613] hover:bg-[#c4050f] text-white font-semibold rounded-lg transition-colors">
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-center text-slate-500 dark:text-slate-500 text-sm mt-6">
            &copy; {{ date('Y') }} MITO IT Helpdesk
        </p>
    </div>
</body>
</html>
