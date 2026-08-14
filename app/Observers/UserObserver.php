<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AuditService;

class UserObserver
{
    public function created(User $user): void
    {
        AuditService::log('created', $user, [], $user->toArray());
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($changes as $field => $value) {
            if ($field === 'updated_at') {
                continue;
            }
            $old[$field] = $user->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old)) {
            return;
        }

        $event = 'updated';
        if (array_key_exists('role_id', $old)) {
            $event = 'role_changed';
        } elseif (array_key_exists('is_active', $old)) {
            $event = $old['is_active'] ? 'deactivated' : 'activated';
        }

        AuditService::log($event, $user, $old, $new);
    }

    public function deleted(User $user): void
    {
        AuditService::log('deleted', $user, $user->toArray(), []);
    }
}
