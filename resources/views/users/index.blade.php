@extends('layouts.app')

@section('title', 'User Management - MITO IT Helpdesk')

@push('skeleton')
<x-loading variant="table" />
@endpush

@section('content')
<div class="space-y-6">
    <x-page-header title="User Management" description="Manage users, roles and departments">
        @slot('actions')
            <a href="{{ route('users.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add User
            </a>
        @endslot
    </x-page-header>

        {{-- Filters --}}
        <x-filter-bar action="{{ route('users.index') }}" method="GET">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
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
                            <option value="">All Depts</option>
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
            @slot('actions')
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">Reset</a>
            @endslot
        </x-filter-bar>

        {{-- Users Table --}}
        <x-table>
            <table class="table">
                    <thead>
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Department</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Last Login</th>
                            <th class="px-4 py-2.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                        {{ $user->role->slug === 'admin' ? 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300' :
                                           ($user->role->slug === 'manager' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' :
                                           ($user->role->slug === 'staff' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')) }}">
                                        {{ $user->role->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400 hidden md:table-cell">
                                    {{ $user->department->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-danger-50 text-danger-700 dark:bg-danger-500/15 dark:text-danger-300">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 hidden lg:table-cell">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn-outline btn-xs">View</a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn-primary btn-xs">Edit</a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn-outline btn-xs">
                                                    {{ $user->is_active ? 'Off' : 'On' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" x-data="{ show: false }" x-on:submit.prevent="show = true">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn-danger btn-xs" x-on:click="show = true">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                                <div x-show="show" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50" x-cloak>
                                                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-5">
                                                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Confirm Delete</h3>
                                                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Delete this user?</p>
                                                        <div class="flex justify-end gap-2">
                                                            <button type="button" class="btn-outline btn-sm" x-on:click="show = false">Cancel</button>
                                                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                                                        </div>
                                                    </div>
                                                </div>
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
            @slot('footer')
                {{ $users->withQueryString()->links('components.pagination') }}
            @endslot
        </x-table>
</div>
@endsection
