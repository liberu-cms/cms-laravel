<?php

use App\Filament\Resources\CollectionItems\CollectionItemResource;
use App\Filament\Resources\CollectionItems\Pages\CreateCollectionItem;
use App\Filament\Resources\CollectionItems\Pages\EditCollectionItem;
use App\Filament\Resources\CollectionItems\Pages\ListCollectionItems;
use App\Filament\Resources\CollectionItems\Pages\ViewCollectionItem;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['user_id' => $this->user->id]);
    $this->collection = Collection::factory()->create(['team_id' => $this->team->id]);
    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
});

it('can render the list page', function (): void {
    Livewire::test(ListCollectionItems::class)
        ->assertSuccessful();
});

it('can list collection items in the table', function (): void {
    $items = CollectionItem::factory()->count(3)->create([
        'collection_id' => $this->collection->id,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListCollectionItems::class)
        ->assertCanSeeTableRecords($items);
});

it('shows view and edit actions per row', function (): void {
    $item = CollectionItem::factory()->create([
        'collection_id' => $this->collection->id,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListCollectionItems::class)
        ->assertTableActionExists('view')
        ->assertTableActionExists('edit');
});

it('can render the create page', function (): void {
    Livewire::test(CreateCollectionItem::class)
        ->assertSuccessful();
});

it('validates required fields on create', function (string $field): void {
    Livewire::test(CreateCollectionItem::class)
        ->fillForm([$field => null])
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);
})->with(['title', 'slug', 'status']);

it('can create a collection item', function (): void {
    Livewire::test(CreateCollectionItem::class)
        ->fillForm([
            'collection_id' => $this->collection->id,
            'title' => 'My New Post',
            'slug' => 'my-new-post',
            'content' => 'Post content',
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('collection_items', [
        'title' => 'My New Post',
        'slug' => 'my-new-post',
    ]);
});

it('can render the view page', function (): void {
    $item = CollectionItem::factory()->create([
        'collection_id' => $this->collection->id,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ViewCollectionItem::class, ['record' => $item->getRouteKey()])
        ->assertSuccessful();
});

it('can render the edit page', function (): void {
    $item = CollectionItem::factory()->create([
        'collection_id' => $this->collection->id,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCollectionItem::class, ['record' => $item->getRouteKey()])
        ->assertSuccessful();
});

it('populates the edit form with existing data', function (): void {
    $item = CollectionItem::factory()->create([
        'title' => 'Existing Title',
        'collection_id' => $this->collection->id,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCollectionItem::class, ['record' => $item->getRouteKey()])
        ->assertFormSet(['title' => 'Existing Title']);
});

it('can update a collection item', function (): void {
    $item = CollectionItem::factory()->create([
        'title' => 'Old Title',
        'collection_id' => $this->collection->id,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCollectionItem::class, ['record' => $item->getRouteKey()])
        ->fillForm(['title' => 'Updated Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($item->fresh()->title)->toBe('Updated Title');
});

it('can delete a collection item from the list', function (): void {
    $item = CollectionItem::factory()->create([
        'collection_id' => $this->collection->id,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListCollectionItems::class)
        ->callTableBulkAction('delete', [$item]);

    $this->assertModelMissing($item);
});

it('has the correct model', function (): void {
    expect(CollectionItemResource::getModel())->toBe(CollectionItem::class);
});
