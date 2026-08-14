<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\AuditService;

class CategoryObserver
{
    public function created(Category $category): void
    {
        AuditService::log('created', $category, [], $category->toArray());
    }

    public function updated(Category $category): void
    {
        $changes = $category->getChanges();
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
            $old[$field] = $category->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old)) {
            return;
        }

        $event = 'updated';
        if (array_key_exists('is_active', $old)) {
            $event = $old['is_active'] ? 'deactivated' : 'activated';
        }

        AuditService::log($event, $category, $old, $new);
    }

    public function deleted(Category $category): void
    {
        AuditService::log('deleted', $category, $category->toArray(), []);
    }
}
