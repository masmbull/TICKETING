<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display category management page.
     */
    public function index(): View
    {
        $categories = Category::with('subCategories')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Create a new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories,name',
            'description' => 'nullable|max:500',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|max:500',
            'is_active' => 'boolean',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? $category->is_active,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Get subcategories for a category (AJAX).
     */
    public function subcategories(string $id): JsonResponse
    {
        $subcategories = SubCategory::where('category_id', $id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($subcategories);
    }

    /**
     * Create a new subcategory.
     */
    public function storeSubCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|max:500',
        ]);

        // Check uniqueness within category
        $exists = SubCategory::where('category_id', $validated['category_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'This subcategory already exists in the selected category.']);
        }

        SubCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Subcategory created successfully.');
    }

    /**
     * Update an existing subcategory.
     */
    public function updateSubCategory(Request $request, string $id): RedirectResponse
    {
        $subCategory = SubCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'is_active' => 'boolean',
        ]);

        $subCategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => $validated['is_active'] ?? $subCategory->is_active,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Subcategory updated successfully.');
    }
}