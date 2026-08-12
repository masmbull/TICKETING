@extends('layouts.app')

@section('title', 'Settings - MITO IT Helpdesk')

@push('skeleton')
<x-loading variant="cards" :count="3" />
@endpush

@section('content')
<div class="space-y-6">
    <x-page-header title="Settings" description="Manage application settings" />

    <div class="max-w-2xl space-y-6">
        {{-- Profile --}}
        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Profile</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="text-base font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</div>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-full bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 capitalize">
                                {{ auth()->user()->role->name ?? 'User' }}
                            </span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn-secondary btn-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Profile
                </a>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Security</h3>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    <div>
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="input" placeholder="Enter current password" />
                    </div>
                    <div>
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" class="input" placeholder="Enter new password" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="input" placeholder="Confirm new password" />
                    </div>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Change Password
                    </button>
                </form>
            </div>
        </div>

        {{-- Admin Shortcuts --}}
        @if(auth()->user()->isAdmin())
        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Administration</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('users.index') }}" class="quick-action">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Users</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Manage users & roles</div>
                        </div>
                    </a>
                    <a href="{{ route('categories.index') }}" class="quick-action">
                        <svg class="w-5 h-5 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M4 7h.01M4 11h.01M4 15h.01M4 19h.01"/></svg>
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Categories</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Manage categories</div>
                        </div>
                    </a>
                    <a href="{{ route('sla-policies.index') }}" class="quick-action">
                        <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">SLA Policies</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Configure SLA rules</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
