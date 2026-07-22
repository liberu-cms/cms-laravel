<?php

declare(strict_types=1);

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Collection as CollectionModel;
use App\Models\Page;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->owner = User::factory()->create();
    $this->teamA = Team::factory()->create(['user_id' => $this->owner->id]);
    $this->teamB = Team::factory()->create(['user_id' => $this->owner->id]);

    $this->actingAs($this->owner);

    $panel = Filament::getPanel('app');
    Filament::setCurrentPanel($panel);

    // Register the tenancy scope + creation observer for the resources under
    // test, mirroring what Panel::boot() does during a real request.
    foreach ([PageResource::class, CollectionResource::class] as $resource) {
        $resource::registerTenancyModelGlobalScope($panel);
        $resource::observeTenancyModelCreation($panel);
    }

    Filament::setTenant($this->teamA);
});

/**
 * Create a record while a given team is the active tenant so Filament's
 * creation observer stamps that team onto it.
 */
function createForTeam(string $model, Team $team, array $attributes = []): mixed
{
    Filament::setTenant($team);

    return $model::factory()->create($attributes);
}

it('lists only the current tenant\'s pages', function (): void {
    $mine = createForTeam(Page::class, $this->teamA, ['user_id' => $this->owner->id]);
    $other = createForTeam(Page::class, $this->teamB, ['user_id' => $this->owner->id]);

    Filament::setTenant($this->teamA);

    Livewire::test(ListPages::class)
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$other]);
});

it('isolates a second tenant-scoped resource the same way', function (): void {
    $mine = createForTeam(CollectionModel::class, $this->teamA);
    $other = createForTeam(CollectionModel::class, $this->teamB);

    Filament::setTenant($this->teamA);

    Livewire::test(ListCollections::class)
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$other]);
});

it('stamps the active tenant onto newly created records automatically', function (): void {
    $page = createForTeam(Page::class, $this->teamB, ['user_id' => $this->owner->id]);

    expect($page->team_id)->toBe($this->teamB->id);
});

it('excludes other tenants\' records from a scoped query', function (): void {
    createForTeam(Page::class, $this->teamA, ['user_id' => $this->owner->id]);
    createForTeam(Page::class, $this->teamB, ['user_id' => $this->owner->id]);

    Filament::setTenant($this->teamA);

    expect(Page::query()->pluck('team_id')->unique()->values()->all())
        ->toBe([$this->teamA->id]);
});

it('denies access to a tenant the user does not belong to', function (): void {
    auth()->logout();
    $stranger = Team::factory()->create();
    $this->actingAs($this->owner);

    expect($this->owner->canAccessTenant($this->teamA))->toBeTrue()
        ->and($this->owner->canAccessTenant($stranger))->toBeFalse();
});

it('exposes the user\'s teams as available tenants', function (): void {
    $tenants = $this->owner->getTenants(Filament::getPanel('app'));

    expect($tenants->pluck('id'))->toContain($this->teamA->id, $this->teamB->id);
});

it('gives a newly registered-style user a personal team via createPersonalTeam', function (): void {
    $user = User::factory()->create();

    $team = $user->createPersonalTeam();

    expect($user->current_team_id)->toBe($team->id)
        ->and($user->fresh()->belongsToTeam($team))->toBeTrue();
});
