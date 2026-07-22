<?php

declare(strict_types=1);

namespace Liberu\Cms\ContentTypes;

use Liberu\Cms\ContentTypes\Contracts\ContentEntryRepositoryInterface;
use Liberu\Cms\ContentTypes\Repositories\ContentEntryRepository;
use Liberu\Cms\ContentTypes\Schema\SchemaValidator;
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
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');
    }
}
