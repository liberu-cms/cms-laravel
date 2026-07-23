<?php

declare(strict_types=1);

namespace Liberu\Cms\ContentTypes;

use Liberu\Cms\ContentTypes\Contracts\ContentEntryRepositoryInterface;
use Liberu\Cms\ContentTypes\Filament\ContentEntryResource;
use Liberu\Cms\ContentTypes\Filament\ContentTypeResource;
use Liberu\Cms\ContentTypes\Models\ContentEntry;
use Liberu\Cms\ContentTypes\Repositories\ContentEntryRepository;
use Liberu\Cms\ContentTypes\Schema\SchemaValidator;
use Liberu\Cms\Contracts\Admin\AdminDashboardRegistryInterface;
use Liberu\Cms\Contracts\Admin\AdminResourceRegistryInterface;
use Liberu\Cms\Contracts\Admin\DashboardStat;
use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;

final class ContentTypesServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new ContentTypesModule;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(ContentEntryRepositoryInterface::class, ContentEntryRepository::class);
        $this->app->singleton(SchemaValidator::class);

        if ($this->app->bound(AdminResourceRegistryInterface::class)) {
            $registry = $this->app->make(AdminResourceRegistryInterface::class);
            $registry->registerResource('content-types', ContentTypeResource::class);
            $registry->registerResource('content-types', ContentEntryResource::class);
        }
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');

        if ($this->app->bound(AdminDashboardRegistryInterface::class)) {
            $this->app->make(AdminDashboardRegistryInterface::class)->registerStat(
                new DashboardStat('Content entries', fn (): int => ContentEntry::count(), 'heroicon-o-rectangle-stack', 'primary'),
            );
        }
    }
}
