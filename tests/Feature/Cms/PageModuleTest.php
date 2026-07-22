<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Liberu\Cms\Contracts\Content\WorkflowState;
use Liberu\Cms\Contracts\Module\ModuleManagerInterface;
use Liberu\Cms\Core\Exceptions\ModuleDependencyException;
use Liberu\Cms\Media\Media\StoreUpload;
use Liberu\Cms\Pages\Contracts\PageRepositoryInterface;
use Liberu\Cms\Pages\Models\Page;

uses(RefreshDatabase::class);

it('auto-generates a unique slug from the title', function (): void {
    $first = Page::create(['title' => 'About Us']);
    $second = Page::create(['title' => 'About Us']);

    expect($first->slug)->toBe('about-us')
        ->and($second->slug)->toBe('about-us-2');
});

it('keeps an explicit slug', function (): void {
    $page = Page::create(['title' => 'Anything', 'slug' => 'custom-slug']);

    expect($page->slug)->toBe('custom-slug');
});

it('moves a page through its editorial lifecycle', function (): void {
    $page = Page::factory()->create();

    expect($page->workflowState())->toBe(WorkflowState::Draft);

    $page->publish();
    expect($page->fresh()->isLive())->toBeTrue();

    $page->archive();
    expect($page->fresh()->workflowState())->toBe(WorkflowState::Archived);
});

it('versions and reverts page content', function (): void {
    $page = Page::factory()->create(['content' => 'first draft']);
    $page->recordRevision();

    $page->update(['content' => 'second draft']);
    $page->recordRevision();

    expect($page->latestRevisionNumber())->toBe(2);

    $page->revertTo(1);
    expect($page->fresh()->content)->toBe('first draft');
});

it('nests pages hierarchically', function (): void {
    $parent = Page::factory()->create();
    $child = Page::factory()->create(['parent_id' => $parent->id]);

    expect($child->parent?->is($parent))->toBeTrue()
        ->and($parent->children)->toHaveCount(1);
});

it('resolves featured media through the media contract', function (): void {
    Storage::fake('public');
    $media = app(StoreUpload::class)(UploadedFile::fake()->image('hero.jpg'));

    $page = Page::factory()->create(['featured_media_id' => $media->mediaId()]);

    expect($page->featuredMedia()?->fileName())->toBe('hero.jpg');
});

it('returns null featured media when none is set', function (): void {
    expect(Page::factory()->create()->featuredMedia())->toBeNull();
});

it('queries pages through the repository', function (): void {
    $live = Page::factory()->published()->create(['slug' => 'live-one']);
    Page::factory()->create();

    $repository = app(PageRepositoryInterface::class);

    expect($repository->findBySlug('live-one')?->is($live))->toBeTrue()
        ->and($repository->published())->toHaveCount(1)
        ->and($repository->roots())->toHaveCount(2);
});

it('cannot disable media while pages depends on it', function (): void {
    expect(fn () => app(ModuleManagerInterface::class)->disable('media'))
        ->toThrow(ModuleDependencyException::class);
});
