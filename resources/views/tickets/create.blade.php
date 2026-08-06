@extends('layouts.app')

@section('title', 'Create Ticket - MITO IT Helpdesk')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('tickets.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to My Tickets
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Create Support Ticket</h1>
        <p class="mt-1 text-sm text-gray-500">Describe your issue and we'll get it resolved</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('tickets.store') }}">
            @csrf
            <div class="space-y-5">
                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select id="category_id" name="category_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Subcategory -->
                <div>
                    <label for="sub_category_id" class="block text-sm font-medium text-gray-700 mb-1">Subcategory *</label>
                    <select id="sub_category_id" name="sub_category_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a category first</option>
                    </select>
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Brief summary of the issue">
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                    <div class="grid grid-cols-4 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="priority" value="low" {{ old('priority', 'low') === 'low' ? 'checked' : '' }} class="peer sr-only">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-300 transition-colors">
                                <div class="text-sm font-medium text-gray-700 peer-checked:text-green-700">Low</div>
                                <div class="text-xs text-gray-400 mt-0.5">Minor issue</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="priority" value="medium" {{ old('priority') === 'medium' ? 'checked' : '' }} class="peer sr-only">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:border-gray-300 transition-colors">
                                <div class="text-sm font-medium text-gray-700 peer-checked:text-yellow-700">Medium</div>
                                <div class="text-xs text-gray-400 mt-0.5">Affects work</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="priority" value="high" {{ old('priority') === 'high' ? 'checked' : '' }} class="peer sr-only">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-gray-300 transition-colors">
                                <div class="text-sm font-medium text-gray-700 peer-checked:text-orange-700">High</div>
                                <div class="text-xs text-gray-400 mt-0.5">Urgent</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="priority" value="critical" {{ old('priority') === 'critical' ? 'checked' : '' }} class="peer sr-only">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-gray-300 transition-colors">
                                <div class="text-sm font-medium text-gray-700 peer-checked:text-red-700">Critical</div>
                                <div class="text-xs text-gray-400 mt-0.5">System down</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea id="description" name="description" rows="6" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Please describe your issue in detail. Include:&#10;- What happened?&#10;- What did you expect?&#10;- Steps to reproduce (if applicable)&#10;- Any error messages">{{ old('description') }}</textarea>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('tickets.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Ticket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subCategorySelect = document.getElementById('sub_category_id');
    const categories = @json($categories);

    function updateSubCategories() {
        const categoryId = parseInt(categorySelect.value);
        subCategorySelect.innerHTML = '<option value="">Select a subcategory</option>';

        if (!categoryId) {
            subCategorySelect.innerHTML = '<option value="">Select a category first</option>';
            return;
        }

        const category = categories.find(c => c.id === categoryId);
        if (category && category.sub_categories && category.sub_categories.length > 0) {
            category.sub_categories.forEach(sub => {
                if (sub.is_active !== false) {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    if ('{{ old('sub_category_id') }}' == sub.id) option.selected = true;
                    subCategorySelect.appendChild(option);
                }
            });
        }
    }

    categorySelect.addEventListener('change', updateSubCategories);
    updateSubCategories();
});
</script>
@endpush
@endsection