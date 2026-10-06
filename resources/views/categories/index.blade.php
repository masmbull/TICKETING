@extends('layouts.app')

@section('title', 'Categories - MITO IT Helpdesk')

@php
$categoryData = $categories->map(fn($c) => [
    'id' => $c->id,
    'name' => $c->name,
    'slug' => $c->slug,
    'description' => $c->description,
    'is_active' => (bool) $c->is_active,
    'subCategories' => $c->subCategories->map(fn($s) => [
        'id' => $s->id,
        'name' => $s->name,
        'slug' => $s->slug,
        'is_active' => (bool) $s->is_active,
    ])->values()->toArray(),
])->values()->toArray();
@endphp

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Categories</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage ticket categories</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">New Category</h2>
                <form method="POST" action="{{ route('categories.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <input type="text" name="name" required placeholder="Category name" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <textarea name="description" rows="2" placeholder="Description" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg">Create Category</button>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">New Subcategory</h2>
                <form method="POST" action="{{ route('subcategories.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <select name="category_id" required class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                            <option value="">Select category</option>
                            @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="name" required placeholder="Subcategory name" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                    </div>
                    <button type="submit" class="w-full py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg">Create Subcategory</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                 x-data="{
                     categories: {{ json_encode($categoryData) }},
                     search: '',
                     page: 1,
                     perPage: 5,
                     deleteTarget: null,
                     askDelete(type, item) { this.deleteTarget = { type, id: item.id, name: item.name }; },
                     get filtered() {
                         const q = this.search.toLowerCase();
                         if (!q) return this.categories;
                         return this.categories.filter(c => c.name.toLowerCase().includes(q) || (c.description && c.description.toLowerCase().includes(q)));
                     },
                     get totalPages() { return Math.ceil(this.filtered.length / this.perPage) || 1; },
                     get paginated() {
                         const start = (this.page - 1) * this.perPage;
                         return this.filtered.slice(start, start + this.perPage);
                     }
                 }">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                    <input type="text" x-model="search" placeholder="Search categories..." class="flex-1 px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                </div>

                <template x-for="cat in paginated" :key="cat.id">
                    <div class="border-b border-slate-100 dark:border-slate-700">
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full" :class="cat.is_active ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white" x-text="cat.name"></p>
                                    <p class="text-xs text-slate-500" x-text="cat.slug"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-1 rounded" :class="cat.is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-100 text-slate-500'" x-text="cat.is_active ? 'Active' : 'Inactive'"></span>
                                <button @click="openEditCat(cat)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button" @click="askDelete('category', cat)" class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                        <div x-show="cat.subCategories.length > 0" class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 ml-6 border-l-2 border-slate-200 dark:border-slate-700 space-y-1">
                            <template x-for="sub in cat.subCategories" :key="sub.id">
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="sub.is_active ? 'bg-emerald-400' : 'bg-slate-300'"></div>
                                        <span class="text-xs text-slate-600 dark:text-slate-300" x-text="sub.name"></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="openEditSub(sub)" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600">
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" @click="askDelete('subcategory', sub)" class="p-1 rounded hover:bg-red-50">
                                            <svg class="w-3 h-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs text-slate-500" x-text="filtered.length + ' categories'"></span>
                    <div class="flex items-center gap-1">
                        <button @click="page--" :disabled="page === 1" class="px-2 py-1 text-xs rounded" :class="page === 1 ? 'text-slate-300' : 'text-slate-600 hover:bg-slate-100'">Previous</button>
                        <button @click="page++" :disabled="page >= totalPages" class="px-2 py-1 text-xs rounded" :class="page >= totalPages ? 'text-slate-300' : 'text-slate-600 hover:bg-slate-100'">Next</button>
                    </div>
                </div>
{{-- Shared delete confirmation (category + subcategory) --}}
        <template x-teleport="body">
            <div x-show="deleteTarget" x-cloak
                 x-on:keydown.escape.window="deleteTarget = null"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                 role="dialog" aria-modal="true" aria-labelledby="delete-cat-title">
                <div x-show="deleteTarget"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" x-on:click="deleteTarget = null"></div>
                <div x-show="deleteTarget"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-[calc(100vw-2rem)] max-w-sm rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl p-6 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10 text-red-500" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16m-9-4v4m2-4v4"/></svg>
                    </div>
                    <h3 id="delete-cat-title" class="text-lg font-semibold text-slate-900 dark:text-white">
                        Delete <span x-text="deleteTarget?.type === 'subcategory' ? 'Subcategory' : 'Category'"></span>?
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Are you sure you want to delete
                        <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="deleteTarget?.name"></span>?
                    </p>
                    <p x-show="deleteTarget?.type === 'category'" class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Its subcategories may also be removed.
                    </p>
                    <form method="POST" :action="deleteTarget ? (deleteTarget.type === 'subcategory' ? '/subcategories/' + deleteTarget.id : '/categories/' + deleteTarget.id) : '#'">
                        @csrf @method('DELETE')
                        <div class="mt-6 flex gap-3">
                            <button type="button" x-on:click="deleteTarget = null"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 dark:focus-visible:ring-slate-500 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 text-sm font-medium bg-[#E30613] hover:bg-[#c4050f] text-white rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#E30613] dark:focus-visible:ring-offset-slate-800 transition">
                                Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
            </div>
        </div>
    </div>
</div>

<div id="editCatModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Edit Category</h3>
        <form id="editCatForm" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <input type="text" name="name" id="editCatName" required class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg">
            <textarea name="description" id="editCatDesc" rows="2" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg"></textarea>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="editCatActive" value="1" class="w-4 h-4 rounded">
                <span class="text-sm text-slate-600 dark:text-slate-300">Active</span>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editCatModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-[#E30613] text-white rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="editSubModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Edit Subcategory</h3>
        <form id="editSubForm" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <input type="text" name="name" id="editSubName" required class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="editSubActive" value="1" class="w-4 h-4 rounded">
                <span class="text-sm text-slate-600 dark:text-slate-300">Active</span>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editSubModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-[#E30613] text-white rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCat(cat) {
    document.getElementById('editCatForm').action = '/categories/' + cat.id;
    document.getElementById('editCatName').value = cat.name;
    document.getElementById('editCatDesc').value = cat.description || '';
    document.getElementById('editCatActive').checked = cat.is_active;
    document.getElementById('editCatModal').classList.remove('hidden');
}
function openEditSub(sub) {
    document.getElementById('editSubForm').action = '/subcategories/' + sub.id;
    document.getElementById('editSubName').value = sub.name;
    document.getElementById('editSubActive').checked = sub.is_active;
    document.getElementById('editSubModal').classList.remove('hidden');
}
</script>
@endsection
