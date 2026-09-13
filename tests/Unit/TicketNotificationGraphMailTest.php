<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Notifications\TicketNotification;
use Tests\TestCase;

/**
 * Pure unit checks on the Graph mail payload rules — no database, no HTTP.
 */
class TicketNotificationGraphMailTest extends TestCase
{
    private function ticket(int $requestorId = 1): Ticket
    {
        $ticket = new Ticket([
            'ticket_number' => 'ITSUP-UNIT-00001',
            'description' => 'Unit test ticket',
            'status' => 'Completed',
            'user_id' => $requestorId,
        ]);
        $ticket->id = 10;

        return $ticket;
    }

    private function fakeNotifiable(int $id): object
    {
        return new class($id)
        {
            public function __construct(private int $id) {}

            public function getAuthIdentifier(): int
            {
                return $this->id;
            }
        };
    }

    /** A reopened ticket must never produce an email. */
    public function test_reopened_event_produces_no_graph_mail(): void
    {
        $notification = new TicketNotification('reopened', $this->ticket());

        $this->assertNull($notification->toGraphMail($this->fakeNotifiable(1)));
    }

    /** An unknown event carries no email payload at all. */
    public function test_unknown_event_produces_no_graph_mail(): void
    {
        $notification = new TicketNotification('commented', $this->ticket());

        $this->assertNull($notification->toGraphMail($this->fakeNotifiable(1)));
    }
}
