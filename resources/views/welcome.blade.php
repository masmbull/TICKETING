<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MITO Ticketing System - Enterprise Internal Helpdesk Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eff6ff', 100:'#dbeafe', 200:'#bfdbfe', 300:'#93c5fd', 400:'#60a5fa', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8', 800:'#1e40af', 900:'#1e3a8a' },
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .role-card { transition: all 0.3s ease; }
        .role-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%); }
        .float-animation { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white">
        <div class="max-w-6xl mx-auto px-6 py-16 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-2xl shadow-lg mb-6 float-animation">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-3">MITO Ticketing System</h1>
            <p class="text-xl text-blue-100 font-light">Internal Helpdesk & Support Portal</p>
            <p class="text-blue-200 mt-2 text-sm">Enterprise Internal Helpdesk Platform</p>
        </div>
    </header>

    <!-- Role Selection -->
    <main class="max-w-6xl mx-auto px-6 -mt-8 pb-16">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-gray-900">Who Am I?</h2>
            <p class="text-gray-600 mt-1">Choose your role to continue.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Administrator -->
            <div class="role-card bg-white rounded-xl shadow-md p-6 border border-gray-100 cursor-pointer" onclick="window.location='{{ url('/login/admin') }}'">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-2xl mb-4">
                        <span class="text-3xl">👑</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Administrator</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Manage users, SLA, categories, departments and all tickets.</p>
                    <a href="{{ url('/login/admin') }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 transition-colors shadow-sm">
                        Continue
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- IT Manager -->
            <div class="role-card bg-white rounded-xl shadow-md p-6 border border-gray-100 cursor-pointer" onclick="window.location='{{ url('/login/manager') }}'">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-2xl mb-4">
                        <span class="text-3xl">👨‍💼</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">IT Manager</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Monitor team performance, SLA, reports and assignments.</p>
                    <a href="{{ url('/login/manager') }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        Continue
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- IT Support -->
            <div class="role-card bg-white rounded-xl shadow-md p-6 border border-gray-100 cursor-pointer" onclick="window.location='{{ url('/login/support') }}'">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-2xl mb-4">
                        <span class="text-3xl">🛠</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">IT Support</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Handle incidents, update ticket status and resolve requests.</p>
                    <a href="{{ url('/login/support') }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                        Continue
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Employee -->
            <div class="role-card bg-white rounded-xl shadow-md p-6 border border-gray-100 cursor-pointer" onclick="window.location='{{ url('/login/employee') }}'">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 rounded-2xl mb-4">
                        <span class="text-3xl">👤</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Employee</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Create support tickets and monitor ticket progress.</p>
                    <a href="{{ url('/login/employee') }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-amber-600 text-white text-sm font-semibold rounded-lg hover:bg-amber-700 transition-colors shadow-sm">
                        Continue
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} MITO Ticketing System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>