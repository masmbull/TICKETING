<?php

namespace App\Http\Controllers;

use App\Models\SecurityInsight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SecurityInsightController extends Controller
{
    /**
     * Display the security insights list with filtering / tabs.
     */
    public function index(Request $request)
    {
        $query = SecurityInsight::query();

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        // Filter by insight type
        if ($request->filled('insight_type')) {
            $query->where('insight_type', $request->input('insight_type'));
        }

        // Filter by scan source
        if ($request->filled('scan_source')) {
            $query->where('scan_source', 'like', "%{$request->input('scan_source')}%");
        }

        // Filter by search term (description/subject)
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('subject', 'like', "%{$term}%");
            });
        }

        // Tab filter (default active)
        $tab = in_array($request->input('tab', 'active'), ['active', 'archived']) ? $request->input('tab') : 'active';
        $query->where('status', $tab);

        // Sort by most recent insight first
        $query->orderByRaw('COALESCE(scan_performed_on, created_at) DESC');

        $insights = $query->paginate(10)->withQueryString();

        $activeCount = SecurityInsight::where('status', 'active')->count();
        $archivedCount = SecurityInsight::where('status', 'archived')->count();

        $severities = SecurityInsight::select('severity')->distinct()->orderBy('severity')->pluck('severity');
        $insightTypes = SecurityInsight::select('insight_type')->distinct()->orderBy('insight_type')->pluck('insight_type');

        // Per-severity counts (active only) for the summary row.
        $severityCounts = SecurityInsight::where('status', 'active')
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity');

        $activeFilters = collect();

        foreach (['severity', 'insight_type', 'scan_source', 'search'] as $key) {
            if ($request->filled($key)) {
                $activeFilters->push([
                    'key' => $key,
                    'value' => $request->input($key),
                    'label' => $this->filterLabel($key, $request->input($key)),
                ]);
            }
        }

        return view('security-insights.index', compact(
            'insights',
            'activeCount',
            'archivedCount',
            'severities',
            'insightTypes',
            'activeFilters',
            'tab',
            'severityCounts'
        ));
    }

    /**
     * Show a single security insight.
     */
    public function show(SecurityInsight $insight)
    {
        return view('security-insights.show', compact('insight'));
    }

    /**
     * Archive a security insight.
     */
    public function archive(SecurityInsight $insight)
    {
        $insight->archive();
        return redirect()->back()->with('success', 'Insight archived.');
    }

    /**
     * Restore an archived security insight.
     */
    public function restore(SecurityInsight $insight)
    {
        $insight->restore();
        return redirect()->back()->with('success', 'Insight restored.');
    }

    /**
     * Delete a security insight permanently.
     */
    public function destroy(SecurityInsight $insight)
    {
        $insight->delete();
        return redirect()->route('security-insights.index')->with('success', 'Insight deleted.');
    }

    /**
     * Export insights to CSV (respects current filters / tab).
     */
    public function exportCsv(Request $request)
    {
        $query = SecurityInsight::query();

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }
        if ($request->filled('insight_type')) {
            $query->where('insight_type', $request->input('insight_type'));
        }
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('subject', 'like', "%{$term}%");
            });
        }

        $tab = in_array($request->input('tab', 'active'), ['active', 'archived']) ? $request->input('tab') : 'active';
        $query->where('status', $tab);

        $insights = $query->orderByRaw('COALESCE(scan_performed_on, created_at) DESC')->get();

        $filename = 'security-insights-' . $tab . '-' . now()->format('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return Response::stream(function () use ($insights) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Severity', 'Subject', 'Insight Type', 'Description', 'Scan Performed On', 'Status']);
            foreach ($insights as $row) {
                fputcsv($out, [
                    $row->id,
                    ucfirst($row->severity),
                    $row->subject,
                    $row->insight_type,
                    $row->description,
                    $row->scan_performed_on?->format('Y-m-d H:i') ?? '',
                    $row->status,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /**
     * Human-friendly label for an active filter chip.
     */
    protected function filterLabel(string $key, string $value): string
    {
        return match ($key) {
            'severity' => 'Severity: ' . ucfirst($value),
            'insight_type' => 'Type: ' . ucfirst($value),
            'scan_source' => 'Source: ' . $value,
            'search' => 'Search: ' . $value,
            default => ucfirst($value),
        };
    }
}
