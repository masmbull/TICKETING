<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>KPI Report - {{ $staff->name ?? 'Unknown' }}</title>
    <style>
        @media print {
            body { font-family: 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
            h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
            h2 { font-size: 13px; font-weight: bold; margin: 16px 0 8px; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
            h3 { font-size: 12px; font-weight: bold; margin: 12px 0 6px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 12px; }
            th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
            th { background: #f0f0f0; font-weight: bold; font-size: 10px; }
            .metric { text-align: center; padding: 6px; }
            .metric .value { font-size: 16px; font-weight: bold; }
            .metric .label { font-size: 9px; color: #666; }
            .header-bar { border-bottom: 2px solid #dc2626; padding-bottom: 6px; margin-bottom: 12px; }
            .no-break { page-break-inside: avoid; }
        }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; }
        .header-bar { border-bottom: 2px solid #dc2626; padding-bottom: 6px; margin-bottom: 12px; }
        h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        h2 { font-size: 13px; font-weight: bold; margin: 16px 0 8px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 12px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; font-size: 11px; }
        .metric { text-align: center; padding: 6px; }
        .metric .value { font-size: 16px; font-weight: bold; }
        .metric .label { font-size: 9px; color: #666; }
        .good { color: #16a34a; }
        .normal { color: #2563eb; }
        .poor { color: #dc2626; }
        .print-btn { display: none; }
    </style>
</head>
<body onload="window.print()">
    <div class="header-bar no-break">
        <h1>IT SUPPORT KPI REPORT</h1>
        <p>Staff: <strong>{{ $staff->name ?? 'Unknown' }}</strong> | Period: <strong>{{ $periodDisplay ?? '—' }}</strong></p>
    </div>

    <h2>KPI Metrics</h2>
    <table class="no-break">
        <tr>
            <th class="metric">Total Tickets Handled</th>
            <td class="metric">{{ $kpi['total'] ?? 0 }}</td>
            <th class="metric">Total Completed</th>
            <td class="metric">{{ $kpi['completed'] ?? 0 }}</td>
        </tr>
        <tr>
            <th class="metric">Total In Progress</th>
            <td class="metric">{{ $kpi['in_progress'] ?? 0 }}</td>
            <th class="metric">Total Waiting Confirmation</th>
            <td class="metric">{{ $kpi['waiting_confirmation'] ?? 0 }}</td>
        </tr>
        <tr>
            <th class="metric">Completion Rate</th>
            <td class="metric">{{ $kpi['completion_rate'] ?? 0 }}%</td>
            <th class="metric">Avg Resolution Time</th>
            <td class="metric">{{ $kpi['avg_resolution'] ?? '—' }}</td>
        </tr>
    </table>

    <h2>SLA Performance</h2>
    <table class="no-break">
        <tr>
            <th>Excellent</th><td>{{ $kpi['sla']['Excellent'] ?? 0 }}</td>
            <th>Normal</th><td>{{ $kpi['sla']['Normal'] ?? 0 }}</td>
            <th>Poor</th><td>{{ $kpi['sla']['Poor'] ?? 0 }}</td>
        </tr>
    </table>

    <h2>Category Breakdown</h2>
    <table class="no-break">
        <tr>
            <th>Category</th><th>Tickets</th><th>Completed</th>
        </tr>
        @forelse($categories as $cat)
        <tr>
            <td>{{ $cat->category }}</td>
            <td>{{ $cat->tickets }}</td>
            <td>{{ $cat->completed }}</td>
        </tr>
        @empty
        <tr><td colspan="3">No category data</td></tr>
        @endforelse
    </table>

    @if($reportType === 'detailed')
    <h2>Ticket Details</h2>
    <table class="no-break">
        <tr>
            <th>Ticket ID</th><th>Category</th><th>Subcategory</th><th>Created</th>
            <th>Assigned</th><th>Completed</th><th>SLA Priority</th><th>SLA Result</th><th>Resolution</th>
        </tr>
        @forelse($tickets as $ticket)
        <tr>
            <td>{{ $ticket->ticket_number }}</td>
            <td>{{ $ticket->category->name ?? '-' }}</td>
            <td>{{ $ticket->subCategory->name ?? '-' }}</td>
            <td>{{ $ticket->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
            <td>{{ $ticket->assigned_at?->format('Y-m-d H:i') ?? '-' }}</td>
            <td>{{ $ticket->completed_at?->format('Y-m-d H:i') ?? '-' }}</td>
            <td>{{ $ticket->sla_priority ? ucfirst($ticket->sla_priority) : 'No SLA' }}</td>
            <td>{{ $ticket->sla_status }}</td>
            <td>{{ $ticket->resolution ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="9">No tickets found</td></tr>
        @endforelse
    </table>
    @endif
</body>
</html>
