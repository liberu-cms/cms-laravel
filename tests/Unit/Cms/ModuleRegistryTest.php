<?php

declare(strict_types=1);

use Liberu\Cms\Core\Module\ModuleRegistry;

it('registers and resolves modules by key', function (): void {
    $registry = new ModuleRegistry;
    $module = fakeModule('pages');

    $registry->register($module);

    expect($registry->has('pages'))->toBeTrue()
        ->and($registry->get('pages'))->toBe($module)
        ->and($registry->all())->toHaveKey('pages');
});

it('returns null for unknown modules', function (): void {
    expect((new ModuleRegistry)->get('ghost'))->toBeNull();
});

it('is idempotent so re-registration replaces rather than duplicates', function (): void {
    $registry = new ModuleRegistry;

    $registry->register(fakeModule('pages'));
    $registry->register(fakeModule('pages'));

    expect($registry->all())->toHaveCount(1);
});
