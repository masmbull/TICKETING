@extends('layouts.app')

@section('title', 'My Profile - MITO IT Helpdesk')

@push('skeleton')
<x-loading variant="form" />
@endpush

@section('content')
<div class="space-y-6">
    <x-page-header title="My Profile" description="Manage your account information" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Profile Info --}}
        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Profile Information</h2>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="name" class="form-label form-label-required">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="input" />
                    </div>
                    <div>
                        <label for="email" class="form-label form-label-required">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="input" />
                    </div>
                    <div>
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="input" placeholder="Optional" />
                    </div>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Change Password</h2>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="current_password" class="form-label form-label-required">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required class="input" />
                    </div>
                    <div>
                        <label for="password" class="form-label form-label-required">New Password</label>
                        <input type="password" id="password" name="password" required minlength="8" class="input" />
                        <p class="form-help">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label form-label-required">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="input" />
                    </div>
                    <button type="submit" class="btn-primary" style="background-color: #f59e0b;">
                        Change Password
                    </button>
                </form>
            </div>
        </div>

        {{-- Account Info --}}
        <div class="lg:col-span-2 card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Account Information</h2>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Role</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ ucfirst($user->role?->name ?? 'User') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Department</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $user->department?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400 font-medium">Member Since</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white mt-1">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
