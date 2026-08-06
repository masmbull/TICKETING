<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed categories
        $this->seed(\Database\Seeders\CategorySeeder::class);

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@mito.local',
            'password' => bcrypt('Test@123'),
            'email_verified_at' => now(),
        ]);
    }

    // ─── Auth Pages ───────────────────────────────────────

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MITO');
        $response->assertSee('Login');
    }

    public function test_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@mito.local',
            'password' => 'Test@123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@mito.local',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // ─── Dashboard ────────────────────────────────────────

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_dashboard_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    // ─── My Tickets ───────────────────────────────────────

    public function test_my_tickets_requires_auth(): void
    {
        $response = $this->get('/my-tickets');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_my_tickets_page_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/my-tickets');
        $response->assertStatus(200);
    }

    public function test_my_tickets_page_loads_with_ticket(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000001',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Test Ticket',
            'description' => 'Test description for smoke test',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $response = $this->get('/my-tickets');
        $response->assertStatus(200);
        $response->assertSee('TKT-000001');
        $response->assertSee('Test Ticket');
    }

    public function test_my_tickets_search(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        Ticket::create([
            'ticket_number' => 'TKT-000002',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Network connectivity issue',
            'description' => 'Cannot connect to WiFi',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $response = $this->get('/my-tickets?search=Network');
        $response->assertStatus(200);
        $response->assertSee('Network connectivity issue');
    }

    public function test_my_tickets_filter_by_status(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        Ticket::create([
            'ticket_number' => 'TKT-000003',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Resolved issue',
            'description' => 'This was resolved',
            'priority' => 'low',
            'status' => 'resolved',
        ]);

        $response = $this->get('/my-tickets?status=resolved');
        $response->assertStatus(200);
        $response->assertSee('Resolved issue');
    }

    // ─── Create Ticket ────────────────────────────────────

    public function test_create_ticket_page_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/my-tickets/create');
        $response->assertStatus(200);
        $response->assertSee('Create Support Ticket');
        $response->assertSee('Category');
        $response->assertSee('Subject');
        $response->assertSee('Description');
        $response->assertSee('Priority');
    }

    public function test_create_ticket_with_valid_data(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $response = $this->post('/my-tickets', [
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Printer not working',
            'description' => 'The office printer on floor 3 is not responding.',
            'priority' => 'medium',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->user->id,
            'subject' => 'Printer not working',
            'priority' => 'medium',
            'status' => 'Open',
        ]);
    }

    public function test_create_ticket_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'subject' => '',
            'description' => '',
            'category_id' => '',
            'priority' => '',
        ]);

        $response->assertSessionHasErrors(['subject', 'description', 'priority']);
    }

    // ─── Ticket Detail ────────────────────────────────────

    public function test_ticket_detail_loads(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000010',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Detail test ticket',
            'description' => 'Testing ticket detail page',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $response = $this->get("/my-tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('TKT-000010');
        $response->assertSee('Detail test ticket');
        $response->assertSee('Testing ticket detail page');
        $response->assertSee('Add a Comment');
    }

    public function test_ticket_detail_shows_comments(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000011',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Comment test',
            'description' => 'Testing comments',
            'priority' => 'low',
            'status' => 'open',
        ]);

        $ticket->comments()->create([
            'user_id' => $this->user->id,
            'comment' => 'This is a test comment',
        ]);

        $response = $this->get("/my-tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('This is a test comment');
    }

    // ─── Comments ─────────────────────────────────────────

    public function test_store_comment(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000012',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Comment store test',
            'description' => 'Testing comment storage',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $response = $this->post("/my-tickets/{$ticket->id}/comments", [
            'comment' => 'New comment added',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'comment' => 'New comment added',
        ]);
    }

    // ─── Status Update ────────────────────────────────────

    public function test_update_ticket_status(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000013',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Status update test',
            'description' => 'Testing status change',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'In Progress',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'In Progress',
        ]);
    }

    public function test_update_ticket_status_validates(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000014',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'subject' => 'Validation test',
            'description' => 'Testing validation',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── Categories ───────────────────────────────────────

    public function test_categories_page_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/categories');
        $response->assertStatus(200);
        $response->assertSee('Category');
    }

    public function test_store_category(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/categories', [
            'name' => 'New Test Category',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'New Test Category',
        ]);
    }

    public function test_update_category(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();

        $response = $this->patch("/categories/{$category->id}", [
            'name' => 'Updated Category Name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
        ]);
    }

    public function test_store_subcategory(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();

        $response = $this->post('/subcategories', [
            'name' => 'New SubCategory',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sub_categories', [
            'name' => 'New SubCategory',
            'category_id' => $category->id,
        ]);
    }

    public function test_subcategories_api(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();

        $response = $this->get("/api/categories/{$category->id}/subcategories");
        $response->assertStatus(200);
        $response->assertJsonCount($category->subCategories->count());
    }

    // ─── Profile ──────────────────────────────────────────

    public function test_profile_page_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Profile');
        $response->assertSee('Change Password');
    }

    public function test_profile_update(): void
    {
        $this->actingAs($this->user);

        $response = $this->patch('/profile', [
            'name' => 'Updated Name',
            'email' => 'test@mito.local',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_password_update(): void
    {
        $this->actingAs($this->user);

        $response = $this->patch('/profile/password', [
            'current_password' => 'Test@123',
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertRedirect();
    }

    // ─── Settings ─────────────────────────────────────────

    public function test_settings_page_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/settings');
        $response->assertStatus(200);
    }
}