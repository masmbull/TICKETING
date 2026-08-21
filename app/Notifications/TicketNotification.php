<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

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
        return ['database'];
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
}
