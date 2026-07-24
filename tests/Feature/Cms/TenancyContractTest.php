<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use App\Support\FilamentTenantResolver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Contracts\Tenancy\TenantModelResolverInterface;
use Liberu\Cms\Pages\Models\Page;
use Liberu\Cms\Posts\Models\Category;

uses(RefreshDatabase::class);

it('resolves the host tenant model through the contract', function (): void {
    // phpunit.xml sets MULTITENANCY=true, so config('permission.teams') is on.
    expect(app(TenantModelResolverInterface::class))->toBeInstanceOf(FilamentTenantResolver::class)
        ->and(app(TenantModelResolverInterface::class)->tenantModel())->toBe(Team::class);
});

it('gives a module model a team relationship without importing the host Team', function (): void {
    $user = User::factory()->create();
    $team = $user->createPersonalTeam();

    $page = Page::factory()->create(['team_id' => $team->id]);

    expect($page->team())->toBeInstanceOf(BelongsTo::class)
        ->and($page->team)->not->toBeNull()
        ->and($page->team->is($team))->toBeTrue();
});

it('scopes a taxonomy model to its team via the contract-backed relationship', function (): void {
    $user = User::factory()->create();
    $team = $user->createPersonalTeam();

    $category = Category::factory()->create(['team_id' => $team->id]);

    expect($category->team->is($team))->toBeTrue();
});
