<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Contracts\Content\WorkflowState;
use Liberu\Cms\Contracts\Module\ModuleManagerInterface;
use Liberu\Cms\Posts\Contracts\PostRepositoryInterface;
use Liberu\Cms\Posts\Models\Category;
use Liberu\Cms\Posts\Models\Post;
use Liberu\Cms\Posts\Models\Tag;

uses(RefreshDatabase::class);

it('auto-generates slug and excerpt on save', function (): void {
    $post = Post::create([
        'title' => 'Hello World',
        'content' => '<p>The quick brown fox jumps over the lazy dog.</p>',
    ]);

    expect($post->slug)->toBe('hello-world')
        ->and($post->excerpt)->toContain('The quick brown fox')
        ->and($post->excerpt)->not->toContain('<p>');
});

it('keeps an explicit excerpt', function (): void {
    $post = Post::create(['title' => 'X', 'content' => 'body', 'excerpt' => 'custom']);

    expect($post->excerpt)->toBe('custom');
});

it('moves a post through its publishing workflow', function (): void {
    $post = Post::factory()->create();

    expect($post->workflowState())->toBe(WorkflowState::Draft);

    $post->schedule(now()->addWeek());
    expect($post->fresh()->isPublished())->toBeTrue()
        ->and($post->fresh()->isLive())->toBeFalse();
});

it('versions and reverts post content', function (): void {
    $post = Post::factory()->create(['content' => 'original']);
    $post->recordRevision();
    $post->update(['content' => 'edited']);

    $post->revertTo(1);

    expect($post->fresh()->content)->toBe('original');
});

it('attaches categories and tags', function (): void {
    $post = Post::factory()->create();
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();

    $post->categories()->attach($category);
    $post->tags()->attach($tag);

    expect($post->categories)->toHaveCount(1)
        ->and($post->tags)->toHaveCount(1)
        ->and($category->posts)->toHaveCount(1);
});

it('generates unique category slugs from the name', function (): void {
    $a = Category::create(['name' => 'Tech News']);
    $b = Category::create(['name' => 'Tech News']);

    expect($a->slug)->toBe('tech-news')
        ->and($b->slug)->toBe('tech-news-2');
});

it('queries live, featured, and taxonomy-filtered posts through the repository', function (): void {
    $category = Category::factory()->create(['slug' => 'news']);
    $tag = Tag::factory()->create(['slug' => 'php']);

    $featured = Post::factory()->published()->featured()->create();
    $featured->categories()->attach($category);
    $featured->tags()->attach($tag);

    Post::factory()->published()->create();
    Post::factory()->create();

    $repository = app(PostRepositoryInterface::class);

    expect($repository->published())->toHaveCount(2)
        ->and($repository->featured())->toHaveCount(1)
        ->and($repository->byCategory('news'))->toHaveCount(1)
        ->and($repository->byTag('php'))->toHaveCount(1)
        ->and($repository->byCategory('missing'))->toHaveCount(0);
});

it('cannot disable media while posts depends on it', function (): void {
    expect(app(ModuleManagerInterface::class)->dependentsOf('media'))
        ->toContain('posts');
});
