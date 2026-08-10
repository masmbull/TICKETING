@extends('layouts.app')

@section('title', 'SLA Policies - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">SLA Policies</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage service level agreements for different priority levels</p>
        </div>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Policy
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-success-50 border border-success-200 dark:bg-success-500/15 dark:border-success-500/30 rounded-xl">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium text-success-700 dark:text-success-400">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($policies as $policy)
            <div class="card">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        @php
                            $priorityClass = match($policy->priority){
                                'Critical' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/15 dark:text-danger-400',
                                'High' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
                                'Medium' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                                default => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $priorityClass }}">{{ $policy->priority }}</span>
                        @if($policy->is_active)
                            <span class="w-2 h-2 rounded-full bg-success-400"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                        @endif
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">{{ $policy->name }}</h3>
                    <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400">
                        <div class="flex justify-between">
                            <span>Response:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $policy->response_hours }}h</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Resolution:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $policy->resolution_hours }}h</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Escalation:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $policy->escalation_enabled ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button onclick="openEditModal({{ $policy->id }}, '{{ $policy->name }}', '{{ $policy->priority }}', {{ $policy->response_hours }}, {{ $policy->resolution_hours }}, {{ $policy->escalation_enabled ? 'true' : 'false' }})" class="btn-primary btn-sm">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <form method="POST" action="{{ route('sla-policies.destroy', $policy->id) }}" class="inline" x-data="{ show: false }" x-on:submit.prevent="show = true">
                            @csrf @method('DELETE')
                            <button type="button" class="btn-danger btn-sm" x-on:click="show = true">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                            <div x-show="show" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50" x-cloak>
                                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Confirm Delete</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Are you sure you want to delete this SLA policy? This action cannot be undone.</p>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="btn-outline" x-on:click="show = false">Cancel</button>
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-500 dark:text-slate-400 text-sm">No SLA policies defined yet.</div>
        @endforelse
    </div>

    {{-- Create Modal --}}
    <div id="createModal" class="hidden fixed inset-0 bg-slate-900/50 dark:bg-slate-950/80 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg w-full max-w-md">
            <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Create SLA Policy</h3>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-slate-500 dark:hover:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('sla-policies.store') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="form-label form-label-required">Name</label>
                    <input type="text" name="name" required class="input" placeholder="e.g. Critical Response" />
                </div>
                <div>
                    <label class="form-label form-label-required">Priority</label>
                    <select name="priority" required class="select">
                        <option value="Critical">Critical</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label form-label-required">Response (hours)</label>
                        <input type="number" name="response_hours" required min="0" step="0.5" class="input" />
                    </div>
                    <div>
                        <label class="form-label form-label-required">Resolution (hours)</label>
                        <input type="number" name="resolution_hours" required min="0" step="0.5" class="input" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="escalation_enabled" value="1" class="w-4 h-4 text-primary-500 border-slate-300 dark:text-primary-400 dark:border-slate-600 rounded focus:ring-primary-500/20" />
                    <label class="text-sm text-slate-700 dark:text-slate-300">Enable Escalation</label>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 bg-slate-900/50 dark:bg-slate-950/80 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg w-full max-w-md">
            <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit SLA Policy</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-slate-500 dark:hover:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="editForm" method="POST" class="p-5 space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="form-label form-label-required">Name</label>
                    <input type="text" name="name" id="edit_name" required class="input" />
                </div>
                <div>
                    <label class="form-label form-label-required">Priority</label>
                    <select name="priority" id="edit_priority" required class="select">
                        <option value="Critical">Critical</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label form-label-required">Response (hours)</label>
                        <input type="number" name="response_hours" id="edit_response" required min="0" step="0.5" class="input" />
                    </div>
                    <div>
                        <label class="form-label form-label-required">Resolution (hours)</label>
                        <input type="number" name="resolution_hours" id="edit_resolution" required min="0" step="0.5" class="input" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="escalation_enabled" id="edit_escalation" value="1" class="w-4 h-4 text-primary-500 border-slate-300 dark:text-primary-400 dark:border-slate-600 rounded focus:ring-primary-500/20" />
                    <label class="text-sm text-slate-700 dark:text-slate-300">Enable Escalation</label>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Settings
    </a>
</div>

<script>
function openEditModal(id, name, priority, response, resolution, escalation) {
    document.getElementById('editForm').action = '/settings/sla-policies/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_priority').value = priority;
    document.getElementById('edit_response').value = response;
    document.getElementById('edit_resolution').value = resolution;
    document.getElementById('edit_escalation').checked = escalation === true;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endsection
