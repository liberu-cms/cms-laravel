<?php

declare(strict_types=1);

namespace Liberu\Cms\Hello;

use Liberu\Cms\Contracts\Module\ModuleInterface;
use Liberu\Cms\Core\Module\ModuleServiceProvider;
use Liberu\Cms\Hello\Contracts\GreeterInterface;
use Liberu\Cms\Hello\Services\Greeter;

final class HelloServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new HelloModule;
    }

    protected function registerModule(): void
    {
        $this->mergeModuleConfig(__DIR__.'/../config/hello.php', 'hello');

        $this->app->bind(GreeterInterface::class, function (): Greeter {
            $greeting = config('hello.greeting', 'Hello, :name!');

            return new Greeter(is_string($greeting) ? $greeting : 'Hello, :name!');
        });
    }

    protected function bootModule(): void
    {
        $this->loadModuleMigrations(__DIR__.'/../database/migrations');
        $this->loadModuleRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__.'/../config/hello.php' => $this->app->configPath('hello.php'),
        ], 'cms-hello-config');
    }
}
