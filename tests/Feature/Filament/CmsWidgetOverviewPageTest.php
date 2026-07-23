<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Widgets\Filament\Pages\WidgetOverview;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
});

it('renders the widget overview grouped by area', function (): void {
    Livewire::test(WidgetOverview::class)
        ->assertSuccessful()
        ->assertSee('Sidebar')
        ->assertSee('Dashboard')
        ->assertSee('Footer');
});

it('lists a registered widget under its area', function (): void {
    Livewire::test(WidgetOverview::class)
        ->assertSee('Search');
});

it('exposes the registered widgets grouped by area', function (): void {
    $areas = collect(app(WidgetOverview::class)->areas())->keyBy('area');

    expect($areas->get('Sidebar')['widgets'])->toHaveCount(1)
        ->and($areas->get('Sidebar')['widgets'][0]['key'])->toBe('search');
});
