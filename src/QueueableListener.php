<?php

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Closure;
use Swoole\Http\Server as HttpServer;

/**
 * Decorator for an event listener that queues it as a Swoole task.
 *
 * When invoked, this listener will create a Task instance with the
 * provided event and the decorated listener, and pass it to the composed
 * HTTP server's task method.
 *
 * @deprecated since 1.1.0; use Phly\Swoole\TaskWorker\DeferredListener instead.
 */
final class QueueableListener
{
    private HttpServer $server;

    private Closure $listener;

    public function __construct(HttpServer $server, Closure $listener)
    {
        $this->server   = $server;
        $this->listener = $listener;
    }

    public function __invoke(object $event): void
    {
        $this->server->task(new Task($this->listener, $event));
    }
}
