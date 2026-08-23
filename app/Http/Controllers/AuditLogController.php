<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    private const EVENT_LABELS = [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'activated' => 'Activated',
        'deactivated' => 'Deactivated',
        'role_changed' => 'Role Changed',
        'assigned' => 'Assigned',
        'reassigned' => 'Reassigned',
        'unassigned' => 'Unassigned',
        'assign_to_me' => 'Assign to Me',
        'status_changed' => 'Status Changed',
        'priority_changed' => 'Priority Changed',
        'sla_updated' => 'SLA Updated',
        'sla_manually_assigned' => 'SLA Manually Assigned',
        'sla_cleared' => 'SLA Cleared',
        'sla_policy_created' => 'SLA Policy Created',
        'sla_policy_updated' => 'SLA Policy Updated',
        'sla_policy_deleted' => 'SLA Policy Deleted',
        'sla_mapping_created' => 'SLA Mapping Created',
        'sla_mapping_updated' => 'SLA Mapping Updated',
        'sla_mapping_deleted' => 'SLA Mapping Deleted',
        'comment_added' => 'Comment Added',
        'comment_deleted' => 'Comment Deleted',
        'problem_analysis_submitted' => 'Problem Analysis Submitted',
        'ticket_completed' => 'Ticket Completed',
        'login_success' => 'Login Success',
        'login_failed' => 'Login Failed',
        'logout' => 'Logout',
        'password_changed' => 'Password Changed',
    ];

    public static function eventLabel(string $event): string
    {
        return self::EVENT_LABELS[$event] ?? ucfirst(str_replace('_', ' ', $event));
    }

    public static function moduleLabel(string $type): string
    {
        if ($type === 'auth') return 'Authentication';
        if ($type === 'system') return 'System';
        $basename = class_basename($type);
        return preg_replace('/(Ticket|User|Category|SubCategory|SlaPolicy|SlaMapping|TicketComment)s?/', '$1s', $basename);
    }

    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('target')) {
            $query->where('target', 'ILIKE', "%{$request->target}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('actor')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($qq) use ($request) {
                    $qq->where('name', 'ILIKE', "%{$request->actor}%")
                       ->orWhere('email', 'ILIKE', "%{$request->actor}%");
                })
                  ->orWhere('target', 'ILIKE', "%{$request->actor}%");
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event', 'ILIKE', "%{$search}%")
                  ->orWhere('auditable_type', 'ILIKE', "%{$search}%")
                  ->orWhere('target', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('ip_address', 'ILIKE', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $events = AuditLog::query()->distinct()->pluck('event')->sort()->values();
        $types = AuditLog::query()->distinct()->pluck('auditable_type')->sort()->values();

        return view('audit.index', compact('logs', 'events', 'types'));
    }

    public function show(AuditLog $log): View
    {
        $log->load('user');

        return view('audit.show', compact('log'));
    }

    public function exportExcel(Request $request)
    {
        $query = AuditLog::with('user')->latest();
        $this->applyFilters($query, $request);
        $logs = $query->get();

        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.xls';

        $html = view('audit.export', compact('logs'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function exportCsv(Request $request)
    {
        $query = AuditLog::with('user')->latest();
        $this->applyFilters($query, $request);
        $logs = $query->get();

        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Actor', 'Action', 'Module', 'Target', 'Description', 'Old Values', 'New Values', 'IP Address']);
            foreach ($logs as $log) {
                $row = [
                    $log->created_at?->format('Y-m-d H:i:s') ?? '',
                    $log->user?->name ?? 'System',
                    self::eventLabel($log->event),
                    self::moduleLabel($log->auditable_type),
                    $log->target ?? "#{$log->auditable_id}",
                    $log->description ?? '',
                    $this->flattenValues($log->old_values),
                    $this->flattenValues($log->new_values),
                    $log->ip_address ?? '',
                ];
                fputcsv($handle, array_map(fn ($v) => $this->sanitizeCsvValue((string) $v), $row));
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

        return $response;
    }

    /**
     * Neutralize CSV formula injection: cells starting with = + - @ are
     * prefixed with an apostrophe so Excel/Sheets treat them as text.
     */
    private function sanitizeCsvValue(string $value): string
    {
        return isset($value[0]) && in_array($value[0], ['=', '+', '-', '@'], true)
            ? "'" . $value
            : $value;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }
        if ($request->filled('target')) {
            $query->where('target', 'ILIKE', "%{$request->target}%");
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('actor')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($qq) use ($request) {
                    $qq->where('name', 'ILIKE', "%{$request->actor}%")
                       ->orWhere('email', 'ILIKE', "%{$request->actor}%");
                })->orWhere('target', 'ILIKE', "%{$request->actor}%");
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event', 'ILIKE', "%{$search}%")
                  ->orWhere('auditable_type', 'ILIKE', "%{$search}%")
                  ->orWhere('target', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('ip_address', 'ILIKE', "%{$search}%");
            });
        }
    }

    protected function flattenValues(?array $values): string
    {
        if (!$values) return '';
        $parts = [];
        foreach ($values as $key => $val) {
            if (!is_array($val)) {
                $parts[] = "{$key}={$val}";
            }
        }
        return implode('; ', $parts);
    }
}
