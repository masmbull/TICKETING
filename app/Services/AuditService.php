<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Centralized audit logging. Observers call this instead of duplicating
 * audit-record creation across controllers, keeping sensitive-field
 * stripping and request-context capture in one place.
 */
class AuditService
{
    /**
     * Fields that must never be written to the audit log.
     */
    protected static array $sensitive = [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
    ];

    /**
     * Record an audit entry.
     *
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public static function log(string $event, Model $model, array $old = [], array $new = []): void
    {
        AuditLog::create([
            'user_id'         => auth()->id(),
            'event'           => $event,
            'auditable_type'  => get_class($model),
            'auditable_id'    => $model->getKey(),
            'old_values'      => self::stripSensitive($old) ?: null,
            'new_values'      => self::stripSensitive($new) ?: null,
            'ip_address'      => optional(request())?->ip(),
            'user_agent'      => optional(request())?->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected static function stripSensitive(array $values): array
    {
        return collect($values)
            ->reject(fn ($_, string $key) => in_array($key, self::$sensitive, true))
            ->all();
    }
}
