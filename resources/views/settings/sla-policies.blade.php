@extends('layouts.app')

@section('title', 'SLA Policies - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">SLA Policies</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage service level agreements and category mappings</p>
        </div>
    </div>

    {{-- SLA Levels --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SLA Levels</h2>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors">New Policy</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">SLA Level</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Resolution Time</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($policies as $policy)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $policy->priority }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $policy->resolution_days ?? '—' }} Day{{ ($policy->resolution_days ?? 0) != 1 ? 's' : '' }}</td>
                        <td class="px-4 py-3">
                            @if($policy->is_active)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-200 dark:border-emerald-900/30">Active</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-700 dark:text-slate-400 dark:border-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button onclick="openEditModal({{ $policy->id }}, '{{ $policy->name }}', '{{ $policy->priority }}', {{ $policy->resolution_days ?? 0 }})"
                                    class="text-xs text-[#E30613] hover:text-[#c4050f] font-medium">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">No SLA policies defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Category → SLA Mapping --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category → SLA Mapping</h2>
            <button onclick="document.getElementById('mappingModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors">+ Add Mapping</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Category</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Subcategory</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">SLA Level</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Resolution</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($mappings as $mapping)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-4 py-3 text-slate-900 dark:text-white font-medium">{{ $mapping->category->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $mapping->subCategory->name ?? '— (all)' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ ucfirst($mapping->priority) }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $mapping->policy->resolution_days ?? '—' }} Day{{ ($mapping->policy->resolution_days ?? 0) != 1 ? 's' : '' }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('sla-policies.mapping.destroy', $mapping->id) }}" class="inline" x-data>
                                @csrf @method('DELETE')
                                <button type="submit" x-on:click.prevent="if(confirm('Delete this mapping?')) $el.closest('form').submit()" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">No SLA mappings configured. Tickets will have "No SLA" unless manually assigned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Settings
    </a>
</div>

{{-- Create Policy Modal --}}
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
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Priority Level *</label>
                <select name="priority" required class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Resolution Time (Days) *</label>
                <input type="number" name="resolution_days" required min="1" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white" placeholder="e.g. 3">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors">Create</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Policy Modal --}}
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
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Priority Level *</label>
                <select name="priority" id="edit_priority" required class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Resolution Time (Days) *</label>
                <input type="number" name="resolution_days" id="edit_resolution" required min="1" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Mapping Modal --}}
<div id="mappingModal" class="hidden fixed inset-0 bg-slate-900/50 dark:bg-slate-950/80 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Add SLA Mapping</h3>
            <button onclick="document.getElementById('mappingModal').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-slate-500 dark:hover:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('sla-policies.mapping.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Category *</label>
                <select name="category_id" required x-data x-on:change="$dispatch('category-changed', $event.target.value)" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">Select category…</option>
                    @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Subcategory (optional — blank = all)</label>
                <select name="sub_category_id"
                        x-data="{ subs: [] }"
                        @category-changed.window="
                            const catId = $event.detail;
                            if (!catId) { subs = []; return; }
                            fetch('/api/categories/' + catId + '/subcategories')
                                .then(r => r.json())
                                .then(d => subs = d)
                                .catch(() => subs = []);
                        "
                        class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">All subcategories</option>
                    <template x-for="sub in subs" :key="sub.id">
                        <option :value="sub.id" x-text="sub.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">SLA Level *</label>
                <select name="priority" required class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    <option value="">Select level…</option>
                    @foreach($policies as $policy)
                    <option value="{{ $policy->priority }}">{{ ucfirst($policy->priority) }} ({{ $policy->resolution_days ?? '—' }} Days)</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('mappingModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#E30613] hover:bg-[#c4050f] rounded-lg transition-colors">Add Mapping</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, priority, resolution) {
    document.getElementById('editForm').action = '/settings/sla-policies/' + id;
    document.getElementById('edit_priority').value = priority;
    document.getElementById('edit_resolution').value = resolution;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endsection
