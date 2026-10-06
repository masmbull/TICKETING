@extends('layouts.app')

@section('title', 'Create Ticket - MITO IT Helpdesk')

@section('content')
<div class="space-y-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('tickets.index') }}" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Create New Ticket</h1>
    </div>

    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-4" x-data="createTicketForm()" @submit="validateRequestor($event)">
        @csrf
<script>
function fetchSubcategories(categoryId) {
    const subSelect = document.querySelector('select[name=sub_category_id]');
    if (!categoryId) {
        subSelect.innerHTML = '<option value="">Select subcategory...</option>';
        return;
    }
    fetch(`/api/subcategories?category_id=${categoryId}`)
        .then(res => res.json())
        .then(data => {
            subSelect.innerHTML = '<option value="">Select subcategory...</option>';
            data.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                subSelect.appendChild(opt);
            });
        })
        .catch(() => {
            subSelect.innerHTML = '<option value="">Error loading subcategories</option>';
        });
}
</script>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Category</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Category *</label>
                        <select name="category_id" required data-tour="ticket-category" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white" @change="fetchSubcategories($event.target.value)">
                            <option value="">Select category...</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Subcategory</label>
                        <select name="sub_category_id" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                            <option value="">Select subcategory...</option>
                        </select>
                    </div>
                </div>
            </div>

            @php $role = auth()->user()->role?->slug ?? 'user'; $isSupport = in_array($role, ['admin', 'manager', 'staff']); @endphp
            @if($isSupport)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Priority & SLA</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Priority *</label>
                        <select name="priority" required class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                            <option value="">Select priority...</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">SLA Policy (Optional)</label>
                        <select name="sla_policy_id" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                            <option value="">Auto-mapping or Default</option>
                            @foreach($slaPolicies ?? [] as $policy)
                            <option value="{{ $policy->id }}" {{ old('sla_policy_id') == $policy->id ? 'selected' : '' }}>{{ $policy->name }} ({{ ucfirst($policy->priority) }}, {{ $policy->resolution_days }} days)</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Leave blank to use category mapping or default (Low)</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Assignment & Requestor</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Requestor *</label>
                        <div class="relative">
                            <input
                                type="text"
                                data-requestor-input
                                x-model="requestorSearch"
                                @focus="requestorOpen = true"
                                @input="requestorOpen = true; if (selectedRequestor) { selectedRequestor = null; }"
                                @keydown.escape="requestorOpen = false"
                                @keydown.arrow-down.prevent="requestorHighlightedIndex = Math.min(requestorHighlightedIndex + 1, filteredRequestors.length - 1)"
                                @keydown.arrow-up.prevent="requestorHighlightedIndex = Math.max(requestorHighlightedIndex - 1, -1)"
                                @keydown.enter.prevent="selectRequestor(filteredRequestors[requestorHighlightedIndex])"
                                placeholder="Search requestor..."
                                class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E30613]/50">
                            
                            <button
                                type="button"
                                x-show="selectedRequestor"
                                @click="clearRequestor"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            
                            <input type="hidden" name="user_id" :value="selectedRequestor?.id || ''">
                            <p x-show="requestorError" x-cloak class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">Please select a requestor before submitting.</p>
                            
                            <div
                                x-show="requestorOpen"
                                @click.outside="requestorOpen = false"
                                x-transition
                                class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto"
                                style="display: none;">
                                <template x-if="filteredRequestors.length === 0">
                                    <div class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">No requestor found</div>
                                </template>
                                <template x-for="(requestor, index) in filteredRequestors" :key="requestor.id">
                                    <button
                                        type="button"
                                        @click="selectRequestor(requestor)"
                                        @mouseover="requestorHighlightedIndex = index"
                                        :class="{
                                            'bg-slate-100 dark:bg-slate-700/50': requestorHighlightedIndex === index,
                                            'bg-white dark:bg-slate-800': requestorHighlightedIndex !== index
                                        }"
                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                                        <div class="font-medium text-slate-900 dark:text-white" x-text="requestor.name"></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400" x-text="requestor.email"></div>
                                    </button>
                                </template>
                            </div>
                        </div>
                        @error('user_id')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                        <p class="text-xs text-slate-500 mt-1">Who is requesting this support?</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Assignee</label>
                        <select name="assignee_id" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                            <option value="">Unassigned</option>
                            @foreach($staffUsers ?? [] as $staff)
                            <option value="{{ $staff->id }}" {{ old('assignee_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">SLA</h2>
                <div class="space-y-3">
                    <p class="text-sm text-slate-600 dark:text-slate-300">SLA will be automatically assigned based on the category you select.</p>
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Description</h2>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Describe your issue *</label>
                <textarea name="description" rows="5" required data-tour="ticket-description" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400" placeholder="Describe your issue in detail...">{{ old('description') }}</textarea>
                @error('description')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Attachments</h2>
            <div class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors"
                 :class="dragOver ? 'border-[#E30613] bg-[#E30613]/5' : 'border-slate-200 dark:border-slate-600 hover:border-[#E30613]'"
                 @click="$refs.attachments.click()"
                 @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)">
                <svg class="w-8 h-8 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                <p class="text-sm text-slate-500 mt-2">Drop files or click to upload</p>
                <p class="text-xs text-slate-400 mt-1">PNG, JPG, PDF, ZIP (max 10MB)</p>
                <input type="file" name="attachments[]" multiple id="attachments" class="hidden" x-ref="attachments"
                       accept=".png,.jpg,.jpeg,.pdf,.zip,image/png,image/jpeg,application/pdf,application/zip"
                       @change="syncFiles()">
            </div>

            <template x-if="fileNames.length > 0">
                <div class="mt-3 space-y-2">
                    <template x-for="(name, idx) in fileNames" :key="idx">
                        <div class="flex items-center gap-3 px-3 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                            <div class="w-8 h-8 rounded bg-[#E30613]/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#E30613]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </div>
                            <span class="text-sm text-slate-700 dark:text-slate-300 truncate flex-1" x-text="name"></span>
                            <button type="button" @click="removeFile(idx)" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </template>
            <p x-show="fileError" x-cloak class="mt-2 text-xs text-red-500" aria-live="polite" x-text="fileError"></p>
        </div>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-500">* Required fields</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('tickets.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</a>
                <button type="submit" data-tour="ticket-submit" class="px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg">Submit Ticket</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function createTicketForm() {
    return {
        allRequestors: @js($users ?? []),
        isSupport: @json(auth()->user()->canManageTickets()),
        requestorSearch: '',
        requestorOpen: false,
        selectedRequestor: null,
        requestorHighlightedIndex: -1,
        requestorError: false,
        fileNames: [],
        fileError: '',
        dragOver: false,

        // Frontend whitelist mirrors the backend mimes:png,jpg,jpeg,pdf,zip rule.
        isAllowed(file) {
            return ['png', 'jpg', 'jpeg', 'pdf', 'zip'].includes(file.name.split('.').pop().toLowerCase());
        },

        applyFiles(fileList) {
            const ok = Array.from(fileList).filter(f => this.isAllowed(f));
            this.fileError = ok.length < fileList.length
                ? 'Unsupported file type. Allowed: PNG, JPG, PDF, ZIP.'
                : '';
            const dt = new DataTransfer();
            ok.forEach(f => dt.items.add(f));
            this.$refs.attachments.files = dt.files;
            this.fileNames = ok.map(f => f.name);
        },

        syncFiles() {
            this.applyFiles(this.$refs.attachments.files);
        },

        handleDrop(e) {
            this.dragOver = false;
            const dt = e.dataTransfer;
            if (!dt.files.length) return;
            const merged = new DataTransfer();
            Array.from(this.$refs.attachments.files).forEach(f => merged.items.add(f));
            Array.from(dt.files).forEach(f => merged.items.add(f));
            this.applyFiles(merged.files);
        },

        removeFile(idx) {
            const input = this.$refs.attachments;
            const dt = new DataTransfer();
            Array.from(input.files).filter((_, i) => i !== idx).forEach(f => dt.items.add(f));
            input.files = dt.files;
            this.syncFiles();
        },

        validateRequestor(event) {
            if (this.isSupport && !this.selectedRequestor) {
                this.requestorError = true;
                event.preventDefault();
                document.querySelector('[data-requestor-input]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            this.requestorError = false;
            return true;
        },
        
        get filteredRequestors() {
            if (!this.requestorSearch.trim()) {
                return this.allRequestors;
            }
            const search = this.requestorSearch.toLowerCase();
            return this.allRequestors.filter(user =>
                user.name.toLowerCase().includes(search) ||
                user.email.toLowerCase().includes(search) ||
                (user.job_title && user.job_title.toLowerCase().includes(search))
            );
        },
        
        selectRequestor(requestor) {
            if (!requestor) return;
            this.selectedRequestor = requestor;
            this.requestorSearch = requestor.name + ' (' + requestor.email + ')';
            this.requestorOpen = false;
            this.requestorHighlightedIndex = -1;
            this.requestorError = false;
        },
        
        clearRequestor() {
            this.selectedRequestor = null;
            this.requestorSearch = '';
            this.requestorOpen = false;
            this.requestorHighlightedIndex = -1;
        },
        
        clearRequestor() {
            this.selectedRequestor = null;
            this.requestorSearch = '';
            this.requestorOpen = false;
            this.requestorHighlightedIndex = -1;
        },
        
        handleInput(event) {
            if (this.selectedRequestor) {
                this.selectedRequestor = null;
            }
        }
    };
}
</script>

@if(session('ticket_created'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.MITO.toast('Ticket Created!', '{{ session("ticket_created") }} created successfully.', 'success');
    });
</script>
@endif
@endpush
