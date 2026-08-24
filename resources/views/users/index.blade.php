@extends('layouts.app')

@section('title', 'Users - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">User Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage system users</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('users.trashed') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Trash
        </a>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </a>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
         x-data="userSearch({
             allUsers: @js($users),
             allRoles: @js($roles),
             allDepts: @js($departments)
         })">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap gap-3">
            <div class="flex flex-wrap gap-2 flex-1">
                <input type="text" x-model.debounce="searchQuery" placeholder="Search users..." class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white flex-1 min-w-[200px]">
                <select x-model="filterRole" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All Roles</option>
                    <template x-for="role in allRoles" :key="role.id">
                        <option :value="role.id" x-text="role.name"></option>
                    </template>
                </select>
                <select x-model="filterDept" class="px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All Depts</option>
                    <template x-for="dept in allDepts" :key="dept.id">
                        <option :value="dept.id" x-text="dept.name"></option>
                    </template>
                </select>
                <button type="button" @click="resetFilters()" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700" x-show="searchQuery || filterRole || filterDept">Clear</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <template x-for="user in paginatedUsers" :key="user.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#E30613] to-[#c4050f] flex items-center justify-center text-white font-bold text-sm">
                                    <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white" x-text="user.name"></p>
                                    <p class="text-xs text-slate-500" x-text="user.email"></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="px-2 py-1 rounded text-xs font-medium" :class="getRoleClass(user.role)">
                                <span x-text="user.role?.name || 'N/A'"></span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 hidden lg:table-cell" x-text="user.department?.name || '—'"></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium" :class="user.is_active ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-200' : 'bg-red-500/10 text-red-600 border border-red-200'">
                                <span x-text="user.is_active ? 'Active' : 'Inactive'"></span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="`/users/${user.id}`" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a :href="`/users/${user.id}/edit`" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <template x-if="user.id !== {{ auth()->id() }}">
                                    <form method="POST" :action="`/users/${user.id}/toggle-status`" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="p-2 rounded-lg" :class="user.is_active ? 'text-orange-500 hover:bg-orange-50' : 'text-emerald-500 hover:bg-emerald-50'" style="--tw-hover-bg: var(--hover-bg);">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        </button>
                                    </form>
                                </template>
                                <template x-if="user.id !== {{ auth()->id() }}">
                                    <form method="POST" :action="`/users/${user.id}`" :id="'soft-delete-form-' + user.id" class="inline" x-data="{ show: false }">
                                        @csrf @method('DELETE')
                                        <button type="button" @click="show = true" class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        {{-- Delete confirmation: soft-delete aware — user goes to Trash, restorable --}}
                                        <div x-teleport="body">
                                        <div x-show="show"
                                             x-cloak
                                             x-on:keydown.escape.window="show = false"
                                             class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                                             role="dialog" aria-modal="true"
                                             :aria-labelledby="'delete-user-title-' + user.id"
                                             :aria-describedby="'delete-user-desc-' + user.id">
                                            <!-- Backdrop -->
                                            <div x-show="show"
                                                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                                 class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" x-on:click="show = false"></div>
                                            <!-- Card -->
                                            <div x-show="show"
                                                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                                 class="relative w-[calc(100vw-2rem)] max-w-sm max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl p-6 text-center">
                                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10 text-red-500" aria-hidden="true">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16m-9-4v4m2-4v4"/></svg>
                                                </div>
                                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white" :id="'delete-user-title-' + user.id">Delete User?</h3>
                                                <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400" :id="'delete-user-desc-' + user.id">
                                                    Are you sure you want to delete
                                                    <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="user.name"></span>?
                                                </p>
                                                <p class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                                    This user will be moved to Trash and can be restored later.
                                                </p>
                                                <div class="mt-6 flex gap-3">
                                                    <button type="button" x-on:click="show = false"
                                                            class="flex-1 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 dark:focus-visible:ring-slate-500 transition">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" :form="'soft-delete-form-' + user.id"
                                                            class="flex-1 px-4 py-2 text-sm font-medium bg-[#E30613] hover:bg-[#c4050f] text-white rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#E30613] dark:focus-visible:ring-offset-slate-800 transition">
                                                        Delete User
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        </div><!-- /x-teleport -->
                                    </form>
                                </template>
                            </div>
                        </td>
                    </tr>
                    </template>
                    <template x-if="filteredUsers.length === 0">
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">No users found</td>
                    </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <template x-if="filteredUsers.length > 0">
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <span x-text="((currentPage - 1) * itemsPerPage) + 1"></span>
                <span>–</span>
                <span x-text="Math.min(currentPage * itemsPerPage, totalResults)"></span>
                <span>of</span>
                <span x-text="totalResults"></span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="prevPage()" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-700'" class="px-3 py-1.5 text-sm rounded-lg text-slate-600 dark:text-slate-300">Previous</button>
                
                <template x-for="page in totalPages" :key="page">
                    <template x-if="(page >= currentPage - 2 && page <= currentPage + 2) || totalPages <= 5">
                        <button @click="goToPage(page)" :class="currentPage === page ? 'bg-[#E30613] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'" class="px-3 py-1.5 text-sm rounded-lg">
                            <span x-text="page"></span>
                        </button>
                    </template>
                </template>

                <button @click="nextPage()" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-700'" class="px-3 py-1.5 text-sm rounded-lg text-slate-600 dark:text-slate-300">Next</button>
            </div>
        </div>
        </template>

    </div>
</div>
@endsection
