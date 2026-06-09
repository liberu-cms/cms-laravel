<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
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
    $this->collection = Collection::factory()->create(['team_id' => $this->team->id]);
    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
});

it('can render the list page', function (): void {
    Livewire::test(ListCategories::class)
        ->assertSuccessful();
});

it('can list categories in the table', function (): void {
    $categories = collect(range(1, 3))->map(fn () => Category::create([
        'name' => fake()->word(),
        'slug' => fake()->unique()->slug(),
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]));

    Livewire::test(ListCategories::class)
        ->assertCanSeeTableRecords($categories);
});

it('can search categories by name', function (): void {
    $matchingCategory = Category::create([
        'name' => 'Unique Category Name',
        'slug' => 'unique-category-name',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);
    $otherCategory = Category::create([
        'name' => 'Other Category',
        'slug' => 'other-category',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListCategories::class)
        ->searchTable('Unique Category Name')
        ->assertCanSeeTableRecords([$matchingCategory])
        ->assertCanNotSeeTableRecords([$otherCategory]);
});

it('can render the create page', function (): void {
    Livewire::test(CreateCategory::class)
        ->assertSuccessful();
});

it('validates required fields on create', function (string $field): void {
    Livewire::test(CreateCategory::class)
        ->fillForm([$field => null])
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);
})->with(['name', 'slug']);

it('can create a category', function (): void {
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'collection_id' => $this->collection->id,
            'name' => 'Tech',
            'slug' => 'tech',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Tech',
        'slug' => 'tech',
    ]);
});

it('can render the edit page', function (): void {
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertSuccessful();
});

it('populates the edit form with existing data', function (): void {
    $category = Category::create([
        'name' => 'Existing Category',
        'slug' => 'existing-category',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertFormSet(['name' => 'Existing Category']);
});

it('can update a category', function (): void {
    $category = Category::create([
        'name' => 'Old Name',
        'slug' => 'old-name',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'Updated Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->fresh()->name)->toBe('Updated Name');
});

it('can delete a category from the list', function (): void {
    $category = Category::create([
        'name' => 'To Delete',
        'slug' => 'to-delete',
        'collection_id' => $this->collection->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListCategories::class)
        ->callTableBulkAction('delete', [$category]);

    $this->assertModelMissing($category);
});

it('has the correct model', function (): void {
    expect(CategoryResource::getModel())->toBe(Category::class);
});
