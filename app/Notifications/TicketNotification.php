<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $event,
        public Ticket $ticket,
        public ?string $extra = null,
    ) {}

    public function via(object $notifiable): array
    {
        return match ($this->event) {
            // Creation had no bell notification before Graph integration;
            // keeping it that way means graph-mail only.
            'created'   => [GraphMailChannel::class],
            // Bell behavior preserved; Graph email added alongside.
            'completed' => ['database', GraphMailChannel::class],
            // Mention email moved from SMTP/Brevo to Graph; bell untouched.
            'mentioned' => ['database', GraphMailChannel::class],
            default     => ['database'],
        };
    }

    public function toArray(object $notifiable): array
    {
        $ticketNumber = $this->ticket->ticket_number;

        return match ($this->event) {
            'assigned' => [
                'title' => 'Ticket Assigned',
                'body' => "Ticket {$ticketNumber} has been assigned to you.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            'reassigned' => [
                'title' => 'Ticket Reassigned',
                'body' => "Ticket {$ticketNumber} has been reassigned.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            'taken' => [
                'title' => 'Ticket Taken',
                'body' => "Ticket {$ticketNumber} has been picked up.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            'status_changed' => [
                'title' => 'Status Changed',
                'body' => "Ticket {$ticketNumber} status is now {$this->ticket->status}.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            'sla_changed' => [
                'title' => 'SLA Updated',
                'body' => "Ticket {$ticketNumber} SLA priority set to " . ucfirst($this->ticket->sla_priority ?? 'No SLA') . ".",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            'commented' => [
                'title' => 'New Comment',
                'body' => ($this->extra ?: 'Someone') . " commented on ticket {$ticketNumber}.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            'mentioned' => [
                'title' => 'You were Mentioned',
                'body' => ($this->extra ?: 'Someone') . " mentioned you in ticket {$ticketNumber}.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            'completed' => [
                'title' => 'Ticket Completed',
                'body' => "Ticket {$ticketNumber} has been completed.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
            default => [
                'title' => 'Ticket Update',
                'body' => "Ticket {$ticketNumber} has been updated.",
                'url' => "/my-tickets/{$this->ticket->id}",
            ],
        };
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Microsoft Graph email payload. Returning null skips sending silently.
     * Replaces the old SMTP/Brevo toMail() for the events that carry email;
     * Brevo config itself is untouched for any other system mail.
     */
    public function toGraphMail(object $notifiable): ?array
    {
        $ticket = $this->ticket;
        $number = $ticket->ticket_number;
        $link = route('tickets.show', $ticket->id);
        $title = (string) ($ticket->description ?: $number);

        return match ($this->event) {
            'created' => [
                'subject' => "[MITO Ticketing] Ticket #{$number} Created",
                'html' => $this->buildHtml([
                    "Ticket <strong>#{$number}</strong> has been created.",
                    'Title: '.e($title),
                    'Requestor: '.e($ticket->user?->name ?? '-'),
                    'Status: '.e($ticket->status),
                    'Created: '.optional($ticket->created_at)->format('Y-m-d H:i'),
                    'Description: '.e(Str::limit($title, 300)),
                ], $link),
            ],

            // Only the requestor gets the completion email, even when other
            // relevant users receive the bell notification. The report carries
            // the latest analysis/resolution so a re-completed ticket shows
            // current data; subject stays a plain completion subject.
            'completed' => $notifiable->getAuthIdentifier() === $ticket->user_id ? [
                'subject' => "[MITO Ticketing] Ticket #{$number} Completed",
                'html' => $this->buildHtml(array_values(array_filter([
                    "Ticket <strong>#{$number}</strong> has been completed.",
                    'Title: '.e($title),
                    'Requestor: '.e($ticket->user?->name ?? '-'),
                    'Assignee: '.e($ticket->assignee?->name ?? 'Unassigned'),
                    'Status: Completed',
                    $this->extra ? e($this->extra) : '',
                    'Problem Analysis: <br>'.nl2br(e((string) $ticket->problem_analysis)),
                    'Resolution: <br>'.nl2br(e((string) $ticket->resolution)),
                    'Completed: '.optional($ticket->completed_at ?? $ticket->updated_at)->format('Y-m-d H:i'),
                ])), $link),
            ] : null,

            'mentioned' => [
                'subject' => "[MITO Ticketing] You were mentioned in ticket #{$number}",
                'html' => $this->buildHtml([
                    e($this->extra ?: 'Someone')." mentioned you in ticket <strong>#{$number}</strong>.",
                    'Comment: <br>'.nl2br(e((string) $ticket->comments()->latest('id')->value('comment'))),
                ], $link),
            ],

            default => null,
        };
    }

    private function buildHtml(array $lines, string $link): string
    {
        $body = '';
        foreach ($lines as $line) {
            $body .= '<p style="margin:0 0 8px">'.$line.'</p>';
        }

        return '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222">'
            .$body
            .'<p><a href="'.e($link).'">View Ticket</a></p>'
            .'</div>';
    }
}
