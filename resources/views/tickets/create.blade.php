@extends('layouts.app')

@section('title', 'Create Ticket - MITO IT Helpdesk')

@section('content')
<div class="min-h-screen -m-6 p-6 lg:p-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('tickets.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">Create New Ticket</h1>
            <p class="text-sm text-slate-500 mt-0.5">Submit a new support request</p>
        </div>
    </div>

    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="max-w-7xl w-full">
        @csrf

        <div class="space-y-6">
            {{-- Subject --}}
            <div class="card">
                <div class="p-5">
                    <label for="subject" class="form-label form-label-required">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                           class="input"
                           placeholder="Brief description of your issue">
                    @error('subject')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Category & Sub Category --}}
            <div class="card">
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

            {{-- Priority & Assignee --}}
            <div class="card">
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="form-label form-label-required">Priority</label>
                            <select name="priority" id="priority" required class="select">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        @if((Auth::user()->isAdmin() || Auth::user()->isManager()) && isset($agents) && $agents->count() > 0)
                        <div>
                            <label for="assignee_id" class="form-label">Assign To</label>
                            <select name="assignee_id" id="assignee_id" class="select">
                                <option value="">Unassigned</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ old('assignee_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }} ({{ $agent->role->name ?? 'User' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="card">
                <div class="p-5">
                    <label for="description" class="form-label form-label-required">Description</label>
                    <textarea name="description" id="description" rows="6" required class="textarea"
                              placeholder="Provide as much detail as possible...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Attachments --}}
            <div class="card">
                <div class="p-5">
                    <label class="form-label">Attachments</label>
                    <div class="mt-2 flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 rounded-xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 transition-all duration-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <p class="text-sm text-slate-600 font-medium">Drop files here or click to upload</p>
                                <p class="text-xs text-slate-400 mt-1">Max 10MB per file</p>
                            </div>
                            <input type="file" name="attachments[]" multiple class="hidden" id="attachments">
                        </label>
                    </div>
                    @error('attachments')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Submit Ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subCategorySelect = document.getElementById('sub_category_id');
    const prioritySelect = document.getElementById('priority');

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
});
</script>
@endpush
