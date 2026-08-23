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
        $description = null;
        if (array_key_exists('role_id', $old)) {
            $event = 'role_changed';
        } elseif (array_key_exists('is_active', $old)) {
            $event = $old['is_active'] ? 'deactivated' : 'activated';
        } elseif (array_key_exists('deleted_by', $old) && $old['deleted_by'] === null) {
            $event = 'user_trashed';
            $deleter = User::find($new['deleted_by']);
            $description = 'User moved to trash by ' . ($deleter?->name ?? '#' . $new['deleted_by']);
        }

        AuditService::log($event, $user, $old, $new, $description);
    }

    public function deleted(User $user): void
    {
        AuditService::log('deleted', $user, $user->toArray(), [], "User soft-deleted: {$user->name} ({$user->email})");
    }

    public function restored(User $user): void
    {
        $restorer = User::find(auth()->id());
        AuditService::log('user_restored', $user, ['deleted_at' => $user->deleted_at?->toDateTimeString()], ['restored_by' => auth()->id()], 'User restored by ' . ($restorer?->name ?? 'unknown'));
    }

    public function forceDeleted(User $user): void
    {
        AuditService::log('user_force_deleted', null, [], ['name' => $user->name, 'email' => $user->email], "User permanently deleted: {$user->name} ({$user->email})");
    }
}

