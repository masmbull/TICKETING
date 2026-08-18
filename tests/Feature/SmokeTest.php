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
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed categories and roles (staff needed for workflow tests)
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@mito.local',
            'password' => bcrypt('Test@123'),
            'email_verified_at' => now(),
        ]);

        // Create an IT Support staff member
        $this->staff = User::factory()->create([
            'name' => 'IT Staff One',
            'email' => 'staff1@mito.local',
            'password' => bcrypt('Test@123'),
            'role_id' => \App\Models\Role::where('slug', 'staff')->value('id'),
            'email_verified_at' => now(),
        ]);
    }

    // ─── Auth Pages ───────────────────────────────────────

    public function test_root_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login/admin');
        $response->assertStatus(200);
        $response->assertSee('MITO');
        $response->assertSee('Sign in');
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
            'description' => 'Test description for smoke test',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ]);

        $response = $this->get('/my-tickets');
        $response->assertStatus(200);
        $response->assertSee('TKT-000001');
        $response->assertSee('Test description for smoke test');
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
            'description' => 'Cannot connect to WiFi on the 3rd floor',
            'priority' => 'high',
            'status' => 'Waiting Confirmation',
        ]);

        $response = $this->get('/my-tickets?search=WiFi');
        $response->assertStatus(200);
        $response->assertSee('Cannot connect to WiFi on the 3rd floor');
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
            'description' => 'This issue was fully resolved',
            'priority' => 'low',
            'status' => 'Completed',
        ]);

        $response = $this->get('/my-tickets?status=Completed');
        $response->assertStatus(200);
        $response->assertSee('This issue was fully resolved');
    }

    // ─── Create Ticket ────────────────────────────────────

    public function test_create_ticket_page_loads(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/my-tickets/create');
        $response->assertStatus(200);
        $response->assertSee('Create New Ticket');
        $response->assertSee('Category');
        $response->assertDontSee('Subject');
        $response->assertSee('Description');
        // Regular users should NOT see Priority — only support users do.
    }

    public function test_create_ticket_with_valid_data(): void
    {
        $this->actingAs($this->user);

        $category = Category::first();
        $subCategory = SubCategory::first();

        $response = $this->post('/my-tickets', [
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'description' => 'The office printer on floor 3 is not responding.',
            'priority' => 'medium',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->user->id,
            'description' => 'The office printer on floor 3 is not responding.',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ]);
    }

    public function test_create_ticket_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/my-tickets', [
            'description' => '',
            'category_id' => '',
            'priority' => '',
        ]);

        $response->assertSessionHasErrors(['description']);
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
            'description' => 'Testing ticket detail page',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ]);

        $response = $this->get("/my-tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('TKT-000010');
        $response->assertSee('Testing ticket detail page');
        $response->assertSee('Add a comment');
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
            'description' => 'Testing comments',
            'priority' => 'low',
            'status' => 'Waiting Confirmation',
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
            'description' => 'Testing comment storage',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
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

    public function test_update_ticket_status_requires_staff_role(): void
    {
        // Regular users cannot change ticket status (backend enforcement).
        $this->actingAs($this->user);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000013',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing status permissions',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
            'assignee_id' => $this->staff->id,
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'In Progress',
            'problem_analysis' => 'Analysed the issue.',
        ]);

        $response->assertForbidden();
    }

    public function test_update_ticket_status_moves_to_in_progress(): void
    {
        $this->actingAs($this->staff);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000013',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing status change',
            'priority' => 'high',
            'status' => 'Waiting Confirmation',
            'assignee_id' => $this->staff->id,
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'In Progress',
            'problem_analysis' => 'Suspect faulty network adapter driver.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'In Progress',
            'problem_analysis' => 'Suspect faulty network adapter driver.',
        ]);
    }

    public function test_update_ticket_status_requires_problem_analysis(): void
    {
        $this->actingAs($this->staff);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000014',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing analysis requirement',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
            'assignee_id' => $this->staff->id,
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'In Progress',
        ]);

        $response->assertSessionHasErrors('problem_analysis');
    }

    public function test_update_ticket_status_requires_resolution_for_completed(): void
    {
        $this->actingAs($this->staff);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000015',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing resolution requirement',
            'priority' => 'medium',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'problem_analysis' => 'Analysed: broken cable.',
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'Completed',
        ]);

        $response->assertSessionHasErrors('resolution');
    }

    public function test_complete_ticket_records_completion_metadata(): void
    {
        $this->actingAs($this->staff);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000016',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing completion',
            'priority' => 'low',
            'status' => 'In Progress',
            'assignee_id' => $this->staff->id,
            'problem_analysis' => 'Analysed: faulty cable.',
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'Completed',
            'resolution' => 'Replaced the cable.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'Completed',
            'resolution' => 'Replaced the cable.',
            'completed_by' => $this->staff->id,
        ]);

        $this->assertNotNull(Ticket::find($ticket->id)->completed_at);
    }

    public function test_update_ticket_status_validates(): void
    {
        $this->actingAs($this->staff);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000017',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing validation',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
            'assignee_id' => $this->staff->id,
        ]);

        $response = $this->patch("/my-tickets/{$ticket->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_assign_to_me(): void
    {
        $this->actingAs($this->staff);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000018',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing assign to me',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ]);

        $response = $this->postJson("/tickets/{$ticket->id}/assign-to-me", [
            'problem_analysis' => 'Picked up and analysed.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assignee_id' => $this->staff->id,
            'status' => 'In Progress',
        ]);
    }

    public function test_assign_to_me_requires_analysis(): void
    {
        $this->actingAs($this->staff);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000019',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing assign analysis requirement',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
        ]);

        $response = $this->postJson("/tickets/{$ticket->id}/assign-to-me", []);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.problem_analysis', 'Problem analysis is required before you can take this ticket.');
    }

    public function test_assign_to_me_rejects_ticket_assigned_to_other_staff(): void
    {
        $this->actingAs($this->staff);

        $otherStaff = User::factory()->create([
            'role_id' => \App\Models\Role::where('slug', 'staff')->value('id'),
        ]);

        $category = Category::first();

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-000020',
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'description' => 'Testing assign conflict',
            'priority' => 'medium',
            'status' => 'Waiting Confirmation',
            'assignee_id' => $otherStaff->id,
        ]);

        // Staff cannot even see tickets assigned to another staff member.
        $response = $this->postJson("/tickets/{$ticket->id}/assign-to-me", [
            'problem_analysis' => 'Trying to take it.',
        ]);

        $response->assertStatus(404);
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