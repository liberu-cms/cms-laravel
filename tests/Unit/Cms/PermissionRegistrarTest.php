<?php

declare(strict_types=1);

use Liberu\Cms\Contracts\Access\AccessScope;
use Liberu\Cms\Contracts\Access\PermissionGroup;
use Liberu\Cms\Users\Access\PermissionRegistrar;

it('builds fully-qualified permission names from a group', function (): void {
    $group = new PermissionGroup('pages', 'Pages', AccessScope::Content, ['view', 'publish']);

    expect($group->permissions())->toBe(['pages.view', 'pages.publish']);
});

it('collects and de-duplicates permissions across groups', function (): void {
    $registrar = new PermissionRegistrar;
    $registrar->register(new PermissionGroup('pages', 'Pages', AccessScope::Content, ['view']));
    $registrar->register(new PermissionGroup('media', 'Media', AccessScope::Media, ['view']));

    expect($registrar->permissions())->toBe(['pages.view', 'media.view']);
});

it('filters permissions by scope', function (): void {
    $registrar = new PermissionRegistrar;
    $registrar->register(new PermissionGroup('pages', 'Pages', AccessScope::Content, ['view']));
    $registrar->register(new PermissionGroup('media', 'Media', AccessScope::Media, ['view']));

    expect($registrar->permissionsForScope(AccessScope::Content))->toBe(['pages.view'])
        ->and($registrar->permissionsForScope(AccessScope::Media))->toBe(['media.view']);
});

it('is idempotent per group key so re-declaring replaces', function (): void {
    $registrar = new PermissionRegistrar;
    $registrar->register(new PermissionGroup('pages', 'Pages', AccessScope::Content, ['view']));
    $registrar->register(new PermissionGroup('pages', 'Pages', AccessScope::Content, ['view', 'publish']));

    expect($registrar->groups())->toHaveCount(1)
        ->and($registrar->permissions())->toBe(['pages.view', 'pages.publish']);
});
