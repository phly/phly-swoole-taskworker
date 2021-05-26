<?php

/**
 * @see       https://github.com/phly/phly-swoole-taskworker for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Container\ContainerInterface;
use Swoole\Http\Server as HttpServer;

use function is_callable;

class DeferredServiceListenerDelegator
{
    /**
     * Decorate a listener as a DeferredListener
     *
     * If the $factory does not produce a PHP callable, this method
     * returns it verbatim. Otherwise, it decorates it as a DeferredListener.
     *
     * @return DeferredServiceListener|mixed
     */
    public function __invoke(
        ContainerInterface $container,
        string $serviceName,
        callable $factory
    ) {
        $listener = $factory();
        if (! is_callable($listener)) {
            return $listener;
        }

        return new DeferredServiceListener(
            $container->get(HttpServer::class),
            $listener,
            $serviceName
        );
    }
}
