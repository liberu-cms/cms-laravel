<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Contracts\Module\ModuleManagerInterface;
use Liberu\Cms\Core\Exceptions\ModuleDependencyException;

uses(RefreshDatabase::class);

it('enables a module by default', function (): void {
    expect(app(ModuleManagerInterface::class)->isEnabled('hello'))->toBeTrue();
});

it('persists a disable decision in the cms_modules table', function (): void {
    $manager = app(ModuleManagerInterface::class);

    $manager->disable('hello');

    expect($manager->isEnabled('hello'))->toBeFalse();
    $this->assertDatabaseHas('cms_modules', ['key' => 'hello', 'enabled' => false]);
});

it('re-enables a previously disabled module', function (): void {
    $manager = app(ModuleManagerInterface::class);

    $manager->disable('hello');
    $manager->enable('hello');

    expect($manager->isEnabled('hello'))->toBeTrue();
});

it('rejects toggling an unknown module', function (): void {
    expect(fn () => app(ModuleManagerInterface::class)->disable('ghost'))
        ->toThrow(ModuleDependencyException::class);
});

it('keeps the application responsive after disabling a module', function (): void {
    app(ModuleManagerInterface::class)->disable('hello');

    $this->get('/login')->assertSuccessful();
});
