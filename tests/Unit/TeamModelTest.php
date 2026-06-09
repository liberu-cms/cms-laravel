<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function (): void {
    $team = new Team;

    expect($team->getFillable())
        ->toContain('user_id')
        ->toContain('name')
        ->toContain('personal_team');
});

it('casts personal_team to boolean', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id, 'personal_team' => 1]);

    expect($team->personal_team)->toBeBool()->toBeTrue();
});

it('has owner relationship returning belongs to', function (): void {
    $team = new Team;

    expect($team->owner())
        ->toBeInstanceOf(BelongsTo::class);
});

it('has users relationship returning belongs to many', function (): void {
    $team = new Team;

    expect($team->users())
        ->toBeInstanceOf(BelongsToMany::class);
});

it('owner relationship returns the correct user', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect($team->owner->id)->toBe($owner->id);
});

it('allUsers returns merged collection of owner and members', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $team->users()->attach($member);

    $allUsers = $team->allUsers();

    expect($allUsers)->toHaveCount(2);
    expect($allUsers->contains($owner))->toBeTrue();
    expect($allUsers->contains($member))->toBeTrue();
});

it('hasUser returns true for team members', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $team->users()->attach($member);

    expect($team->hasUser($member))->toBeTrue();
});

it('hasUserWithEmail returns true for owner email', function (): void {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect($team->hasUserWithEmail('owner@example.com'))->toBeTrue();
});

it('hasUserWithEmail returns false for unknown email', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect($team->hasUserWithEmail('nobody@example.com'))->toBeFalse();
});

it('removeUser detaches user from team', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $team->users()->attach($member);

    $team->removeUser($member);

    $team->refresh();
    expect($team->users->contains($member))->toBeFalse();
});

it('removeUser nulls current_team_id when it matches the team', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $team->users()->attach($member);
    $member->forceFill(['current_team_id' => $team->id])->save();

    $team->removeUser($member);

    expect($member->fresh()->current_team_id)->toBeNull();
});

it('creates roles when team is created', function (): void {
    $owner = User::factory()->create();
    Team::factory()->create(['user_id' => $owner->id]);

    $this->assertDatabaseHas('roles', ['name' => 'super_admin', 'guard_name' => 'web']);
    $this->assertDatabaseHas('roles', ['name' => 'editor', 'guard_name' => 'web']);
    $this->assertDatabaseHas('roles', ['name' => 'viewer', 'guard_name' => 'web']);
});
