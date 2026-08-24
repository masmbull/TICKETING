@extends('layouts.app')

@section('title', 'Trash - Users - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Deleted Users</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Restore or permanently remove soft-deleted users</p>
        </div>
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">
            Back to Users
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Deleted At</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Deleted By</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $user->role?->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $user->deleted_at?->format('M d, Y H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $user->deletedBy?->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <form action="{{ route('users.restore', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg">
                                    Restore
                                </button>
                            </form>
                            {{-- Delete Forever: irreversible — custom confirmation replaces native confirm() --}}
                            <form action="{{ route('users.force', $user->id) }}" method="POST" id="force-delete-form-{{ $user->id }}" class="inline" x-data="{ show: false }">
                                @csrf
                                @method('DELETE')
                                <button type="button" x-on:click="show = true" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg">
                                    Delete Forever
                                </button>
                                <div x-teleport="body">
                                <div x-show="show"
                                     x-cloak
                                     x-on:keydown.escape.window="show = false"
                                     class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                                     role="dialog" aria-modal="true"
                                     aria-labelledby="force-delete-title-{{ $user->id }}"
                                     aria-describedby="force-delete-desc-{{ $user->id }}">
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
                                        <h3 id="force-delete-title-{{ $user->id }}" class="text-lg font-semibold text-slate-900 dark:text-white">Delete User Permanently?</h3>
                                        <p id="force-delete-desc-{{ $user->id }}" class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                            Are you sure you want to permanently delete
                                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->name }}</span>?
                                        </p>
                                        <p class="mt-1 text-sm leading-relaxed text-red-600/90 dark:text-red-400/90">
                                            This action cannot be undone. The user and their account data will be permanently removed.
                                        </p>
                                        <div class="mt-6 flex gap-3">
                                            <button type="button" x-on:click="show = false"
                                                    class="flex-1 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 dark:focus-visible:ring-slate-500 transition">
                                                Cancel
                                            </button>
                                            <button type="submit" form="force-delete-form-{{ $user->id }}"
                                                    class="flex-1 px-4 py-2 text-sm font-medium bg-[#E30613] hover:bg-[#c4050f] text-white rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#E30613] dark:focus-visible:ring-offset-slate-800 transition">
                                                Delete Forever
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                </div><!-- /x-teleport -->
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <p class="text-sm text-slate-500 dark:text-slate-400">No deleted users.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
