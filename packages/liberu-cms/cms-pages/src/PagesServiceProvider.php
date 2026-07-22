<?php

declare(strict_types=1);

namespace Liberu\Cms\Pages;

use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;
use Liberu\Cms\Pages\Contracts\PageRepositoryInterface;
use Liberu\Cms\Pages\Repositories\PageRepository;

final class PagesServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new PagesModule;
    }

    protected function registerModule(): void
    {
        $this->mergeModuleConfig(__DIR__.'/../config/pages.php', 'cms-pages');

        $this->app->singleton(PageRepositoryInterface::class, PageRepository::class);
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pages.php' => $this->app->configPath('cms-pages.php'),
            ], 'cms-pages-config');
        }
    }
}
