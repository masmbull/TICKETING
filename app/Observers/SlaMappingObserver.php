<?php

namespace App\Observers;

use App\Models\SlaMapping;
use App\Services\AuditService;

class SlaMappingObserver
{
    public function created(SlaMapping $mapping): void
    {
        $categoryName = $mapping->category?->name ?? 'Unknown';
        $subName = $mapping->subCategory?->name ?? 'All';
        AuditService::log('sla_mapping_created', $mapping, [], $mapping->toArray(), "SLA mapping created: {$categoryName} → {$subName} → {$mapping->priority}");
    }

    public function updated(SlaMapping $mapping): void
    {
        $changes = $mapping->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($changes as $field => $value) {
            $old[$field] = $mapping->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old)) {
            return;
        }

        $categoryName = $mapping->category?->name ?? 'Unknown';
        AuditService::log('sla_mapping_updated', $mapping, $old, $new, "SLA mapping updated: {$categoryName}");
    }

    public function deleted(SlaMapping $mapping): void
    {
        $categoryName = $mapping->category?->name ?? 'Unknown';
        AuditService::log('sla_mapping_deleted', $mapping, $mapping->toArray(), [], "SLA mapping deleted for {$categoryName}");
    }
}
