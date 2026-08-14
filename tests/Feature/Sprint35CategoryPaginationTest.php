<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint35CategoryPaginationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

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
    }

    public function test_categories_loaded_from_controller(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/categories');
        $response->assertStatus(200);

        // Controller must load all categories and deliver them to the view.
        $this->assertGreaterThan(0, Category::count());
        foreach (Category::all() as $category) {
            $response->assertSee($category->name);
        }
    }

    public function test_expected_seeded_category_names_are_delivered(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/categories');
        $response->assertStatus(200);

        // These names are expected from CategorySeeder and must appear in the rendered response
        // (they are delivered both in the client-side x-data JSON and the server-rendered dropdown).
        foreach (['CCTV', 'Email', 'Hardware', 'Microsoft 365', 'Network', 'Printer'] as $name) {
            $response->assertSee($name, "Expected seeded category '$name' to be present in the response");
        }
    }

    public function test_all_category_data_delivered_to_client_side_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/categories');
        $response->assertStatus(200);

        $content = $response->getContent();

        // The Alpine x-data must carry the categories array and client-side pagination config.
        $this->assertStringContainsString('x-data="', $content, 'Alpine x-data attribute must be present');
        $this->assertStringContainsString('categories:', $content, 'x-data must deliver the categories array');
        $this->assertStringContainsString('perPage: 5', $content, 'Client-side pagination must use 5 per page');
        $this->assertStringContainsString('get filtered()', $content, 'Client-side search/filter logic must be present');
        $this->assertStringContainsString('get paginated()', $content, 'Client-side pagination logic must be present');
    }

    public function test_client_side_pagination_controls_exist(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/categories');
        $response->assertStatus(200);

        $response->assertSee('perPage: 5');
        $response->assertSee('Search categories');
        $response->assertSee('Previous');
        $response->assertSee('Next');
    }

    public function test_subcategory_parent_dropdown_contains_all_categories(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/categories');
        $response->assertStatus(200);

        // The parent-category <select> (New Subcategory form) must list EVERY category.
        foreach (Category::all() as $category) {
            $response->assertSee($category->name);
        }
    }

    public function test_no_raw_javascript_visible_on_categories_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/categories');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Unrelated raw framework JavaScript must not appear as visible text before <body>.
        $jsSnippet = 'if (!r.ok) throw new Error(); return r.json();';
        $bodyPos = strpos($content, '<body');

        if ($bodyPos !== false) {
            $beforeBody = substr($content, 0, $bodyPos);
            $this->assertStringNotContainsString($jsSnippet, $beforeBody, 'Raw JavaScript should not appear as visible text before <body>');
        }
    }
}
