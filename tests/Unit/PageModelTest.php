<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_can_be_created(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create([
            'title'   => 'Test Page',
            'slug'    => 'test-page',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'slug'  => 'test-page',
        ]);
    }

    public function test_page_has_fillable_attributes(): void
    {
        $page = new Page();

        $this->assertContains('title', $page->getFillable());
        $this->assertContains('slug', $page->getFillable());
        $this->assertContains('content', $page->getFillable());
        $this->assertContains('status', $page->getFillable());
        $this->assertContains('user_id', $page->getFillable());
    }

    public function test_page_published_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create([
            'user_id'      => $user->id,
            'published_at' => '2025-01-01 00:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $page->published_at);
    }

    public function test_page_belongs_to_team(): void
    {
        $page = new Page();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $page->team()
        );
    }

    public function test_page_factory_creates_home_page(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->home()->create(['user_id' => $user->id]);

        $this->assertEquals('home', $page->slug);
        $this->assertEquals('Home', $page->title);
        $this->assertEquals('home', $page->template);
    }

    public function test_page_menu_link_attribute(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create([
            'slug'    => 'about',
            'user_id' => $user->id,
        ]);

        $this->assertStringContainsString('about', $page->getMenuLinkAttribute());
    }

    public function test_page_menu_name_attribute(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create([
            'title'   => 'About Us',
            'user_id' => $user->id,
        ]);

        $this->assertEquals('About Us', $page->getMenuNameAttribute());
    }
}
