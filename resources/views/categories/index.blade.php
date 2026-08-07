@extends('layouts.app')

@section('title', 'Category Management - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Category Management</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage ticket categories and subcategories</p>
    </div>

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
                                <span class="inline-flex items-center {{ $sub->is_active ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }} text-xs font-bold px-2 py-0.5 rounded-full">
                                    {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                </span>
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
@endsection
