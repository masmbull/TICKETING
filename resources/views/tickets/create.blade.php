@extends('layouts.app')

@section('title', 'Create Ticket - MITO IT Helpdesk')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Support Ticket</h1>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-5 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        @csrf

        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category_id" id="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="">-- Select Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="sub_category_id" class="block text-sm font-medium text-gray-700 mb-1">Sub Category</label>
            <select name="sub_category_id" id="sub_category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" disabled>
                <option value="">-- Select Sub Category --</option>
            </select>
        </div>

        <div>
            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Brief description of the issue">
        </div>

        <div>
            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
            <select name="priority" id="priority" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <textarea name="description" id="description" rows="5" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Detailed description of the issue">{{ old('description') }}</textarea>
        </div>

        @if($agents->count() > 0)
        <div>
            <label for="assignee_id" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
            <select name="assignee_id" id="assignee_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="">-- Auto-assign --</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Attachments</label>
            <input type="file" name="attachments[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="mt-1 text-xs text-gray-400">Max 10MB per file. You can select multiple files.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('tickets.index') }}" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Create Ticket</button>
        </div>
    </form>
</div>

<script>
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    const subSelect = document.getElementById('sub_category_id');
    const prioritySelect = document.getElementById('priority');

    if (!categoryId) {
        subSelect.innerHTML = '<option value="">-- Select Sub Category --</option>';
        subSelect.disabled = true;
        return;
    }

    // Fetch subcategories
    fetch('{{ route("api.subcategories") }}?category_id=' + categoryId)
        .then(r => r.json())
        .then(data => {
            subSelect.innerHTML = '<option value="">-- Select Sub Category --</option>';
            data.forEach(sub => {
                subSelect.innerHTML += '<option value="' + sub.id + '">' + sub.name + '</option>';
            });
            subSelect.disabled = data.length === 0;
        });

    // Fetch default priority
    fetch('{{ route("api.category-priority") }}?category_id=' + categoryId)
        .then(r => r.json())
        .then(data => {
            if (data.default_priority) {
                prioritySelect.value = data.default_priority;
            }
        });
});
</script>
@endsection