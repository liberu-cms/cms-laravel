<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Contracts\Events\EventBusInterface;
use Liberu\Cms\Contracts\Events\Theme\ThemeActivated;
use Liberu\Cms\Contracts\Theme\ThemeManagerInterface;
use Liberu\Cms\Themes\Exceptions\UnknownTheme;
use Liberu\Cms\Themes\Theme;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->base = sys_get_temp_dir().'/cms-theme-base-'.uniqid();
    $this->child = sys_get_temp_dir().'/cms-theme-child-'.uniqid();
    @mkdir($this->base.'/layouts', 0777, true);
    @mkdir($this->child, 0777, true);
    file_put_contents($this->base.'/layouts/app.blade.php', 'base app');
    file_put_contents($this->base.'/home.blade.php', 'base home');
    file_put_contents($this->child.'/home.blade.php', 'child home');

    $this->themes = app(ThemeManagerInterface::class);
    $this->themes->register(new Theme('base', 'Base', $this->base));
    $this->themes->register(new Theme('child', 'Child', $this->child, parent: 'base'));
});

it('registers and lists themes including the default', function (): void {
    expect($this->themes->all())->toHaveKeys(['base', 'child', 'default'])
        ->and($this->themes->get('child')?->parent())->toBe('base');
});

it('builds the inheritance chain nearest-first', function (): void {
    $keys = array_map(fn ($theme): string => $theme->key(), $this->themes->inheritanceChain('child'));

    expect($keys)->toBe(['child', 'base']);
});

it('switches the active theme and persists it', function (): void {
    $this->themes->setActive('child');

    expect($this->themes->active()?->key())->toBe('child');
});

it('announces a theme switch on the event bus', function (): void {
    $received = null;
    app(EventBusInterface::class)->listen(ThemeActivated::class, function (ThemeActivated $event) use (&$received): void {
        $received = $event;
    });

    $this->themes->setActive('base');

    expect($received)->toBeInstanceOf(ThemeActivated::class)
        ->and($received->themeKey)->toBe('base');
});

it('rejects activating an unknown theme', function (): void {
    expect(fn () => $this->themes->setActive('ghost'))->toThrow(UnknownTheme::class);
});

it('resolves a view from the active child theme', function (): void {
    $this->themes->setActive('child');

    expect($this->themes->resolveView('home'))->toBe($this->child.'/home.blade.php');
});

it('falls back to the parent theme for a view the child does not override', function (): void {
    $this->themes->setActive('child');

    expect($this->themes->resolveView('layouts.app'))->toBe($this->base.'/layouts/app.blade.php');
});

it('returns null for an unresolved view', function (): void {
    $this->themes->setActive('child');

    expect($this->themes->resolveView('nonexistent'))->toBeNull();
});
