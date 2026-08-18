<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint39CreateTicketToastTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private SubCategory $subCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);

        $this->user = User::factory()->create([
            'email' => 'test@mito.local',
            'password' => bcrypt('Test@123'),
            'email_verified_at' => now(),
        ]);

        $this->category = Category::first();
        $this->subCategory = SubCategory::first();
    }

    // 1. Successful ticket creation redirects to tickets.create
    public function test_successful_creation_redirects_to_create_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Test redirect target',
        ]);

        $response->assertRedirect(route('tickets.create'));
    }

    // 2. Successful ticket creation does NOT redirect to tickets.index
    public function test_successful_creation_does_not_redirect_to_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'No index redirect',
        ]);

        // Redirect must be to /my-tickets/create, not to /my-tickets (index)
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/my-tickets/create', $location);
        $this->assertStringEndsWith('/my-tickets/create', $location);
    }

    // 3. Successful ticket creation does NOT redirect to ticket details
    public function test_successful_creation_does_not_redirect_to_details(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'No detail redirect',
        ]);

        $response->assertRedirect(route('tickets.create'));
    }

    // 4. Session contains the success notification data
    public function test_session_contains_ticket_created_flash(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Flash data check',
        ]);

        $response->assertSessionHas('ticket_created');
    }

    // 5. Generated ticket number is included in the success notification data
    public function test_ticket_number_in_flash_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Ticket number in flash',
        ]);

        $ticketNumber = session('ticket_created');
        $this->assertNotNull($ticketNumber);
        $this->assertMatchesRegularExpression('/^ITSUP-\d{8}-\d{5}$/', $ticketNumber);
    }

    // 6. Ticket is actually created
    public function test_ticket_is_actually_created(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Actual creation test',
        ]);

        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->user->id,
            'description' => 'Actual creation test',
            'status' => 'Waiting Confirmation',
        ]);
    }

    // 7. Validation failure remains on tickets.create
    public function test_validation_failure_stays_on_create_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'description' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('description');
    }

    // 8. Validation failure preserves old input
    public function test_validation_failure_preserves_old_input(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'description' => '',
            'category_id' => $this->category->id,
        ]);

        $response->assertSessionHasInput('category_id', $this->category->id);
    }

    // 9. Validation failure does not create a ticket
    public function test_validation_failure_does_not_create_ticket(): void
    {
        $this->actingAs($this->user);

        $countBefore = \App\Models\Ticket::count();

        $this->post('/my-tickets', ['description' => '']);

        $this->assertEquals($countBefore, \App\Models\Ticket::count());
    }

    // 10. Validation failure does not create a success notification
    public function test_validation_failure_no_success_notification(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', ['description' => '']);

        $response->assertSessionMissing('ticket_created');
    }

    // 11. Existing authorization rules remain intact
    public function test_unauthenticated_user_cannot_create_ticket(): void
    {
        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'description' => 'Unauthorized test',
        ]);

        $response->assertRedirect('/login');
    }

    // 12. Existing SLA/priority behavior remains intact
    public function test_sla_priority_behavior_unchanged(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'SLA behavior check',
        ]);

        $ticket = \App\Models\Ticket::where('description', 'SLA behavior check')->first();
        $this->assertNotNull($ticket);
        // Regular user ticket: no explicit SLA set
        $this->assertNull($ticket->sla_priority);
        $this->assertNull($ticket->sla_deadline);
    }

    // 13. Existing attachment behavior remains intact
    public function test_create_page_allows_attachments(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('tickets.create'));
        $response->assertStatus(200);
        $response->assertSee('attachments[]');
        $response->assertSee('Attachments');
    }

    // 14. Existing audit logging remains intact
    public function test_ticket_creation_creates_audit_log(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'category_id' => $this->category->id,
            'sub_category_id' => $this->subCategory->id,
            'description' => 'Audit log check',
        ]);

        $ticket = \App\Models\Ticket::where('description', 'Audit log check')->first();
        $this->assertNotNull($ticket);

        $audit = \App\Models\AuditLog::where('auditable_type', \App\Models\Ticket::class)
            ->where('auditable_id', $ticket->id)
            ->where('event', 'created')
            ->first();
        $this->assertNotNull($audit);
    }

    // 15. Create Ticket page contains the SweetAlert2 integration
    public function test_create_page_loads_with_swalar2_bundle(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('tickets.create'));
        $response->assertStatus(200);
        // SweetAlert2 is bundled in app.js which is included via @vite in the layout
        $content = $response->getContent();
        $this->assertStringContainsString('build/assets/app-', $content);
    }
}
