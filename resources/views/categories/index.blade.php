@extends('layouts.app')

@section('title', 'Category Management - MITO IT Helpdesk')

@push('skeleton')
<x-loading variant="cards" :count="3" />
@endpush

@section('content')
<div class="space-y-6">
    <x-page-header title="Category Management" description="Manage ticket categories and subcategories" />


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Add Category Form --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">New Category</h2>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="cat_name" class="form-label form-label-required">Category Name</label>
                            <input type="text" id="cat_name" name="name" required class="input" placeholder="e.g., Hardware" />
                        </div>
                        <div>
                            <label for="cat_description" class="form-label">Description</label>
                            <textarea id="cat_description" name="description" rows="2" class="textarea" placeholder="Brief description"></textarea>
                        </div>
                        <button type="submit" class="btn-primary w-full">Create Category</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">New Subcategory</h2>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('subcategories.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="sub_category_id" class="form-label form-label-required">Parent Category</label>
                            <select id="sub_category_id" name="category_id" required class="select">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sub_name" class="form-label form-label-required">Subcategory Name</label>
                            <input type="text" id="sub_name" name="name" required class="input" placeholder="e.g., Laptop" />
                        </div>
                        <div>
                            <label for="sub_description" class="form-label">Description</label>
                            <textarea id="sub_description" name="description" rows="2" class="textarea" placeholder="Brief description"></textarea>
                        </div>
                        <button type="submit" class="btn-primary w-full">Create Subcategory</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Category List --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">All Categories</h2>
                </div>

                @forelse($categories as $category)
                <div class="border-b border-slate-100 dark:border-slate-800 last:border-b-0">
                    <div class="px-5 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $category->is_active ? 'bg-success-500' : 'bg-slate-300' }}"></div>
                            <div>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $category->name }}</span>
                                <span class="text-xs text-slate-400 dark:text-slate-500 ml-2">{{ $category->slug }}</span>
                                @if($category->description)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $category->subCategories->count() }} subcategories</span>
                            <span class="inline-flex items-center {{ $category->is_active ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }} text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <button onclick="openEditCategoryModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}', {{ $category->is_active ? 'true' : 'false' }})" class="btn-primary btn-sm px-2 py-1 text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button x-data="{ show: false }" x-on:click="show = true" class="btn-danger btn-sm px-2 py-1 text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            <div x-show="show" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50" x-cloak>
                                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete Category</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Are you sure you want to delete "{{ $category->name }}"? This action cannot be undone.</p>
                                    <form method="POST" action="{{ route('categories.destroy', $category->id) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <div class="flex justify-end gap-3">
                                            <button type="button" class="btn-outline" x-on:click="show = false">Cancel</button>
                                            <button type="submit" class="btn-danger">Delete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Subcategories --}}
                    @if($category->subCategories->count() > 0)
                    <div class="bg-slate-50/50 px-5 py-3 ml-6 border-l-2 border-slate-200 dark:bg-slate-900/50 dark:border-slate-700">
                        <div class="space-y-2">
                            @foreach($category->subCategories as $sub)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $sub->is_active ? 'bg-success-400' : 'bg-slate-300' }}"></div>
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $sub->name }}</span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $sub->slug }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="inline-flex items-center {{ $sub->is_active ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }} text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <button onclick="openEditSubcategoryModal({{ $sub->id }}, '{{ $sub->name }}', {{ $sub->is_active ? 'true' : 'false' }})" class="btn-primary btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button x-data="{ show: false }" x-on:click="show = true" class="btn-danger btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    <div x-show="show" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50" x-cloak>
                                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6">
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete Subcategory</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Are you sure you want to delete "{{ $sub->name }}"? This action cannot be undone.</p>
                                            <form method="POST" action="{{ route('subcategories.destroy', $sub->id) }}" class="inline">
                                                @csrf @method('DELETE')
                                                <div class="flex justify-end gap-3">
                                                    <button type="button" class="btn-outline" x-on:click="show = false">Cancel</button>
                                                    <button type="submit" class="btn-danger">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="bg-slate-50/50 px-5 py-3 ml-6 border-l-2 border-slate-200 dark:bg-slate-900/50 dark:border-slate-700">
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic">No subcategories yet</p>
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M4 7h.01M4 11h.01M4 15h.01M4 19h.01"/></svg>
                    <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">No categories yet</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Create your first category to get started.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
{{-- Edit Category Modal --}}
<div id="editCategoryModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Category</h3>
            <button onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-slate-500 dark:hover:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editCategoryForm" method="POST" class="p-5 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="form-label form-label-required">Name</label>
                <input type="text" name="name" id="edit_category_name" required class="input" />
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" id="edit_category_description" rows="2" class="textarea"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit_category_active" value="1" class="w-4 h-4 text-primary-500 border-slate-300 rounded focus:ring-primary-500/20" />
                <label class="text-sm text-slate-700 dark:text-slate-300">Active</label>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Subcategory Modal --}}
<div id="editSubcategoryModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Subcategory</h3>
            <button onclick="document.getElementById('editSubcategoryModal').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-slate-500 dark:hover:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editSubcategoryForm" method="POST" class="p-5 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="form-label form-label-required">Name</label>
                <input type="text" name="name" id="edit_subcategory_name" required class="input" />
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit_subcategory_active" value="1" class="w-4 h-4 text-primary-500 border-slate-300 rounded focus:ring-primary-500/20" />
                <label class="text-sm text-slate-700 dark:text-slate-300">Active</label>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('editSubcategoryModal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCategoryModal(id, name, description, isActive) {
    document.getElementById('editCategoryForm').action = '/categories/' + id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_category_description').value = description || '';
    document.getElementById('edit_category_active').checked = isActive === true;
    document.getElementById('editCategoryModal').classList.remove('hidden');
}
function openEditSubcategoryModal(id, name, isActive) {
    document.getElementById('editSubcategoryForm').action = '/subcategories/' + id;
    document.getElementById('edit_subcategory_name').value = name;
    document.getElementById('edit_subcategory_active').checked = isActive === true;
    document.getElementById('editSubcategoryModal').classList.remove('hidden');
}
</script>
@endsection
