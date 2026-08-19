<?php

namespace App\Observers;

use App\Models\TicketComment;
use App\Services\AuditService;

class TicketCommentObserver
{
    public function created(TicketComment $comment): void
    {
        $ticket = $comment->ticket;
        $userName = $comment->user?->name ?? 'Unknown';

        AuditService::log('comment_added', $comment, [], $comment->toArray(), "Comment added by {$userName} on ticket {$ticket?->ticket_number}");
    }

    public function updated(TicketComment $comment): void
    {
        $changes = $comment->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($changes as $field => $value) {
            $old[$field] = $comment->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old)) {
            return;
        }

        AuditService::log('updated', $comment, $old, $new);
    }

    public function deleted(TicketComment $comment): void
    {
        AuditService::log('comment_deleted', $comment, $comment->toArray(), []);
    }
}
