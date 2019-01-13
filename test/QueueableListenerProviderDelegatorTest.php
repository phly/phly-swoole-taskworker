<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\QueueableListenerProvider;
use Phly\Swoole\TaskWorker\QueueableListenerProviderDelegator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Swoole\Http\Server as HttpServer;

class QueueableListenerProviderDelegatorTest extends TestCase
{
    public function testDelegatorProducesAProviderDecoratedAsAQueueableProvider()
    {
        $server = $this->createMock(HttpServer::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(HttpServer::class)->willReturn($server);

        $decoratedProvider = $this->prophesize(ListenerProviderInterface::class)->reveal();
        $factory = function () use ($decoratedProvider) {
            return $decoratedProvider;
        };

        $delegator = new QueueableListenerProviderDelegator();

        $provider = $delegator(
            $container->reveal(),
            ListenerProviderInterface::class,
            $factory
        );

        $this->assertInstanceOf(QueueableListenerProvider::class, $provider);
        $this->assertAttributeSame($server, 'server', $provider);
        $this->assertAttributeSame($decoratedProvider, 'provider', $provider);
    }
}
