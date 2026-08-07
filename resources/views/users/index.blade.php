@extends('layouts.app')

@section('title', 'User Management - MITO IT Helpdesk')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">User Management</h1>
            <a href="{{ route('users.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add User
            </a>
        </div>

        {{-- Filters --}}
        <div class="card mb-6">
            <form method="GET" class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                               class="input" />
                    </div>
                    <div>
                        <select name="role" class="select">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="department" class="select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="status" class="select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="submit" class="btn-primary btn-sm">Filter</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Last Login</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                        {{ $user->role->slug === 'admin' ? 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300' :
                                           ($user->role->slug === 'manager' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' :
                                           ($user->role->slug === 'staff' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')) }}">
                                        {{ $user->role->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    {{ $user->department->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-danger-50 text-danger-700 dark:bg-danger-500/15 dark:text-danger-300">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.show', $user->id) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold">View</a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold">Edit</a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-warning-600 hover:text-warning-700 dark:text-warning-400 dark:hover:text-warning-300 font-semibold">
                                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300 font-semibold">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="empty-state">
                                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">No users found</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Try adjusting your search or filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $users->withQueryString()->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
