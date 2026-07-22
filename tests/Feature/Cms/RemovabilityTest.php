<?php

declare(strict_types=1);

use Liberu\Cms\Contracts\Events\EventBusInterface;
use Liberu\Cms\Contracts\Module\ModuleManagerInterface;
use Liberu\Cms\Contracts\Module\ModuleRegistryInterface;

it('boots the application with a module disabled', function (): void {
    $app = makeBareCmsApp(disabledModules: ['hello']);

    expect($app->make(ModuleManagerInterface::class)->isEnabled('hello'))->toBeFalse();
});

it('does not register a disabled module\'s routes', function (): void {
    $app = makeBareCmsApp(disabledModules: ['hello']);

    expect($app['router']->getRoutes()->getByName('cms.hello.greet'))->toBeNull();
});

it('still registers a disabled module in the registry so the graph stays complete', function (): void {
    $app = makeBareCmsApp(disabledModules: ['hello']);

    expect($app->make(ModuleRegistryInterface::class)->has('hello'))->toBeTrue();
});

it('keeps the kernel and other modules working when one module is disabled', function (): void {
    $app = makeBareCmsApp(disabledModules: ['hello']);

    expect($app->make(EventBusInterface::class))->not->toBeNull()
        ->and($app->make(ModuleManagerInterface::class)->bootOrder())->not->toContain('hello');
});
