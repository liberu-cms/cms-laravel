<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Contracts\Access\AccessControlInterface;
use Liberu\Cms\Contracts\Module\ModuleManagerInterface;
use Liberu\Cms\Core\Exceptions\ModuleDependencyException;
use Liberu\Cms\Users\Access\AccessControl;
use Liberu\Cms\Users\Access\SyncPermissions;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Create a user whose single role grants exactly the given permissions, within
 * their own team's context, and act as them. Avoids the super_admin role so no
 * gate bypass masks the check.
 *
 * @param  array<int, string>  $permissions
 */
function actingUserGranted(array $permissions): User
{
    $user = User::factory()->create();
    $team = $user->createPersonalTeam();
    setPermissionsTeamId($team->id);

    app(SyncPermissions::class)();

    $role = Role::create(['name' => 'scoped-role', 'team_id' => $team->id, 'guard_name' => 'web']);
    $role->givePermissionTo($permissions);
    $user->syncRoles([$role]);

    test()->actingAs($user);
    setPermissionsTeamId($team->id);

    return $user;
}

it('resolves the access-control contract from the container', function (): void {
    expect(app(AccessControlInterface::class))->toBeInstanceOf(AccessControl::class);
});

it('grants an ability the user\'s role includes', function (): void {
    actingUserGranted(['users.view']);

    expect(app(AccessControlInterface::class)->can('users.view'))->toBeTrue();
});

it('denies an ability the user\'s role does not include', function (): void {
    actingUserGranted(['users.view']);

    $access = app(AccessControlInterface::class);

    expect($access->can('users.delete'))->toBeFalse()
        ->and($access->cannot('users.delete'))->toBeTrue();
});

it('denies everything when no user is authenticated', function (): void {
    expect(app(AccessControlInterface::class)->can('users.view'))->toBeFalse();
});

it('checks a specific user via canForUser', function (): void {
    $user = actingUserGranted(['users.view']);
    auth()->logout();

    $access = app(AccessControlInterface::class);

    expect($access->canForUser($user, 'users.view'))->toBeTrue()
        ->and($access->canForUser($user, 'users.delete'))->toBeFalse();
});

it('throws when authorizing a denied ability', function (): void {
    actingUserGranted(['users.view']);

    expect(fn () => app(AccessControlInterface::class)->authorize('users.delete'))
        ->toThrow(AuthorizationException::class);
});

it('does not throw when authorizing a granted ability', function (): void {
    actingUserGranted(['users.view']);

    app(AccessControlInterface::class)->authorize('users.view');
})->throwsNoExceptions();

it('keeps the Users module foundational so it cannot be disabled', function (): void {
    expect(fn () => app(ModuleManagerInterface::class)->disable('users'))
        ->toThrow(ModuleDependencyException::class);
});
