<?php

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Pages\Models\Page;
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
    Livewire::test(ListPages::class)
        ->assertSuccessful();
});

it('can list pages in the table', function (): void {
    $pages = Page::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListPages::class)
        ->assertCanSeeTableRecords($pages);
});

it('can search pages by title', function (): void {
    $matchingPage = Page::factory()->create([
        'title' => 'My Unique Title',
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);
    $otherPage = Page::factory()->create([
        'title' => 'Something Else',
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListPages::class)
        ->searchTable('My Unique Title')
        ->assertCanSeeTableRecords([$matchingPage])
        ->assertCanNotSeeTableRecords([$otherPage]);
});

it('can render the create page', function (): void {
    Livewire::test(CreatePage::class)
        ->assertSuccessful();
});

it('validates required fields on create', function (string $field): void {
    Livewire::test(CreatePage::class)
        ->fillForm([$field => null])
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);
})->with(['title', 'slug', 'content', 'template', 'status']);

it('can create a page', function (): void {
    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'My New Page',
            'slug' => 'my-new-page',
            'content' => 'Page content',
            'template' => 'default',
            'status' => 'published',
            'user_id' => $this->user->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('cms_pages', [
        'title' => 'My New Page',
        'slug' => 'my-new-page',
    ]);
});

it('can render the edit page', function (): void {
    $page = Page::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertSuccessful();
});

it('populates the edit form with existing data', function (): void {
    $page = Page::factory()->create([
        'title' => 'Existing Title',
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormSet(['title' => 'Existing Title']);
});

it('can update a page', function (): void {
    $page = Page::factory()->create([
        'title' => 'Old Title',
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm(['title' => 'Updated Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->fresh()->title)->toBe('Updated Title');
});

it('can delete a page from the list', function (): void {
    $page = Page::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Livewire::test(ListPages::class)
        ->callTableBulkAction('delete', [$page]);

    $this->assertModelMissing($page);
});

it('has the correct model', function (): void {
    expect(PageResource::getModel())->toBe(Page::class);
});
