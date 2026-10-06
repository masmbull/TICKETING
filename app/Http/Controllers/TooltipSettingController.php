<?php

namespace App\Http\Controllers;

use App\Models\TooltipSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TooltipSettingController extends Controller
{
    /**
     * Show the tooltip / onboarding tour configuration page.
     */
    public function index(): View
    {
        $setting = TooltipSetting::current();

        return view('settings.tooltip', compact('setting'));
    }

    /**
     * Toggle the tour on/off and save the editable steps.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'steps' => ['nullable', 'array'],
            'steps.*.title' => ['nullable', 'string', 'max:120'],
            'steps.*.description' => ['nullable', 'string', 'max:500'],
            'steps.*.target' => ['nullable', 'string', 'max:120'],
        ]);

        $steps = collect($validated['steps'] ?? [])
            ->map(fn ($step) => [
                'title' => trim($step['title'] ?? ''),
                'description' => trim($step['description'] ?? ''),
                'target' => trim($step['target'] ?? ''),
            ])
            ->filter(fn ($step) => $step['title'] !== '' || $step['description'] !== '')
            ->values()
            ->all();

        TooltipSetting::current()->update([
            'enabled' => $request->boolean('enabled'),
            'steps' => $steps ?: TooltipSetting::defaultSteps(),
        ]);

        return redirect()->route('tooltip.index')->with('success', 'Tooltip settings saved.');
    }
}