<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login{{ isset($roleName) ? " - {$roleName}" : "" }} - MITO Ticketing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>

        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 {{ $roleColor ?? 'bg-blue-600' }} rounded-2xl shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">MITO Ticketing System</h1>
            <p class="text-sm text-gray-600 mt-1">
                @if(isset($roleName))
                    Sign in as <span class="font-semibold {{ $roleTextColor ?? 'text-blue-600' }}">{{ $roleName }}</span>
                @else
                    Sign in to your account
                @endif
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-md p-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-6">
                @csrf
                @if(isset($roleSlug))
                    <input type="hidden" name="role" value="{{ $roleSlug }}">
                @endif

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $demoEmail ?? '') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                        placeholder="you@mito.local"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                        placeholder="••••••••"
                    >
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>

                    <a href="#" class="text-sm text-gray-400 cursor-not-allowed" title="Disabled for MVP">
                        Forgot Password?
                    </a>
                </div>

                <!-- Login Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center items-center px-4 py-2.5 {{ $roleColor ?? 'bg-blue-600' }} text-white text-sm font-semibold rounded-lg shadow-sm {{ $roleHoverColor ?? 'hover:bg-blue-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        Login
                    </button>
                </div>
            </form>
        </div>

        <!-- Demo Mode Banner -->
        @if(config('app.demo_mode', false))
        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-xs text-amber-700 text-center">
                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <strong>Demo Mode</strong> — All accounts use password: <code class="bg-amber-100 px-1 rounded font-mono">Admin@123</code>
            </p>
        </div>
        @endif

        <!-- Footer -->
        <p class="text-center text-xs text-gray-500 mt-8">
            &copy; {{ date('Y') }} MITO Ticketing System. All rights reserved.
        </p>
    </div>
</body>
</html>