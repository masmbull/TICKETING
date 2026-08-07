@extends('layouts.app')

@section('title', 'Create Ticket - MITO IT Helpdesk')

@section('content')
<div class="min-h-screen bg-gray-50 -m-6 p-6">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('tickets.index') }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Create New Ticket</h1>
            <p class="text-sm text-gray-500 mt-0.5">Submit a new support request</p>
        </div>
    </div>

    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="max-w-3xl">
        @csrf

        <div class="space-y-6">
            {{-- Subject --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <label for="subject" class="block text-sm font-semibold text-gray-900 mb-2">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm"
                       placeholder="Brief description of your issue">
                @error('subject')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category & Sub Category --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-900 mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" id="category_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">Select category...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sub_category_id" class="block text-sm font-semibold text-gray-900 mb-2">Sub Category</label>
                        <select name="sub_category_id" id="sub_category_id"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">Select sub-category...</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Priority & Assignee --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-sm font-semibold text-gray-900 mb-2">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" id="priority" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>
                    @if((Auth::user()->isAdmin() || Auth::user()->isManager()) && isset($agents) && $agents->count() > 0)
                    <div>
                        <label for="assignee_id" class="block text-sm font-semibold text-gray-900 mb-2">Assign To</label>
                        <select name="assignee_id" id="assignee_id"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm">
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

            {{-- Description --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description <span class="text-red-500">*</span></label>
                <textarea name="description" id="description" rows="6" required
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm"
                          placeholder="Provide as much detail as possible...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Attachments --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Attachments</label>
                <div class="border-2 border-dashed border-gray-200 rounded-lg p-6 text-center hover:border-blue-300 transition-colors">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <p class="text-sm text-gray-600 mb-1">Drop files here or click to upload</p>
                    <p class="text-xs text-gray-400">Max 10MB per file</p>
                    <input type="file" name="attachments[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" style="position: relative;">
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Submit Ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
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

    const categoryData = @json($categories->map(fn($c) => [
        'id' => $c->id,
        'default_priority' => $c->default_priority,
        'subcategories' => $c->subCategories->map(fn($sc) => ['id' => $sc->id, 'name' => $sc->name])
    ]));

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
