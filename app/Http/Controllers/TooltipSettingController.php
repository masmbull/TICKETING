<?php

namespace App\Http\Controllers;

use App\Models\TooltipSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TooltipSettingController extends Controller
{
    /** Private dir for admin-uploaded tour screenshots (mirrors attachments). */
    private function tourDir(): string
    {
        $dir = storage_path('app/private/tour');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /** Git-tracked default slides shipped with the app. */
    private function bundledDir(): string
    {
        return resource_path('tour');
    }

    /**
     * Show the tooltip / onboarding tour configuration page.
     */
    public function index(): View
    {
        $setting = TooltipSetting::current();

        return view('settings.tooltip', compact('setting'));
    }

    /**
     * Toggle the tour on/off and save the editable steps (incl. screenshot).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'steps' => ['nullable', 'array'],
            'steps.*.title' => ['nullable', 'string', 'max:120'],
            'steps.*.description' => ['nullable', 'string', 'max:500'],
            'steps.*.target' => ['nullable', 'string', 'max:120'],
            'steps.*.image' => ['nullable', 'string', 'max:180'],
            'steps.*.image_upload' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ]);

        $dir = $this->tourDir();

        $steps = collect($validated['steps'] ?? [])
            ->map(function ($step) use ($dir) {
                $image = trim($step['image'] ?? '');

                // A new upload replaces (and removes) the previous screenshot.
                if (isset($step['image_upload'])) {
                    if ($image !== '' && is_file($dir.'/'.$image)) {
                        @unlink($dir.'/'.$image);
                    }
                    $ext = strtolower($step['image_upload']->getClientOriginalExtension() ?: 'png');
                    $image = uniqid().'_'.time().'.'.$ext;
                    $step['image_upload']->move($dir, $image);
                }

                return [
                    'title' => trim($step['title'] ?? ''),
                    'description' => trim($step['description'] ?? ''),
                    'target' => trim($step['target'] ?? ''),
                    'image' => $image,
                ];
            })
            ->filter(fn ($step) => $step['title'] !== '' || $step['description'] !== '')
            ->values()
            ->all();

        // ponytail: orphaned screenshots from removed/replaced steps above are
        // not GC'd here (only replace/clear deletes); add a sweep when it grows.
        TooltipSetting::current()->update([
            'enabled' => $request->boolean('enabled'),
            'steps' => $steps ?: TooltipSetting::defaultSteps(),
        ]);

        return redirect()->route('tooltip.index')->with('success', 'Tooltip settings saved.');
    }

    /**
     * Serve a step's screenshot inline. Any authenticated user (the tour runs
     * for regular users), so this is NOT behind the admin middleware.
     */
    public function image(int $index)
    {
        $step = TooltipSetting::current()->steps[$index] ?? null;
        $name = $step['image'] ?? '';
        abort_if($name === '', 404, 'Screenshot not found.');

        // Admin uploads win; fall back to the bundled default slide of the same
        // name so the default tour works without any storage/volume state.
        $path = $this->tourDir().'/'.$name;
        if (!is_file($path)) {
            $path = $this->bundledDir().'/'.basename($name);
        }

        abort_if(!is_file($path), 404, 'Screenshot not found.');

        return response()->file($path, [
            'Content-Type' => 'image/'.strtolower(pathinfo($name, PATHINFO_EXTENSION)),
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}