<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Container\ContainerInterface;
use Swoole\Http\Server as HttpServer;

/**
 * Delegator factory for decorating a ListenerProviderInterface as a QueueableListenerProvider
 */
class QueueableListenerProviderDelegator
{
    public function __invoke(
        ContainerInterface $container,
        string $serviceName,
        callable $factory
    ) : QueueableListenerProvider {
        return new QueueableListenerProvider(
            $container->get(HttpServer::class),
            $factory()
        );
    }
}
