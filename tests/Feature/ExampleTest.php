<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that the root URL shows the public welcome page.
     */
    public function test_root_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('MITO');
    }

    /**
     * Test that a role-specific login page is accessible.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login/admin');

        $response->assertStatus(200);
        $response->assertSee('Sign in');
    }
}
