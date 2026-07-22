<?php

declare(strict_types=1);

namespace Liberu\Cms\Core\Events;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Liberu\Cms\Contracts\Events\CmsEvent;
use Liberu\Cms\Contracts\Events\EventBusInterface;

/**
 * Thin, type-safe wrapper over the framework dispatcher.
 *
 * Only CmsEvent instances cross this seam, which keeps the module boundary
 * honest and gives the platform a single place to later add cross-application
 * transport (queue, webhook, message bus) without changing any module.
 */
final readonly class EventBus implements EventBusInterface
{
    public function __construct(private Dispatcher $dispatcher) {}

    public function dispatch(CmsEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function listen(string $eventClass, Closure|string|array $listener): void
    {
        $this->dispatcher->listen($eventClass, $listener);
    }
}
