<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:sans-serif;font-size:12px;">
    <thead>
        <tr style="background:#f0f0f0;">
            <th>Timestamp</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Module</th>
            <th>Target</th>
            <th>Description</th>
            <th>Old Values</th>
            <th>New Values</th>
            <th>IP Address</th>
        </tr>
    </thead>
    <tbody>
        @forelse($logs as $log)
        <tr>
            <td>{{ $log->created_at?->format('Y-m-d H:i:s') ?? '' }}</td>
            <td>{{ $log->user?->name ?? 'System' }}</td>
            <td>{{ \App\Http\Controllers\AuditLogController::eventLabel($log->event) }}</td>
            <td>{{ \App\Http\Controllers\AuditLogController::moduleLabel($log->auditable_type) }}</td>
            <td>{{ $log->target ?? '#' . $log->auditable_id }}</td>
            <td>{{ $log->description ?? '' }}</td>
            <td>{{ $log->old_values ? collect($log->old_values)->map(fn($v, $k) => "{$k}={$v}")->implode('; ') : '' }}</td>
            <td>{{ $log->new_values ? collect($log->new_values)->filter(fn($v) => !is_array($v))->map(fn($v, $k) => "{$k}={$v}")->implode('; ') : '' }}</td>
            <td>{{ $log->ip_address ?? '' }}</td>
        </tr>
        @empty
        <tr><td colspan="9">No audit logs found</td></tr>
        @endforelse
    </tbody>
</table>
