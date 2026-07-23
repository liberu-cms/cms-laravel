<?php

declare(strict_types=1);

namespace Liberu\Cms\Menus;

use Liberu\Cms\Contracts\Admin\AdminDashboardRegistryInterface;
use Liberu\Cms\Contracts\Admin\AdminResourceRegistryInterface;
use Liberu\Cms\Contracts\Admin\DashboardStat;
use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;
use Liberu\Cms\Menus\Contracts\MenuRepositoryInterface;
use Liberu\Cms\Menus\Filament\MenuItemResource;
use Liberu\Cms\Menus\Filament\MenuResource;
use Liberu\Cms\Menus\Models\Menu;
use Liberu\Cms\Menus\Repositories\MenuRepository;

final class MenusServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new MenusModule;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(MenuRepositoryInterface::class, MenuRepository::class);

        if ($this->app->bound(AdminResourceRegistryInterface::class)) {
            $registry = $this->app->make(AdminResourceRegistryInterface::class);
            $registry->registerResource('menus', MenuResource::class);
            $registry->registerResource('menus', MenuItemResource::class);
        }
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');

        if ($this->app->bound(AdminDashboardRegistryInterface::class)) {
            $this->app->make(AdminDashboardRegistryInterface::class)->registerStat(
                new DashboardStat('Menus', fn (): int => Menu::count(), 'heroicon-o-bars-3', 'primary'),
            );
        }
    }
}
