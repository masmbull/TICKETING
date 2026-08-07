@extends('layouts.app')

@section('title', 'SLA Policies - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">SLA Policies</h1>
            <p class="mt-1 text-sm text-gray-500">Manage service level agreements for different priority levels</p>
        </div>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
            + New Policy
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($policies as $policy)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                        @match($policy->priority) {
                            @case('Critical') bg-red-100 text-red-800 @break
                            @case('High') bg-orange-100 text-orange-800 @break
                            @case('Medium') bg-yellow-100 text-yellow-800 @break
                            @default bg-green-100 text-green-800
                        }">{{ $policy->priority }}</span>
                    @if($policy->is_active)
                        <span class="w-2 h-2 rounded-full bg-green-400"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ $policy->name }}</h3>
                <div class="space-y-1 text-xs text-gray-500">
                    <div class="flex justify-between">
                        <span>Response:</span>
                        <span class="font-medium text-gray-700">{{ $policy->response_hours }}h</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Resolution:</span>
                        <span class="font-medium text-gray-700">{{ $policy->resolution_hours }}h</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Escalation:</span>
                        <span class="font-medium text-gray-700">{{ $policy->escalation_enabled ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button onclick="openEditModal({{ $policy->id }}, '{{ $policy->name }}', '{{ $policy->priority }}', {{ $policy->response_hours }}, {{ $policy->resolution_hours }}, {{ $policy->escalation_enabled ? 'true' : 'false' }})" class="text-xs text-blue-600 hover:text-blue-800">Edit</button>
                    <form method="POST" action="{{ route('sla-policies.destroy', $policy->id) }}" onsubmit="return confirm('Delete this policy?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">No SLA policies defined yet.</div>
        @endforelse
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Create SLA Policy</h3>
            <form method="POST" action="{{ route('sla-policies.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Critical Response">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="Critical">Critical</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Response (hours)</label>
                            <input type="number" name="response_hours" required min="0" step="0.5" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resolution (hours)</label>
                            <input type="number" name="resolution_hours" required min="0" step="0.5" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="escalation_enabled" value="1" class="h-4 w-4 text-blue-600 rounded">
                        <label class="ml-2 text-sm text-gray-700">Enable Escalation</label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Edit SLA Policy</h3>
            <form id="editForm" method="POST">
            @csrf @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" id="edit_priority" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="Critical">Critical</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Response (hours)</label>
                            <input type="number" name="response_hours" id="edit_response" required min="0" step="0.5" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resolution (hours)</label>
                            <input type="number" name="resolution_hours" id="edit_resolution" required min="0" step="0.5" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="escalation_enabled" id="edit_escalation" value="1" class="h-4 w-4 text-blue-600 rounded">
                        <label class="ml-2 text-sm text-gray-700">Enable Escalation</label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
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