<?php

declare(strict_types=1);

namespace Liberu\Cms\Widgets;

use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;
use Liberu\Cms\Widgets\Widgets\SearchWidget;

final class WidgetsServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new WidgetsModule;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(WidgetRegistry::class);
    }

    protected function bootModule(): void
    {
        $this->app->make(WidgetRegistry::class)->register(new SearchWidget);
    }
}
