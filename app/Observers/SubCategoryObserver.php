<?php

namespace App\Observers;

use App\Models\SubCategory;
use App\Services\AuditService;

class SubCategoryObserver
{
    public function created(SubCategory $subCategory): void
    {
        AuditService::log('created', $subCategory, [], $subCategory->toArray());
    }

    public function updated(SubCategory $subCategory): void
    {
        $changes = $subCategory->getChanges();
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
            $old[$field] = $subCategory->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old)) {
            return;
        }

        $event = 'updated';
        if (array_key_exists('is_active', $old)) {
            $event = $old['is_active'] ? 'deactivated' : 'activated';
        }

        AuditService::log($event, $subCategory, $old, $new);
    }

    public function deleted(SubCategory $subCategory): void
    {
        AuditService::log('deleted', $subCategory, $subCategory->toArray(), []);
    }
}
