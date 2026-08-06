@extends('layouts.app')

@section('title', 'Category Management - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Category Management</h1>
        <p class="mt-1 text-sm text-gray-500">Manage ticket categories and subcategories</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Category Form -->
        <div class="lg:col-span-1 space-y-6">
            <!-- New Category -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">New Category</h2>
                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="cat_name" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                            <input type="text" id="cat_name" name="name" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., Hardware">
                        </div>
                        <div>
                            <label for="cat_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="cat_description" name="description" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Create Category
                        </button>
                    </div>
                </form>
            </div>

            <!-- New Subcategory -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">New Subcategory</h2>
                <form method="POST" action="{{ route('subcategories.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="sub_category_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Category *</label>
                            <select id="sub_category_id" name="category_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sub_name" class="block text-sm font-medium text-gray-700 mb-1">Subcategory Name *</label>
                            <input type="text" id="sub_name" name="name" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., Laptop">
                        </div>
                        <div>
                            <label for="sub_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="sub_description" name="description" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Create Subcategory
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Category List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">All Categories</h2>
                </div>

                @forelse($categories as $category)
                <div class="border-b border-gray-100 last:border-b-0">
                    <!-- Category Header -->
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $category->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                            <div>
                                <span class="text-sm font-semibold text-gray-900">{{ $category->name }}</span>
                                <span class="text-xs text-gray-400 ml-2">{{ $category->slug }}</span>
                                @if($category->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">{{ $category->subCategories->count() }} subcategories</span>
                            <span class="inline-flex items-center {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} text-xs font-medium px-2 py-0.5 rounded-full">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <!-- Subcategories -->
                    @if($category->subCategories->count() > 0)
                    <div class="bg-gray-50 px-6 py-3 ml-6 border-l-2 border-gray-200">
                        <div class="space-y-2">
                            @foreach($category->subCategories as $sub)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $sub->is_active ? 'bg-green-400' : 'bg-gray-300' }}"></div>
                                    <span class="text-sm text-gray-700">{{ $sub->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $sub->slug }}</span>
                                </div>
                                <span class="inline-flex items-center {{ $sub->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} text-xs font-medium px-2 py-0.5 rounded-full">
                                    {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="bg-gray-50 px-6 py-3 ml-6 border-l-2 border-gray-200">
                        <p class="text-xs text-gray-400 italic">No subcategories yet</p>
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No categories yet</h3>
                    <p class="mt-2 text-sm text-gray-500">Create your first category to get started.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection