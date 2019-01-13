<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\EventDispatcher\ListenerShouldQueue;
use Phly\Swoole\TaskWorker\QueueableListener;
use Phly\Swoole\TaskWorker\QueueableListenerProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Swoole\Http\Server as HttpServer;

class QueueableListenerProviderTest extends TestCase
{
    public function testDecoratesListenersThatShouldQueue()
    {
        $event    = (object) ['event' => true];
        $server   = $this->createMock(HttpServer::class);
        $listener = new class implements ListenerShouldQueue {
            public function __invoke()
            {
            }
        };
        $provider = $this->prophesize(ListenerProviderInterface::class);
        $provider
            ->getListenersForEvent($event)
            ->willReturn([$listener]);

        $queueable = new QueueableListenerProvider($server, $provider->reveal());

        $listeners = iterator_to_array($queueable->getListenersForEvent($event));

        $this->assertCount(1, $listeners);
        $test = array_pop($listeners);
        $this->assertInstanceOf(QueueableListener::class, $test);
        $this->assertAttributeSame($server, 'server', $test);
        $this->assertAttributeSame($listener, 'listener', $test);
    }

    public function testDoesNotDecorateNonQueueuableListeners()
    {
        $event    = (object) ['event' => true];
        $server   = $this->createMock(HttpServer::class);
        $listener = function ($event) {
        };
        $provider = $this->prophesize(ListenerProviderInterface::class);
        $provider
            ->getListenersForEvent($event)
            ->willReturn([$listener]);

        $queueable = new QueueableListenerProvider($server, $provider->reveal());

        $listeners = iterator_to_array($queueable->getListenersForEvent($event));

        $this->assertCount(1, $listeners);
        $test = array_pop($listeners);
        $this->assertSame($listener, $test);
    }
}
