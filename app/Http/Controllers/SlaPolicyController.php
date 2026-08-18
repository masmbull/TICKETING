<?php

namespace App\Http\Controllers;

use App\Models\SlaPolicy;
use App\Models\SlaMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaPolicyController extends Controller
{
    public function index(): View
    {
        $policies = SlaPolicy::orderBy('priority')->get();
        $mappings = SlaMapping::with(['category', 'subCategory', 'policy'])->get();

        return view('settings.sla-policies', compact('policies', 'mappings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'resolution_days' => ['required', 'integer', 'min:1'],
        ]);

        SlaPolicy::updateOrCreate(
            ['priority' => $validated['priority']],
            [
                'name' => ucfirst($validated['priority']),
                'resolution_days' => $validated['resolution_days'],
                'resolution_hours' => $validated['resolution_days'] * 24,
                'is_active' => true,
            ]
        );

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy saved.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $policy = SlaPolicy::findOrFail($id);

        $validated = $request->validate([
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'resolution_days' => ['required', 'integer', 'min:1'],
        ]);

        $policy->update([
            'priority' => $validated['priority'],
            'resolution_days' => $validated['resolution_days'],
            'resolution_hours' => $validated['resolution_days'] * 24,
        ]);

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        SlaPolicy::findOrFail($id)->delete();

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy deleted.');
    }

    public function storeMapping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
        ]);

        $existing = SlaMapping::where('category_id', $validated['category_id'])
            ->where('sub_category_id', $validated['sub_category_id'] ?? null)
            ->where('priority', $validated['priority'])
            ->first();

        if ($existing) {
            return redirect()->route('sla-policies.index')
                ->with('error', 'This exact mapping already exists.');
        }

        SlaMapping::updateOrCreate(
            [
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'] ?? null,
            ],
            [
                'priority' => $validated['priority'],
                'is_active' => true,
            ]
        );

        return redirect()->route('sla-policies.index')->with('success', 'SLA mapping added.');
    }

    public function destroyMapping(int $id): RedirectResponse
    {
        SlaMapping::findOrFail($id)->delete();

        return redirect()->route('sla-policies.index')->with('success', 'SLA mapping deleted.');
    }
}
