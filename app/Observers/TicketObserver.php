<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\AuditService;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        AuditService::log('created', $ticket, [], $ticket->toArray());
    }

    public function updated(Ticket $ticket): void
    {
        $changes = $ticket->getChanges();
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
            $old[$field] = $ticket->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old)) {
            return;
        }

        $event = 'updated';
        if (array_key_exists('status', $old)) {
            $event = 'status_changed';
        } elseif (array_key_exists('assignee_id', $old)) {
            if (!$old['assignee_id'] && $new['assignee_id']) {
                $event = 'assigned';
            } elseif ($old['assignee_id'] && !$new['assignee_id']) {
                $event = 'unassigned';
            } else {
                $event = 'reassigned';
            }
        } elseif (array_key_exists('priority', $old)) {
            $event = 'priority_changed';
        } elseif (array_key_exists('sla_priority', $old) || array_key_exists('sla_deadline', $old)) {
            $event = 'sla_updated';
        }

        AuditService::log($event, $ticket, $old, $new);
    }

    public function deleted(Ticket $ticket): void
    {
        AuditService::log('deleted', $ticket, $ticket->toArray(), []);
    }
}
