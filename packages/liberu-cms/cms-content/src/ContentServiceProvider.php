<?php

declare(strict_types=1);

namespace Liberu\Cms\Content;

use Illuminate\Support\ServiceProvider;
use Liberu\Cms\Content\Workflow\Workflow;
use Liberu\Cms\Contracts\Content\WorkflowInterface;

/**
 * Content foundation provider. Not a module — this is always-on shared
 * infrastructure (like the kernel), so it registers unconditionally.
 */
final class ContentServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(WorkflowInterface::class, Workflow::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
