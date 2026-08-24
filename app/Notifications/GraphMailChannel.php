<?php

namespace App\Notifications;

use App\Services\MicrosoftGraphMailService;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Laravel notification channel that delivers email through Microsoft Graph.
 * Failures are logged and swallowed: a mail problem must never break ticket
 * creation, completion or comments. Tokens/secrets never reach the logs.
 */
class GraphMailChannel
{
    public function __construct(private MicrosoftGraphMailService $graph) {}

    public function send(object $notifiable, object $notification): void
    {
        $message = method_exists($notification, 'toGraphMail')
            ? $notification->toGraphMail($notifiable)
            : null;

        if ($message === null) {
            return;
        }

        $email = $notifiable->email ?? '';
        $context = [
            'event'   => $notification->event ?? class_basename($notification),
            'ticket'  => $notification->ticket->ticket_number ?? null,
            'user_id' => $notifiable->getAuthIdentifier() ?? null,
        ];

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info('Graph mail skipped: recipient has no valid email address.', $context);

            return;
        }

        try {
            // Graph answers 202 Accepted with an empty body on success.
            $status = $this->graph->send($email, $message['subject'], $message['html'], 'HTML');
            Log::info("Graph accepted the message ({$status}).", $context);
        } catch (Throwable $e) {
            // Service exception messages carry no tokens/secrets.
            Log::warning('Graph mail send failed.', $context + ['error' => $e->getMessage()]);
        }
    }
}
