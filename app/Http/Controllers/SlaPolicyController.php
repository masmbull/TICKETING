<?php

namespace App\Http\Controllers;

use App\Models\SlaPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaPolicyController extends Controller
{
    public function index(): View
    {
        $policies = SlaPolicy::orderBy('priority')->get();

        return view('settings.sla-policies', compact('policies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', 'max:50'],
            'response_hours' => ['required', 'numeric', 'min:0'],
            'resolution_hours' => ['required', 'numeric', 'min:0'],
            'escalation_enabled' => ['boolean'],
        ]);

        $validated['escalation_enabled'] = $request->boolean('escalation_enabled');
        $validated['is_active'] = true;

        SlaPolicy::create($validated);

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy created successfully.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $policy = SlaPolicy::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', 'max:50'],
            'response_hours' => ['required', 'numeric', 'min:0'],
            'resolution_hours' => ['required', 'numeric', 'min:0'],
            'escalation_enabled' => ['boolean'],
        ]);

        $validated['escalation_enabled'] = $request->boolean('escalation_enabled');

        $policy->update($validated);

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $policy = SlaPolicy::findOrFail($id);
        $policy->delete();

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy deleted successfully.');
    }
}