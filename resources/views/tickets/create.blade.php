@extends('layouts.app')

@section('title', 'Create Ticket - MITO IT Helpdesk')

@push('skeleton')
<x-loading />
@endpush

@section('content')
<div class="space-y-6">
    {{-- PAGE HEADER --}}
    <x-page-header title="Create New Ticket" description="Submit a new support request and we'll get back to you shortly">
        @slot('actions')
            <a href="{{ route('tickets.index') }}" class="btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Tickets
            </a>
        @endslot
    </x-page-header>

        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-6" id="createTicketForm">
            @csrf

            {{-- Classification --}}
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Classification</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Help us route your request to the right team</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="category_id" class="form-label form-label-required">Category</label>
                            <select name="category_id" id="category_id" required class="select">
                                <option value="">Select category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sub_category_id" class="form-label">Sub Category</label>
                            <select name="sub_category_id" id="sub_category_id" class="select">
                                <option value="">Select sub-category...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            @php $role = auth()->user()->role?->slug ?? 'user'; @endphp
            @if(in_array($role, ['admin', 'manager', 'staff']))
            {{-- Priority & Assignment --}}
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Priority & Assignment</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Set urgency and assign to team member</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label for="priority" class="form-label form-label-required">Priority</label>
                            <select name="priority" id="priority" required class="select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                            @error('priority')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sla_policy_id" class="form-label">SLA Policy</label>
                            <select name="sla_policy_id" id="sla_policy_id" class="select">
                                <option value="">Auto-assign</option>
                                @foreach($slaPolicies ?? [] as $sla)
                                    <option value="{{ $sla->id }}" {{ old('sla_policy_id') == $sla->id ? 'selected' : '' }}>
                                        {{ $sla->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="assignee_id" class="form-label">Assign To</label>
                            <select name="assignee_id" id="assignee_id" class="select">
                                <option value="">Unassigned</option>
                                @foreach($staffUsers ?? [] as $staff)
                                    <option value="{{ $staff->id }}" {{ old('assignee_id') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Description --}}
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Description</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Provide as much detail as possible</p>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="form-label form-label-required">Description</label>
                        <textarea name="description" id="description" rows="6" required class="textarea"
                                  placeholder="Describe your issue in detail. Include steps to reproduce, error messages, and any relevant information...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Attachments --}}
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Attachments</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Upload screenshots, logs, or relevant files</p>
                        </div>
                    </div>

                    <label class="form-label">Files</label>
                    <div class="mt-2">
                        <div id="dropZone" class="relative flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer transition-all duration-200 hover:border-primary-300 hover:bg-primary-50/20 dark:hover:border-primary-600 dark:hover:bg-primary-500/10 bg-slate-50/50 dark:bg-slate-900/50" role="button" tabindex="0">
                            <div class="flex flex-col items-center justify-center pt-6 pb-6 px-4 pointer-events-none">
                                <div class="w-12 h-12 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center mb-3 shadow-sm">
                                    <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Drop files here or click to upload</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1.5">Maximum 10MB per file. PNG, JPG, PDF, ZIP supported.</p>
                            </div>
                            <input type="file" name="attachments[]" multiple class="hidden" id="attachments" accept=".png,.jpg,.jpeg,.pdf,.zip,image/png,image/jpeg,application/pdf,application/zip">
                        </div>
                        <div id="uploadError" class="mt-2 hidden"></div>
                    </div>

                    {{-- File preview list --}}
                    <div id="filePreviewList" class="mt-3 space-y-2 hidden"></div>

                    @error('attachments')
                        <p class="form-error mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-2 pb-6">
                <p class="text-xs text-slate-400 dark:text-slate-500">Fields marked with <span class="text-red-500">*</span> are required</p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('tickets.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Submit Ticket
                    </button>
                </div>
            </div>

        </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subCategorySelect = document.getElementById('sub_category_id');
    const prioritySelect = document.getElementById('priority');
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('attachments');
    const filePreviewList = document.getElementById('filePreviewList');

    const categoryData = {!! json_encode($categories->map(fn($c) => [
        'id' => $c->id,
        'default_priority' => $c->default_priority,
        'subcategories' => $c->subCategories->map(fn($sc) => ['id' => $sc->id, 'name' => $sc->name])
    ])) !!};

    const oldCategoryId = '{{ old("category_id") }}';
    const oldSubCategoryId = '{{ old("sub_category_id") }}';

    function loadSubCategories(categoryId) {
        subCategorySelect.innerHTML = '<option value="">Select sub-category...</option>';
        const category = categoryData.find(c => c.id == categoryId);
        if (category && category.subcategories.length > 0) {
            category.subcategories.forEach(function(sc) {
                const option = document.createElement('option');
                option.value = sc.id;
                option.textContent = sc.name;
                if (sc.id == oldSubCategoryId) option.selected = true;
                subCategorySelect.appendChild(option);
            });
        }
    }

    function updatePriority(categoryId) {
        // The priority field is only rendered for support roles — guard the
        // case where the current user is an employee and the field is absent.
        if (!prioritySelect) return;
        const category = categoryData.find(c => c.id == categoryId);
        if (category && category.default_priority) {
            prioritySelect.value = category.default_priority;
        }
    }

    categorySelect.addEventListener('change', function() {
        loadSubCategories(this.value);
        updatePriority(this.value);
    });

    if (oldCategoryId) {
        loadSubCategories(oldCategoryId);
        updatePriority(oldCategoryId);
    }

    // Drag and drop styling for file upload
    if (dropZone && fileInput) {
        const MAX_FILE_SIZE = 10 * 1024 * 1024;
        const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'pdf', 'zip'];
        const ALLOWED_MIMES = ['image/png', 'image/jpeg', 'application/pdf', 'application/zip', 'application/x-zip-compressed'];
        const errorBox = document.getElementById('uploadError');

        function showError(message) {
            if (!errorBox) return;
            errorBox.innerHTML = '<p class="text-xs text-danger-600 dark:text-danger-400 flex items-start gap-1.5"><svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>' + escapeHtml(message) + '</span></p>';
            errorBox.classList.remove('hidden');
        }

        function clearError() {
            if (errorBox) errorBox.classList.add('hidden');
        }

        function isValidFile(file) {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (!ALLOWED_EXTENSIONS.includes(ext)) {
                showError('"' + file.name + '" has an unsupported type. Only PNG, JPG, PDF and ZIP files are allowed.');
                return false;
            }
            if (file.size > MAX_FILE_SIZE) {
                showError('"' + file.name + '" exceeds the 10MB size limit.');
                return false;
            }
            return true;
        }

        function updateFileInput() {
            const dt = new DataTransfer();
            const accepted = [];
            Array.from(fileInput.files).forEach(f => {
                if (isValidFile(f)) accepted.push(f);
            });
            accepted.forEach(f => dt.items.add(f));
            fileInput.files = dt.files;
        }

        function addFiles(fileList) {
            const incoming = Array.from(fileList);
            const existing = Array.from(fileInput.files);
            const names = new Set(existing.map(f => f.name));
            const merged = existing.slice();

            incoming.forEach(file => {
                if (names.has(file.name)) return;
                if (!isValidFile(file)) return;
                names.add(file.name);
                merged.push(file);
            });

            const dt = new DataTransfer();
            merged.forEach(f => dt.items.add(f));
            fileInput.files = dt.files;
            clearError();
            renderPreview();
        }

        function renderPreview() {
            const files = Array.from(fileInput.files);
            if (files.length === 0) {
                filePreviewList.classList.add('hidden');
                filePreviewList.innerHTML = '';
                return;
            }

            filePreviewList.innerHTML = '';
            filePreviewList.classList.remove('hidden');

            files.forEach(file => {
                const item = document.createElement('div');
                item.className = 'flex items-center gap-3 px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm animate-slide-up';

                const icon = document.createElement('div');
                icon.className = 'w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center flex-shrink-0';
                icon.innerHTML = '<svg class="w-4 h-4 text-slate-500 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';

                const info = document.createElement('div');
                info.className = 'flex-1 min-w-0';
                info.innerHTML = '<p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">' + escapeHtml(file.name) + '</p>' +
                    '<p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">' + formatFileSize(file.size) + '</p>';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'text-slate-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10';
                removeBtn.title = 'Remove file';
                removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                removeBtn.addEventListener('click', () => {
                    const dt = new DataTransfer();
                    Array.from(fileInput.files).forEach(f => {
                        if (f !== file) dt.items.add(f);
                    });
                    fileInput.files = dt.files;
                    clearError();
                    renderPreview();
                });

                item.appendChild(icon);
                item.appendChild(info);
                item.appendChild(removeBtn);
                filePreviewList.appendChild(item);
            });
        }

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-primary-400', 'bg-primary-50/40', 'scale-[1.01]');
                dropZone.classList.remove('border-slate-200', 'bg-slate-50/50');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-primary-400', 'bg-primary-50/40', 'scale-[1.01]');
                dropZone.classList.add('border-slate-200', 'bg-slate-50/50');
            });
        });

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                fileInput.click();
            }
        });

        dropZone.addEventListener('drop', (e) => {
            addFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            clearError();
            updateFileInput();
            renderPreview();
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
});
</script>
@endpush
