<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display the administrator-only audit log viewer.
     * Authorization is enforced server-side via the `admin` route middleware.
     */
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event', 'ilike', "%{$search}%")
                  ->orWhere('auditable_type', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();
        $events = AuditLog::query()->distinct()->pluck('event')->sort()->values();
        $types = AuditLog::query()->distinct()->pluck('auditable_type')->sort()->values();

        return view('audit.index', compact('logs', 'events', 'types'));
    }
}
