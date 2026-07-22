<?php

declare(strict_types=1);

namespace Liberu\Cms\Posts;

use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;
use Liberu\Cms\Posts\Contracts\PostRepositoryInterface;
use Liberu\Cms\Posts\Repositories\PostRepository;

final class PostsServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new PostsModule;
    }

    protected function registerModule(): void
    {
        $this->mergeModuleConfig(__DIR__.'/../config/posts.php', 'cms-posts');

        $this->app->singleton(PostRepositoryInterface::class, PostRepository::class);
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/posts.php' => $this->app->configPath('cms-posts.php'),
            ], 'cms-posts-config');
        }
    }
}
