<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    protected static array $sensitive = [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'session_token',
        'auth_token',
        'secret',
        'api_key',
    ];

    public static function log(string $event, ?Model $model = null, array $old = [], array $new = [], ?string $description = null): void
    {
        $auditableType = $model ? get_class($model) : 'system';
        // ponytail: auditable_id is NOT NULL; system rows use 0 like logAuth().
        // Migrate column to nullable + index on (event) when history matters.
        $auditableId = $model ? $model->getKey() : 0;
        $target = $model ? self::resolveTarget($model) : ($new['target'] ?? null);

        AuditLog::create([
            'user_id'      => auth()->id(),
            'event'        => $event,
            'auditable_type' => $auditableType,
            'auditable_id'   => $auditableId,
            'target'       => $target,
            'description'  => $description,
            'old_values'   => self::stripSensitive($old) ?: null,
            'new_values'   => self::stripSensitive($new) ?: null,
            'ip_address'   => optional(request())?->ip(),
            'user_agent'   => optional(request())?->userAgent(),
        ]);
    }

    public static function logAuth(string $event, ?string $email, array $extra = [], ?string $description = null): void
    {
        AuditLog::create([
            'user_id'      => auth()->id(),
            'event'        => $event,
            'auditable_type' => 'auth',
            'auditable_id'   => 0,
            'target'       => $email,
            'description'  => $description,
            'old_values'   => null,
            'new_values'   => self::stripSensitive(array_merge(['email' => $email], $extra)),
            'ip_address'   => optional(request())?->ip(),
            'user_agent'   => optional(request())?->userAgent(),
        ]);
    }

    protected static function resolveTarget(Model $model): ?string
    {
        if ($model instanceof \App\Models\Ticket) {
            return $model->ticket_number;
        }
        if ($model instanceof \App\Models\User) {
            return $model->email;
        }
        if ($model instanceof \App\Models\SlaPolicy) {
            return $model->name;
        }
        if ($model instanceof \App\Models\SlaMapping) {
            return $model->priority;
        }
        if ($model instanceof \App\Models\Category) {
            return $model->name;
        }
        if ($model instanceof \App\Models\SubCategory) {
            return $model->name;
        }
        if ($model instanceof \App\Models\TicketComment) {
            return 'comment:' . $model->ticket_id;
        }

        return (string) $model->getKey();
    }

    protected static function stripSensitive(array $values): array
    {
        return collect($values)
            ->reject(fn ($_, string $key) => in_array($key, self::$sensitive, true))
            ->all();
    }
}
