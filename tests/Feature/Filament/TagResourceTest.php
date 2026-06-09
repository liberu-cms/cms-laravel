<?php

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Tags\TagResource;
use App\Models\Collection;
use App\Models\Tag;
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
    Livewire::test(ListTags::class)
        ->assertSuccessful();
});

it('can list tags in the table', function (): void {
    $tags = collect(range(1, 3))->map(fn () => Tag::create([
        'name' => fake()->word(),
        'slug' => fake()->unique()->slug(),
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]));

    Livewire::test(ListTags::class)
        ->assertCanSeeTableRecords($tags);
});

it('can search tags by name', function (): void {
    $matchingTag = Tag::create([
        'name' => 'Unique Tag Name',
        'slug' => 'unique-tag-name',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);
    $otherTag = Tag::create([
        'name' => 'Other Tag',
        'slug' => 'other-tag',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListTags::class)
        ->searchTable('Unique Tag Name')
        ->assertCanSeeTableRecords([$matchingTag])
        ->assertCanNotSeeTableRecords([$otherTag]);
});

it('can render the create page', function (): void {
    Livewire::test(CreateTag::class)
        ->assertSuccessful();
});

it('validates required fields on create', function (string $field): void {
    Livewire::test(CreateTag::class)
        ->fillForm([$field => null])
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);
})->with(['name', 'slug']);

it('can create a tag', function (): void {
    Livewire::test(CreateTag::class)
        ->fillForm([
            'collection_id' => $this->collection->id,
            'name' => 'Laravel',
            'slug' => 'laravel',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('tags', [
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
});

it('can render the edit page', function (): void {
    $tag = Tag::create([
        'name' => 'Test Tag',
        'slug' => 'test-tag',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertSuccessful();
});

it('populates the edit form with existing data', function (): void {
    $tag = Tag::create([
        'name' => 'Existing Tag',
        'slug' => 'existing-tag',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertFormSet(['name' => 'Existing Tag']);
});

it('can update a tag', function (): void {
    $tag = Tag::create([
        'name' => 'Old Name',
        'slug' => 'old-name',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm(['name' => 'Updated Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tag->fresh()->name)->toBe('Updated Name');
});

it('can delete a tag from the list', function (): void {
    $tag = Tag::create([
        'name' => 'To Delete',
        'slug' => 'to-delete',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListTags::class)
        ->callTableBulkAction('delete', [$tag]);

    $this->assertModelMissing($tag);
});

it('has the correct model', function (): void {
    expect(TagResource::getModel())->toBe(Tag::class);
});
