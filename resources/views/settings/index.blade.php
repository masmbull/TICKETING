@extends('layouts.app')

@section('title', 'Settings - MITO IT Helpdesk')

<div class="min-h-screen bg-gray-50 -m-6 p-6">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage application settings</p>
    </div>

    <div class="max-w-2xl space-y-6">
        {{-- Profile --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Profile</h3>
            </div>
            <div class="px-5 py-4">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="text-base font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 capitalize">
                                {{ auth()->user()->role->name ?? 'User' }}
                            </span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Edit Profile
                </a>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Security</h3>
            </div>
            <div class="px-5 py-4">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Enter current password">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" id="password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Enter new password">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Confirm new password">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
        {{-- Admin Shortcuts --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Administration</h3>
            </div>
            <div class="px-5 py-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Users</div>
                            <div class="text-xs text-gray-500">Manage users & roles</div>
                        </div>
                    </a>
                    <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M4 7h.01M4 11h.01M4 15h.01M4 19h.01"/></svg>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Categories</div>
                            <div class="text-xs text-gray-500">Manage categories</div>
                        </div>
                    </a>
                    <a href="{{ route('sla-policies.index') }}" class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <div class="text-sm font-medium text-gray-900">SLA Policies</div>
                            <div class="text-xs text-gray-500">Configure SLA rules</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection