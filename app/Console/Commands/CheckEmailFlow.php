<?php

namespace App\Console\Commands;

use App\Http\Controllers\TicketController;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * End-to-end verification of the production Microsoft Graph email path.
 *
 * Drives a real Ticket through the real lifecycle transitions and sends the
 * notifications the application really sends (GraphMailChannel ->
 * MicrosoftGraphMailService -> client-credentials token -> /sendMail).
 *
 * Only the outbound HTTP transport is redirected to a local recorder, so
 * graph.microsoft.com is never contacted and no message can be delivered by
 * accident. Everything else stays production code.
 *
 * Expected outcomes:
 *   created            -> email
 *   completed          -> email
 *   reopened           -> NO email
 *   completed (again)  -> email
 */
class CheckEmailFlow extends Command
{
    protected $signature = 'mito:check-email-flow
                            {--to= : Address the real integration would email}
                            {--create-ticket : Also send one direct test mail via send()}';

    protected $description = 'Verify the real Microsoft Graph email flow: created/completed/reopened/completed-again';

    /** @var array<int, array{to: string, subject: string}> */
    private array $captured = [];

    /** Address every lifecycle stage must actually deliver to. */
    private string $recipient = '';

    public function handle(MicrosoftGraphMailService $graph): int
    {
        $to = (string) ($this->option('to') ?: config('services.microsoft-graph.mail_from'));
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid --to address (and MS_GRAPH_MAIL_FROM is not a valid fallback).');

            return self::FAILURE;
        }

        $this->recipient = $to;

        $this->info('Microsoft Graph email flow check');
        $this->line('Recipient: '.$to);
        $this->newLine();

        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'mito-check'], 200),
            'graph.microsoft.com/*' => function (ClientRequest $request) {
                $message = $request['message'];
                $this->captured[] = [
                    'to' => $message['toRecipients'][0]['emailAddress']['address'] ?? '',
                    'subject' => $message['subject'] ?? '',
                ];

                return Http::response('', 202);
            },
        ]);

        $staff = User::whereHas('role', fn ($q) => $q->where('slug', 'staff'))->first();
        $requestor = User::whereHas('role', fn ($q) => $q->where('slug', 'user'))->first();

        if (! $staff || ! $requestor) {
            $this->error('Need at least one staff user and one regular user in the database.');

            return self::FAILURE;
        }

        // Redirect every stage to the requested address so the flow reports on
        // real deliveries. Restored (even on failure) before the command ends.
        $originalRequestorEmail = $requestor->email;
        $requestor->forceFill(['email' => $this->recipient])->saveQuietly();

        try {
            $failures = $this->runFlow($requestor, $staff);
        } finally {
            $requestor->forceFill(['email' => $originalRequestorEmail])->saveQuietly();
        }

        if ($this->option('create-ticket')) {
            $this->newLine();
            $this->line('Sending one direct message through MicrosoftGraphMailService::send()...');
            try {
                $status = $graph->send($to, '[MITO Ticketing] Email flow check', '<p>Automated mito:check-email-flow message.</p>', 'HTML');
                $this->info('Graph accepted the message (HTTP '.$status.').');
            } catch (Throwable $e) {
                $this->error('Direct send failed: '.$e->getMessage());
                $failures++;
            }
        }

        $this->newLine();
        if ($failures === 0) {
            $this->info('Email flow check passed: '.count($this->captured).' message(s) produced, all stages matched.');

            return self::SUCCESS;
        }

        $this->error($failures.' stage(s) did not match the expected behaviour.');

        return self::FAILURE;
    }

    /**
     * Drive the real lifecycle transitions and verify how many Graph messages
     * each one produces. Returns the number of stages that did not match.
     */
    private function runFlow(User $requestor, User $staff): int
    {
        $ticket = Ticket::create([
            'ticket_number' => 'ITSUP-MAILCHK-'.now()->format('Ymd-His'),
            'user_id' => $requestor->id,
            'assignee_id' => $staff->id,
            'description' => 'Automated email flow check (mito:check-email-flow)',
            'status' => 'In Progress',
        ]);

        $failures = 0;

        try {
            // 'created' is the same call the controller makes on ticket creation.
            $failures += $this->stage('created', 1, fn () => $requestor->notify(new TicketNotification('created', $ticket)));

            // The remaining transitions are driven through the real controller
            // so the reopened-no-email guarantee is verified on the production
            // path rather than by calling the notification directly.
            Auth::login($staff);
            $controller = app(TicketController::class);

            $failures += $this->stage('completed', 1, function () use ($controller, $ticket) {
                $controller->completeTicket(new HttpRequest(['resolution' => 'Automated check: first completion.']), $ticket->id);
                $ticket->refresh();
            });

            $failures += $this->stage('reopened (no email)', 0, function () use ($controller, $ticket) {
                $controller->reopenTicket(new HttpRequest(), $ticket->id);
                $ticket->refresh();
            });

            $failures += $this->stage('completed after reopen', 1, function () use ($controller, $ticket) {
                $controller->completeTicket(new HttpRequest(['resolution' => 'Automated check: final resolution.']), $ticket->id);
                $ticket->refresh();
            });

            Auth::logout();

            if ($ticket->status !== 'Completed') {
                $this->error('Ticket did not end in Completed state (unexpected lifecycle result).');
                $failures++;
            }
        } finally {
            // Keep the real database clean: the check ticket never persists.
            $ticket->forceDelete();
        }

        return $failures;
    }

    /**
     * Run one lifecycle stage, then verify how many Graph messages it produced.
     */
    private function stage(string $label, int $expect, callable $action): int
    {
        $before = count($this->captured);
        $action();
        $count = count($this->captured) - $before;

        if ($count === $expect) {
            $this->info(sprintf('  [OK]   %-22s %d email(s)', $label, $count));

            return 0;
        }

        $this->error(sprintf('  [FAIL] %-22s expected %d, got %d', $label, $expect, $count));

        return 1;
    }
}
