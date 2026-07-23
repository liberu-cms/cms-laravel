<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Liberu\Cms\Contracts\Content\WorkflowState;
use Liberu\Cms\Pages\Models\Page;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_can_be_created(): void
    {
        $user = User::factory()->create();
        Page::factory()->create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('cms_pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
    }

    public function test_page_has_fillable_attributes(): void
    {
        $page = new Page;

        foreach (['title', 'slug', 'content', 'status', 'user_id'] as $attribute) {
            $this->assertContains($attribute, $page->getFillable());
        }
    }

    public function test_page_published_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create([
            'user_id' => $user->id,
            'published_at' => '2025-01-01 00:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $page->published_at);
    }

    public function test_page_belongs_to_team(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Page)->team());
    }

    public function test_page_factory_creates_home_page(): void
    {
        $page = Page::factory()->home()->create();

        $this->assertEquals('home', $page->slug);
        $this->assertEquals('home', $page->template);
    }

    public function test_page_defaults_to_draft_state(): void
    {
        $this->assertSame(WorkflowState::Draft, Page::factory()->create()->workflowState());
    }

    public function test_page_nests_under_a_parent(): void
    {
        $parent = Page::factory()->create();
        $child = Page::factory()->create(['parent_id' => $parent->id]);

        $this->assertTrue($child->parent?->is($parent));
    }
}
