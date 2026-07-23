<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Admin\AdminResourceRegistry;
use Liberu\Cms\ContentTypes\Filament\ContentTypeResource;
use Liberu\Cms\Contracts\Admin\AdminResourceRegistryInterface;
use Liberu\Cms\Media\Filament\MediaResource;
use Liberu\Cms\Menus\Filament\MenuItemResource;
use Liberu\Cms\Menus\Filament\MenuResource;
use Liberu\Cms\Pages\Filament\PageResource;
use Liberu\Cms\Posts\Filament\PostResource;
use Liberu\Cms\Themes\Filament\Pages\ThemeManagement;
use Liberu\Cms\Widgets\Filament\Pages\WidgetOverview;

uses(RefreshDatabase::class);

it('deduplicates resources and pages registered for the same module', function (): void {
    $registry = new AdminResourceRegistry;

    $registry->registerResource('pages', 'A');
    $registry->registerResource('pages', 'A');
    $registry->registerResource('pages', 'B');
    $registry->registerPage('themes', 'P');
    $registry->registerPage('themes', 'P');

    expect($registry->resources())->toBe(['pages' => ['A', 'B']])
        ->and($registry->pages())->toBe(['themes' => ['P']]);
});

it('collects each content module resource into the shared registry', function (): void {
    $registry = app(AdminResourceRegistryInterface::class)->resources();

    expect($registry)
        ->toHaveKeys(['pages', 'posts', 'media', 'content-types'])
        ->and($registry['pages'])->toContain(PageResource::class)
        ->and($registry['posts'])->toContain(PostResource::class)
        ->and($registry['media'])->toContain(MediaResource::class)
        ->and($registry['content-types'])->toContain(ContentTypeResource::class);
});

it('registers every module resource onto the panel', function (): void {
    $resources = Filament::getPanel('app')->getResources();

    expect($resources)
        ->toContain(PageResource::class)
        ->toContain(PostResource::class)
        ->toContain(MediaResource::class)
        ->toContain(ContentTypeResource::class)
        ->toContain(MenuResource::class)
        ->toContain(MenuItemResource::class);
});

it('registers every module page onto the panel', function (): void {
    $pages = Filament::getPanel('app')->getPages();

    expect($pages)
        ->toContain(ThemeManagement::class)
        ->toContain(WidgetOverview::class);
});
