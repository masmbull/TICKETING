<?php

namespace App\Observers;

use App\Models\SlaPolicy;
use App\Services\AuditService;

class SlaPolicyObserver
{
    public function created(SlaPolicy $policy): void
    {
        AuditService::log('sla_policy_created', $policy, [], $policy->toArray(), "SLA policy created: {$policy->name}");
    }

    public function updated(SlaPolicy $policy): void
    {
        $changes = $policy->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($changes as $field => $value) {
            $old[$field] = $policy->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old)) {
            return;
        }

        AuditService::log('sla_policy_updated', $policy, $old, $new, "SLA policy updated: {$policy->name}");
    }

    public function deleted(SlaPolicy $policy): void
    {
        AuditService::log('sla_policy_deleted', $policy, $policy->toArray(), [], "SLA policy deleted: {$policy->name}");
    }
}
