<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Contracts\Theme\ThemeManagerInterface;
use Liberu\Cms\Themes\Filament\Pages\ThemeManagement;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($this->team);
});

it('renders the themes page listing the registered themes', function (): void {
    Livewire::test(ThemeManagement::class)
        ->assertSuccessful()
        ->assertSee('Default');
});

it('activates a registered theme', function (): void {
    Livewire::test(ThemeManagement::class)
        ->call('activate', 'default');

    expect(app(ThemeManagerInterface::class)->active()?->key())->toBe('default');
});

it('leaves the active theme unchanged when activating an unknown theme', function (): void {
    Livewire::test(ThemeManagement::class)
        ->call('activate', 'ghost');

    expect(app(ThemeManagerInterface::class)->active()?->key())->toBe('default');
});
