@extends('layouts.app')

@section('title', 'Settings - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Application settings and system information</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">System Information</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Application</dt>
                    <dd class="font-medium text-gray-900">MITO IT Helpdesk</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Version</dt>
                    <dd class="font-medium text-gray-900">0.1.0 (MVP)</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Laravel</dt>
                    <dd class="font-medium text-gray-900">{{ app()->version() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">PHP</dt>
                    <dd class="font-medium text-gray-900">{{ phpversion() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Environment</dt>
                    <dd class="font-medium text-gray-900">{{ ucfirst(config('app.env')) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Database</dt>
                    <dd class="font-medium text-gray-900">{{ config('database.default') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
            <div class="space-y-3">
                @if(auth()->user()->role?->slug === 'admin')
                    <a href="{{ route('users.index') }}" class="block p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <div class="text-sm font-medium text-gray-900">User Management</div>
                        <div class="text-xs text-gray-500">Manage users, roles, and departments</div>
                    </a>
                    <a href="{{ route('categories.index') }}" class="block p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <div class="text-sm font-medium text-gray-900">Category Management</div>
                        <div class="text-xs text-gray-500">Manage ticket categories and subcategories</div>
                    </a>
                    <a href="{{ route('sla-policies.index') }}" class="block p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <div class="text-sm font-medium text-gray-900">SLA Policies</div>
                        <div class="text-xs text-gray-500">Manage service level agreements per priority</div>
                    </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="block p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                    <div class="text-sm font-medium text-gray-900">Edit Profile</div>
                    <div class="text-xs text-gray-500">Update your personal information and password</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection