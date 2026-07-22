<?php

declare(strict_types=1);

namespace Liberu\Cms\Media;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Liberu\Cms\Contracts\Events\EventBusInterface;
use Liberu\Cms\Contracts\Media\MediaRepositoryInterface;
use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;
use Liberu\Cms\Media\Media\MediaRepository;
use Liberu\Cms\Media\Media\StoreUpload;

final class MediaServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new MediaModule;
    }

    protected function registerModule(): void
    {
        $this->mergeModuleConfig(__DIR__.'/../config/media.php', 'cms-media');

        $this->app->singleton(MediaRepositoryInterface::class, MediaRepository::class);

        $this->app->bind(StoreUpload::class, function (): StoreUpload {
            $config = $this->app->make(ConfigRepository::class);

            $disk = $config->get('cms-media.disk');
            $maxSize = $config->get('cms-media.max_size_kb');
            $mimeTypes = $config->get('cms-media.allowed_mime_types');

            return new StoreUpload(
                $this->app->make(EventBusInterface::class),
                is_string($disk) ? $disk : 'public',
                is_int($maxSize) ? $maxSize : 20480,
                is_array($mimeTypes) ? array_values(array_filter($mimeTypes, is_string(...))) : [],
            );
        });
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/media.php' => $this->app->configPath('cms-media.php'),
            ], 'cms-media-config');
        }
    }
}
