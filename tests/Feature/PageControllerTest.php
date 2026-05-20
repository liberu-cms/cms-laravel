<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_successful_response(): void
    {
        User::factory()->create();
        Page::factory()->home()->create();

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_returns_404_when_no_home_page_exists(): void
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }

    public function test_page_by_slug_returns_successful_response(): void
    {
        $user = User::factory()->create();
        Page::factory()->create([
            'title'    => 'About',
            'slug'     => 'about',
            'template' => 'default',
            'status'   => 'published',
            'user_id'  => $user->id,
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
    }

    public function test_page_by_slug_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/nonexistent-page');

        $response->assertStatus(404);
    }

    public function test_home_page_uses_home_template(): void
    {
        User::factory()->create();
        Page::factory()->home()->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('templates.home');
    }

    public function test_page_uses_default_template(): void
    {
        $user = User::factory()->create();
        Page::factory()->create([
            'slug'     => 'test-page',
            'template' => 'default',
            'user_id'  => $user->id,
        ]);

        $response = $this->get('/test-page');

        $response->assertStatus(200);
        $response->assertViewIs('templates.default');
    }

    public function test_page_view_receives_page_variable(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create([
            'title'   => 'My Test Page',
            'slug'    => 'my-test-page',
            'user_id' => $user->id,
        ]);

        $response = $this->get('/my-test-page');

        $response->assertStatus(200);
        $response->assertViewHas('page', fn ($viewPage) => $viewPage->id === $page->id);
    }
}
