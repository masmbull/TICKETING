<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\CommentMention;
use App\Notifications\TicketNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentionNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $reporter;
    protected User $assignee;
    protected User $mentioned;
    protected User $inactive;
    protected Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->reporter = User::factory()->create(['name' => 'Alice', 'is_active' => true]);
        $this->assignee = User::factory()->create(['name' => 'Bob', 'is_active' => true]);
        $this->mentioned = User::factory()->create(['name' => 'Charlie', 'is_active' => true]);
        $this->inactive = User::factory()->create(['name' => 'David', 'is_active' => false]);

        // Create a ticket
        $this->ticket = Ticket::factory()->create([
            'user_id' => $this->reporter->id,
            'assignee_id' => $this->assignee->id,
        ]);
    }

    /**
     * CASE 1: User A mentions User B → notification to B
     */
    public function test_user_mentioned_receives_notification(): void
    {
        Notification::fake();

        $this->actingAs($this->reporter);
        
        $response = $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => 'Hey @Charlie, can you review this?',
        ]);

        $response->assertRedirect();

        // Verify mention was recorded
        $this->assertDatabaseHas('comment_mentions', [
            'user_id' => $this->mentioned->id,
            'mentioned_name' => 'Charlie',
        ]);

        // Verify notification was sent
        Notification::assertSentTo(
            $this->mentioned,
            TicketNotification::class,
            function ($notification) {
                $data = $notification->toArray($this->mentioned);
                return $data['title'] === 'You were Mentioned' &&
                       str_contains($data['body'], 'Alice') &&
                       str_contains($data['body'], 'mentioned you');
            }
        );
    }

    /**
     * CASE 2: User A mentions diri sendiri → NO notification
     */
    public function test_self_mention_no_notification(): void
    {
        Notification::fake();

        $this->actingAs($this->reporter);
        
        $response = $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => 'I need to check @Alice on this',
        ]);

        $response->assertRedirect();

        // Verify comment was created
        $this->assertDatabaseHas('ticket_comments', [
            'user_id' => $this->reporter->id,
            'comment' => 'I need to check @Alice on this',
        ]);

        // Verify mention was NOT recorded for self
        $this->assertDatabaseMissing('comment_mentions', [
            'user_id' => $this->reporter->id,
        ]);

        // Verify notification was NOT sent to self
        Notification::assertNotSentTo($this->reporter, TicketNotification::class);
    }

    /**
     * CASE 3: User A mentions User B 2x dalam 1 comment → 1 notification (unique constraint)
     */
    public function test_duplicate_mention_only_one_record(): void
    {
        Notification::fake();

        $this->actingAs($this->reporter);
        
        $response = $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => '@Charlie please check this. @Charlie can you prioritize?',
        ]);

        $response->assertRedirect();

        // Verify only ONE mention record exists
        $mentionCount = CommentMention::where([
            'user_id' => $this->mentioned->id,
            'mentioned_name' => 'Charlie',
        ])->count();

        $this->assertEquals(1, $mentionCount);
    }

    /**
     * CASE 4: Mention inactive user → tidak muncul autocomplete & no notification
     */
    public function test_inactive_user_mention_not_processed(): void
    {
        Notification::fake();

        $this->actingAs($this->reporter);
        
        $response = $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => '@David can you help with this?',
        ]);

        $response->assertRedirect();

        // Verify mention was NOT recorded for inactive user
        $this->assertDatabaseMissing('comment_mentions', [
            'user_id' => $this->inactive->id,
        ]);

        // Verify notification was NOT sent
        Notification::assertNotSentTo($this->inactive, TicketNotification::class);
    }

    /**
     * CASE 5: Comment tanpa mention → normal
     */
    public function test_comment_without_mention(): void
    {
        Notification::fake();

        $this->actingAs($this->reporter);
        
        $response = $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => 'This is a normal comment without any mentions',
        ]);

        $response->assertRedirect();

        // Verify comment was created
        $this->assertDatabaseHas('ticket_comments', [
            'comment' => 'This is a normal comment without any mentions',
        ]);

        // Verify no mentions recorded
        $comment = TicketComment::latest()->first();
        $this->assertEquals(0, $comment->mentions()->count());
    }

    /**
     * CASE 6: Existing comments tetap tampil normal
     */
    public function test_existing_comments_display_normally(): void
    {
        // Create a comment with mention
        $comment = TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->reporter->id,
            'comment' => 'Check this @Charlie',
        ]);

        CommentMention::create([
            'comment_id' => $comment->id,
            'user_id' => $this->mentioned->id,
            'mentioned_name' => 'Charlie',
        ]);

        // View ticket
        $response = $this->actingAs($this->reporter)
            ->get(route('tickets.show', $this->ticket->id));

        $response->assertSuccessful();
        $response->assertSee('Check this');
        $response->assertSee('Charlie');
    }

    /**
     * CASE 8: Keyboard navigation (@riy → arrow down → enter)
     */
    public function test_user_search_endpoint_returns_active_users(): void
    {
        $response = $this->actingAs($this->reporter)
            ->getJson('/api/users/search?q=Char');

        $response->assertSuccessful();
        $response->assertJsonFragment(['name' => 'Charlie']);
        
        // Inactive user should not appear
        $response->assertJsonMissing(['name' => 'David']);
    }

    /**
     * CASE 9: Multiple users mentioned in one comment
     */
    public function test_multiple_mentions_in_one_comment(): void
    {
        Notification::fake();

        $user3 = User::factory()->create(['name' => 'Eve', 'is_active' => true]);

        $this->actingAs($this->reporter);
        
        $response = $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => '@Charlie and @Eve, please review this ticket',
        ]);

        $response->assertRedirect();

        // Verify both mentions recorded
        $this->assertDatabaseHas('comment_mentions', [
            'user_id' => $this->mentioned->id,
            'mentioned_name' => 'Charlie',
        ]);

        $this->assertDatabaseHas('comment_mentions', [
            'user_id' => $user3->id,
            'mentioned_name' => 'Eve',
        ]);

        // Verify notifications sent to both
        Notification::assertSentTo($this->mentioned, TicketNotification::class);
        Notification::assertSentTo($user3, TicketNotification::class);
    }

    /**
     * CASE 10: Attachment + mention tetap bekerja
     */
    public function test_mention_with_attachment(): void
    {
        Notification::fake();

        $this->actingAs($this->reporter);
        
        $response = $this->post(
            route('tickets.comments.store', $this->ticket->id),
            [
                'comment' => '@Charlie check the attached screenshot',
                'attachments' => [],
            ]
        );

        $response->assertRedirect();

        // Verify mention was recorded
        $this->assertDatabaseHas('comment_mentions', [
            'user_id' => $this->mentioned->id,
            'mentioned_name' => 'Charlie',
        ]);

        // Verify notification sent
        Notification::assertSentTo($this->mentioned, TicketNotification::class);
    }

    /**
     * Test that mention highlighting is stored correctly
     */
    public function test_mention_highlighting_data(): void
    {
        $this->actingAs($this->reporter);
        
        $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => 'Hey @Charlie, this is important',
        ]);

        $comment = TicketComment::latest()->first();
        $mentions = $comment->mentions;

        $this->assertCount(1, $mentions);
        $this->assertEquals('Charlie', $mentions[0]->mentioned_name);
    }

    /**
     * Test XSS prevention - mentions shouldn't allow injection
     */
    public function test_xss_prevention_in_mentions(): void
    {
        $this->actingAs($this->reporter);
        
        // Try to inject script tag (shouldn't match as username)
        $response = $this->post(route('tickets.comments.store', $this->ticket->id), [
            'comment' => 'Check @<script>alert("xss")</script>',
        ]);

        $response->assertRedirect();

        // Verify no mention was recorded (the regex won't match this)
        $comment = TicketComment::latest()->first();
        $this->assertEquals(0, $comment->mentions()->count());
    }
}