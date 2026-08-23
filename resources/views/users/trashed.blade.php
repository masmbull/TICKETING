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
                            <form action="{{ route('users.force', $user->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Permanently delete {{ $user->name }}? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg">
                                    Delete Forever
                                </button>
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
