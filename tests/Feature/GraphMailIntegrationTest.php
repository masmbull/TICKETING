<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GraphMailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $requestor;
    protected array $sentTo = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->requestor = User::factory()->create([
            'role_id' => Role::where('slug', 'user')->value('id'),
        ]);
    }

    /**
     * Fake both Graph endpoints; record every sendMail recipient address.
     */
    protected function captureGraph(int $status = 202): void
    {
        $this->sentTo = [];
        $captured = &$this->sentTo;

        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'fake-token'], 200),
            'graph.microsoft.com/*' => function (Request $request) use (&$captured, $status) {
                // Illuminate\Http\Client\Request exposes the JSON body via
                // ArrayAccess; ->json() is not available in this version.
                $captured[] = $request['message']['toRecipients'][0]['emailAddress']['address'];

                return Http::response('', $status);
            },
        ]);
    }

    protected function graphCalls(): int
    {
        return count(array_filter(
            $this->sentTo,
            fn (?string $to) => str_contains((string) $to, '@')
        ));
    }

    /** CASE 1: support-created ticket -> exactly one Graph email to requestor. */
    public function test_ticket_created_sends_one_graph_email_to_requestor(): void
    {
        $this->captureGraph();
        $admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id')]);

        $response = $this->actingAs($admin)->post(route('tickets.store'), [
            'user_id' => $this->requestor->id,
            'description' => 'Printer on floor 3 jams paper',
            'priority' => 'low',
        ]);

        $response->assertRedirect(route('tickets.create'));
        $this->assertSame([$this->requestor->email], $this->sentTo);

        // Bell behavior unchanged: creation never produced a database row before.
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $this->requestor->id]);
    }

    /** CASE 2 (duplicate rule): creator == requestor -> no email. */
    public function test_self_created_ticket_skips_email(): void
    {
        $this->captureGraph();
        $admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id')]);

        $this->actingAs($admin)->post(route('tickets.store'), [
            'user_id' => $admin->id,
            'description' => 'Admin files own ticket',
            'priority' => 'low',
        ]);

        $this->assertSame([], $this->sentTo);
    }

    /** CASE 3: In Progress -> Completed transition -> one email + bell intact. */
    public function test_completed_transition_sends_one_email_and_keeps_bell(): void
    {
        // Actor must be the assignee: findTicketForUser() scopes staff access.
        $staff = User::factory()->create(['role_id' => Role::where('slug', 'staff')->value('id')]);
        $ticket = Ticket::factory()->create([
            'user_id' => $this->requestor->id,
            'assignee_id' => $staff->id,
            'status' => 'In Progress',
        ]);

        $this->captureGraph();

        $response = $this->actingAs($staff)
            ->postJson(route('tickets.complete', $ticket->id), ['resolution' => 'Replaced roller']);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame([$this->requestor->email], $this->sentTo);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'Completed']);

        // Existing bell notification still written for relevant users.
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->requestor->id,
            'type' => \App\Notifications\TicketNotification::class,
        ]);
    }

    /** CASE 4: already-completed ticket updated -> no duplicate email. */
    public function test_already_completed_ticket_gets_no_second_email(): void
    {
        $staff = User::factory()->create(['role_id' => Role::where('slug', 'staff')->value('id')]);
        $ticket = Ticket::factory()->create([
            'user_id' => $this->requestor->id,
            'assignee_id' => $staff->id,
            'status' => 'In Progress',
        ]);

        $this->captureGraph();
        $actor = $this->actingAs($staff);

        $actor->postJson(route('tickets.complete', $ticket->id), ['resolution' => 'First fix']);
        $this->assertSame(1, $this->graphCalls());

        // Second attempt: controller guard rejects non In Progress tickets.
        $actor->postJson(route('tickets.complete', $ticket->id), ['resolution' => 'Second try'])
            ->assertStatus(422);

        // A plain unrelated update must also stay silent.
        $ticket->update(['priority' => 'high']);

        $this->assertSame(1, $this->graphCalls());
    }

    /** CASE 5: mention -> bell + one Graph email to mentioned user. */
    public function test_mention_sends_bell_and_one_graph_email(): void
    {
        // Single-word names: the @mention regex captures [\w\s]+ so dotted or
        // multi-word faker names would not resolve.
        $charlie = User::factory()->create(['name' => 'Charlie', 'is_active' => true]);
        $reporter = User::factory()->create(['is_active' => true]);
        $ticket = Ticket::factory()->create(['user_id' => $reporter->id]);

        $this->captureGraph();

        $this->actingAs($reporter)
            ->post(route('tickets.comments.store', $ticket->id), [
                'comment' => 'Hey @'.$charlie->name.', can you review this?',
            ])
            ->assertRedirect();

        $this->assertSame([$charlie->email], $this->sentTo);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $charlie->id,
            'type' => \App\Notifications\TicketNotification::class,
        ]);
    }

    /** CASE 6: self mention -> no Graph email. */
    public function test_self_mention_sends_no_email(): void
    {
        $alice = User::factory()->create(['name' => 'Alice', 'is_active' => true]);
        $ticket = Ticket::factory()->create(['user_id' => $alice->id]);

        $this->captureGraph();

        $this->actingAs($alice)
            ->post(route('tickets.comments.store', $ticket->id), [
                'comment' => 'Note to self: check @'.$alice->name.' later',
            ])
            ->assertRedirect();

        $this->assertSame([], $this->sentTo);
    }

    /** CASE 7: same user mentioned twice in one comment -> one email. */
    public function test_duplicate_mention_sends_single_email(): void
    {
        $charlie = User::factory()->create(['name' => 'Charlie', 'is_active' => true]);
        $reporter = User::factory()->create(['name' => 'Bob', 'is_active' => true]);
        $ticket = Ticket::factory()->create(['user_id' => $reporter->id]);

        $this->captureGraph();

        $this->actingAs($reporter)
            ->post(route('tickets.comments.store', $ticket->id), [
                'comment' => '@'.$charlie->name.' ping. And again @'.$charlie->name.', urgent!',
            ])
            ->assertRedirect();

        $this->assertSame([$charlie->email], $this->sentTo);
    }

    /** CASE 8: Graph failure must not break the business operation. */
    public function test_graph_failure_does_not_break_completion(): void
    {
        Log::spy();

        $staff = User::factory()->create(['role_id' => Role::where('slug', 'staff')->value('id')]);
        $ticket = Ticket::factory()->create([
            'user_id' => $this->requestor->id,
            'assignee_id' => $staff->id,
            'status' => 'In Progress',
        ]);

        $this->captureGraph(status: 500);

        $response = $this->actingAs($staff)
            ->postJson(route('tickets.complete', $ticket->id), ['resolution' => 'Resolved anyway']);

        // Business outcome unaffected despite HTTP 500 from Graph.
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'Completed']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->requestor->id]);

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $ctx) => $message === 'Graph mail send failed.'
                && isset($ctx['error'])
                && !array_key_exists('token', $ctx));
    }

    /** CASE 9: recipient without valid email -> skipped safely, no crash. */
    public function test_invalid_recipient_email_is_skipped_safely(): void
    {
        $staff = User::factory()->create(['role_id' => Role::where('slug', 'staff')->value('id')]);
        $broken = User::factory()->create(['role_id' => Role::where('slug', 'user')->value('id')]);
        $broken->forceFill(['email' => 'definitely-not-an-email'])->saveQuietly();

        $ticket = Ticket::factory()->create([
            'user_id' => $broken->id,
            'assignee_id' => $staff->id,
            'status' => 'In Progress',
        ]);

        $this->captureGraph();

        $response = $this->actingAs($staff)
            ->postJson(route('tickets.complete', $ticket->id), ['resolution' => 'Done']);

        $response->assertOk();
        $this->assertSame([], $this->sentTo);
        // Bell row still present even though mail was skipped.
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $broken->id]);
    }
}
