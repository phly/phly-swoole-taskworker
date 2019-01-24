<?php
/**
 * @see       https://github.com/phly/phly-swoole-taskworker for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/phly/phly-swoole-taskworker/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Container\ContainerInterface;
use Swoole\Http\Server as HttpServer;

class DeferredListenerDelegator
{
    /**
     * Decorate a listener as a DeferredListener
     *
     * If the $factory does not produce a PHP callable, this method
     * returns it verbatim. Otherwise, it decorates it as a DeferredListener.
     *
     * @return DeferredListener|mixed
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

        return new DeferredListener(
            $container->get(HttpServer::class),
            $listener
        );
    }
}
