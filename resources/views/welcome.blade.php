<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MITO Ticketing System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-bg { background: linear-gradient(135deg, #172554 0%, #2563eb 50%, #3b82f6 100%); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-sans text-slate-800 antialiased">
    <header class="gradient-bg text-white">
        <div class="max-w-6xl mx-auto px-6 py-16 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl shadow-lg mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-3 tracking-tight">MITO Ticketing System</h1>
            <p class="text-xl text-blue-100 font-light">Internal Helpdesk & Support Portal</p>
            <p class="text-blue-200 mt-2 text-sm">Enterprise Internal Helpdesk Platform</p>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 -mt-8 pb-16">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-slate-900">Who Am I?</h2>
            <p class="text-slate-500 mt-1">Choose your role to continue.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ route('login', ['role' => 'admin']) }}" class="card card-hover p-6 text-center group">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-200">
                    <span class="text-3xl">&#x1F451;</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Administrator</h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">Manage users, SLA, categories, departments and all tickets.</p>
                <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-xl group-hover:bg-purple-700 transition-all duration-200 shadow-sm">
                    Continue
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </span>
            </a>

            <a href="{{ route('login', ['role' => 'manager']) }}" class="card card-hover p-6 text-center group">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-200">
                    <span class="text-3xl">&#x1F464;</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">IT Manager</h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">Monitor team performance, SLA, reports and assignments.</p>
                <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl group-hover:bg-blue-700 transition-all duration-200 shadow-sm">
                    Continue
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </span>
            </a>

            <a href="{{ route('login', ['role' => 'support']) }}" class="card card-hover p-6 text-center group">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-200">
                    <span class="text-3xl">&#x1F6E0;</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">IT Support</h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">Handle incidents, update ticket status and resolve requests.</p>
                <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-xl group-hover:bg-green-700 transition-all duration-200 shadow-sm">
                    Continue
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </span>
            </a>

            <a href="{{ route('login', ['role' => 'employee']) }}" class="card card-hover p-6 text-center group">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-200">
                    <span class="text-3xl">&#x1F464;</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Employee</h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">Create support tickets and monitor ticket progress.</p>
                <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-amber-600 text-white text-sm font-semibold rounded-xl group-hover:bg-amber-700 transition-all duration-200 shadow-sm">
                    Continue
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </span>
            </a>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <p class="text-sm text-slate-500">&copy; {{ date('Y') }} MITO Ticketing System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
