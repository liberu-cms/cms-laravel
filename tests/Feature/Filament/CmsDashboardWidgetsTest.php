<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Admin\Filament\Widgets\ContentOverviewWidget;
use Liberu\Cms\Admin\Filament\Widgets\ModulesOverviewWidget;
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

it('registers the dashboard widgets on the panel', function (): void {
    $widgets = Filament::getPanel('app')->getWidgets();

    expect($widgets)
        ->toContain(ModulesOverviewWidget::class)
        ->toContain(ContentOverviewWidget::class);
});

it('renders the modules overview widget', function (): void {
    Livewire::test(ModulesOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Modules installed')
        ->assertSee('Enabled');
});

it('renders the content overview widget with module-contributed stats', function (): void {
    Livewire::test(ContentOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Pages')
        ->assertSee('Posts')
        ->assertSee('Media')
        ->assertSee('Menus');
});

it('reflects the live content count in the overview', function (): void {
    Page::factory()->count(4)->create();

    Livewire::test(ContentOverviewWidget::class)
        ->assertSee('Pages')
        ->assertSee('4');
});
