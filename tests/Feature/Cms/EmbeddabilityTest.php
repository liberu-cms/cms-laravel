<?php

declare(strict_types=1);

use Liberu\Cms\Contracts\Events\EventBusInterface;
use Liberu\Cms\Contracts\Module\ModuleManagerInterface;
use Liberu\Cms\Contracts\Module\ModuleRegistryInterface;
use Liberu\Cms\Hello\Contracts\GreeterInterface;
use Liberu\Cms\Hello\Events\HelloGreeted;

it('boots the CMS kernel inside a bare application', function (): void {
    $app = makeBareCmsApp();

    expect($app->make(ModuleRegistryInterface::class)->has('hello'))->toBeTrue()
        ->and($app->make(ModuleManagerInterface::class)->isEnabled('hello'))->toBeTrue();
});

it('serves a module capability inside a bare application', function (): void {
    $app = makeBareCmsApp();

    $message = $app->make(GreeterInterface::class)->greet('ada');

    expect($message)->toBe('Hello, ada!');
});

it('routes a module request inside a bare application', function (): void {
    $app = makeBareCmsApp();

    expect($app['router']->getRoutes()->getByName('cms.hello.greet'))->not->toBeNull();
});

it('delivers cross-module events over the bus inside a bare application', function (): void {
    $app = makeBareCmsApp();
    $received = null;

    $bus = $app->make(EventBusInterface::class);
    $bus->listen(HelloGreeted::class, function (HelloGreeted $event) use (&$received): void {
        $received = $event;
    });
    $bus->dispatch(new HelloGreeted('ada', 'Hello, ada!'));

    expect($received)->toBeInstanceOf(HelloGreeted::class)
        ->and($received->name)->toBe('ada');
});
