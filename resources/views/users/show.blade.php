@extends('layouts.app')

@section('title', $user->name . ' - User Profile')

@push('skeleton')
<x-loading variant="form" />
@endpush

@section('content')
<div class="space-y-6">
    <x-page-header :title="$user->name" description="User profile and account details">
        @slot('actions')
            <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Users
            </a>
        @endslot
    </x-page-header>

        <div class="card mb-6">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $user->name }}</h2>
                        <p class="text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Role</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                {{ $user->role->slug === 'admin' ? 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300' :
                                   ($user->role->slug === 'manager' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' :
                                   ($user->role->slug === 'staff' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')) }}">
                                {{ $user->role->name ?? 'N/A' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Department</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->department->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Status</dt>
                        <dd class="mt-1">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-danger-50 text-danger-700 dark:bg-danger-500/15 dark:text-danger-300">Inactive</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Last Login</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Force Password Change</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->force_password_change ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Member Since</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn-primary btn-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit User
                    </a>
                </div>
            </div>
        </div>
</div>
@endsection
