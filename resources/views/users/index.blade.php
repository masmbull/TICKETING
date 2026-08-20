@extends('layouts.app')

@section('title', 'Users - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">User Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage system users</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
         x-data="userSearch({
             allUsers: @json($users->items()),
             allRoles: @json($roles),
             allDepts: @json($departments)
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
                    <template x-for="user in filteredUsers" :key="user.id">
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
                                    <form method="POST" :action="`/users/${user.id}`" class="inline" x-data="{ show: false }">
                                        @csrf @method('DELETE')
                                        <button type="button" @click="show = true" class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <div x-show="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-cloak>
                                            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 max-w-sm">
                                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Delete User?</h3>
                                                <p class="text-sm text-slate-500 mb-4">This cannot be undone.</p>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" @click="show = false" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Cancel</button>
                                                    <button type="submit" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-lg">Delete</button>
                                                </div>
                                            </div>
                                        </div>
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

    </div>
</div>

@push('scripts')
<script>
function userSearch(config) {
    return {
        allUsers: config.allUsers,
        allRoles: config.allRoles,
        allDepts: config.allDepts,
        searchQuery: '',
        filterRole: '',
        filterDept: '',

        get filteredUsers() {
            return this.allUsers
                .filter(user => {
                    // Search filter (case-insensitive)
                    if (this.searchQuery.trim()) {
                        const query = this.searchQuery.trim().toLowerCase();
                        const matchesName = user.name.toLowerCase().includes(query);
                        const matchesEmail = user.email.toLowerCase().includes(query);
                        const matchesJobTitle = (user.job_title || '').toLowerCase().includes(query);
                        const matchesRole = (user.role?.name || '').toLowerCase().includes(query);
                        
                        if (!matchesName && !matchesEmail && !matchesJobTitle && !matchesRole) {
                            return false;
                        }
                    }

                    // Role filter
                    if (this.filterRole && user.role?.id != this.filterRole) {
                        return false;
                    }

                    // Department filter
                    if (this.filterDept && user.department?.id != this.filterDept) {
                        return false;
                    }

                    return true;
                })
                .sort((a, b) => a.name.localeCompare(b.name)); // A-Z sorting
        },

        getRoleClass(role) {
            if (!role) return 'bg-slate-100 text-slate-600 border border-slate-200';
            
            const slug = role.slug || role.name?.toLowerCase();
            return {
                'admin': 'bg-purple-500/10 text-purple-600 border border-purple-200',
                'manager': 'bg-blue-500/10 text-blue-600 border border-blue-200',
                'staff': 'bg-emerald-500/10 text-emerald-600 border border-emerald-200',
            }[slug] || 'bg-slate-100 text-slate-600 border border-slate-200';
        },

        resetFilters() {
            this.searchQuery = '';
            this.filterRole = '';
            this.filterDept = '';
        }
    }
}
</script>
@endpush
@endsection
