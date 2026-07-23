<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Pages\Filament\Pages\ListPages;
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

it('renders the pages list', function (): void {
    Livewire::test(ListPages::class)->assertSuccessful();
});

it('lists page records', function (): void {
    $pages = Page::factory()->count(3)->create();

    Livewire::test(ListPages::class)->assertCanSeeTableRecords($pages);
});

it('creates a page through the modal', function (): void {
    Livewire::test(ListPages::class)
        ->callAction('create', [
            'title' => 'A Brand New Page',
            'template' => 'default',
            'status' => 'draft',
        ]);

    $this->assertDatabaseHas('cms_pages', ['title' => 'A Brand New Page']);
});

it('generates a slug when none is given', function (): void {
    Livewire::test(ListPages::class)
        ->callAction('create', [
            'title' => 'Slugless Page',
            'template' => 'default',
            'status' => 'draft',
        ]);

    $this->assertDatabaseHas('cms_pages', ['slug' => 'slugless-page']);
});

it('edits a page through the row action', function (): void {
    $page = Page::factory()->create(['title' => 'Original']);

    Livewire::test(ListPages::class)
        ->callTableAction('edit', $page, ['title' => 'Renamed']);

    expect($page->fresh()->title)->toBe('Renamed');
});

it('deletes a page through the row action', function (): void {
    $page = Page::factory()->create();

    Livewire::test(ListPages::class)->callTableAction('delete', $page);

    $this->assertModelMissing($page);
});
