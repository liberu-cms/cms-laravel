<?php

use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Models\Collection;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
});

it('can render the list page', function (): void {
    Livewire::test(ListCollections::class)
        ->assertSuccessful();
});

it('can list collections in the table', function (): void {
    $collections = Collection::factory()->count(3)->create([
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListCollections::class)
        ->assertCanSeeTableRecords($collections);
});

it('can search collections by name', function (): void {
    $matchingCollection = Collection::factory()->create([
        'name' => 'My Unique Collection',
        'team_id' => $this->team->id,
    ]);
    $otherCollection = Collection::factory()->create([
        'name' => 'Something Else',
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListCollections::class)
        ->searchTable('My Unique Collection')
        ->assertCanSeeTableRecords([$matchingCollection])
        ->assertCanNotSeeTableRecords([$otherCollection]);
});

it('can render the create page', function (): void {
    Livewire::test(CreateCollection::class)
        ->assertSuccessful();
});

it('validates required fields on create', function (string $field): void {
    Livewire::test(CreateCollection::class)
        ->fillForm([$field => null])
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);
})->with(['name', 'slug']);

it('can create a collection', function (): void {
    Livewire::test(CreateCollection::class)
        ->fillForm([
            'name' => 'My Blog',
            'slug' => 'my-blog',
            'description' => 'A blog collection',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('collections', [
        'name' => 'My Blog',
        'slug' => 'my-blog',
    ]);
});

it('can render the edit page', function (): void {
    $collection = Collection::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->assertSuccessful();
});

it('populates the edit form with existing data', function (): void {
    $collection = Collection::factory()->create([
        'name' => 'Existing Collection',
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->assertFormSet(['name' => 'Existing Collection']);
});

it('can update a collection', function (): void {
    $collection = Collection::factory()->create([
        'name' => 'Old Name',
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm(['name' => 'Updated Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($collection->fresh()->name)->toBe('Updated Name');
});

it('can delete a collection from the list', function (): void {
    $collection = Collection::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(ListCollections::class)
        ->callTableBulkAction('delete', [$collection]);

    $this->assertModelMissing($collection);
});

it('has the correct model', function (): void {
    expect(CollectionResource::getModel())->toBe(Collection::class);
});
