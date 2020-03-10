<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Swoole\Http\Server as HttpServer;

/**
 * Decorator for an event listener that defers it to run as a Swoole task.
 *
 * When invoked, this listener will create a Task instance with the
 * provided event and the decorated listener, and pass it to the composed
 * HTTP server's task method.
 */
final class DeferredServiceListener
{
    /**
     * @var HttpServer
     */
    private $server;

    /**
     * @var callable
     */
    private $listener;

    /**
     * @var string
     */
    private $serviceName;

    public function __construct(HttpServer $server, callable $listener, string $serviceName)
    {
        $this->server      = $server;
        $this->listener    = $listener;
        $this->serviceName = $serviceName;
    }

    public function __invoke(object $event) : void
    {
        $this->server->task(new ServiceBasedTask($this->serviceName, $event));
    }

    public function getListener(): callable
    {
        return $this->listener;
    }
}
