<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Contracts\Access\PermissionRegistrarInterface;
use Liberu\Cms\Contracts\Module\ModuleManagerInterface;
use Liberu\Cms\Contracts\Module\ModuleRegistryInterface;

uses(RefreshDatabase::class);

it('registers the admin module in the registry', function (): void {
    $registry = app(ModuleRegistryInterface::class);

    expect($registry->has('admin'))->toBeTrue()
        ->and($registry->get('admin')?->name())->toBe('Admin');
});

it('enables the admin module by default and lets it be disabled', function (): void {
    $manager = app(ModuleManagerInterface::class);

    expect($manager->isEnabled('admin'))->toBeTrue();

    $manager->disable('admin');

    expect($manager->isEnabled('admin'))->toBeFalse();
});

it('declares the modules permission group', function (): void {
    $permissions = app(PermissionRegistrarInterface::class)->permissions();

    expect($permissions)
        ->toContain('modules.view')
        ->toContain('modules.manage');
});
