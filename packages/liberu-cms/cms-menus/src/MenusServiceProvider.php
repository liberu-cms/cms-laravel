<?php

declare(strict_types=1);

namespace Liberu\Cms\Menus;

use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;
use Liberu\Cms\Menus\Contracts\MenuRepositoryInterface;
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
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');
    }
}
