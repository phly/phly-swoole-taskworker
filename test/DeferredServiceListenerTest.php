<?php

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\DeferredServiceListener;
use Phly\Swoole\TaskWorker\ServiceBasedTask;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Swoole\Http\Server as HttpServer;

class DeferredServiceListenerTest extends TestCase
{
    public function testListenerInvocationCreatesAServerBasedTaskWithTheListenerNameAndEvent()
    {
        $listener = function () {
        };
        $event    = (object) ['event' => true];

        $server = $this->createMock(HttpServer::class);
        $server
            ->expects($this->once())
            ->method('task')
            ->with($this->callback(function ($task) use ($event) {
                if (! $task instanceof ServiceBasedTask) {
                    return false;
                }

                $r = new ReflectionProperty($task, 'serviceName');
                $r->setAccessible(true);
                if ($r->getValue($task) !== 'service-name') {
                    return false;
                }

                $r = new ReflectionProperty($task, 'payload');
                $r->setAccessible(true);
                if ([$event] !== $r->getValue($task)) {
                    return false;
                }

                return true;
            }));

        $deferredListener = new DeferredServiceListener($server, $listener, 'service-name');

        $this->assertSame($listener, $deferredListener->getListener());
        $this->assertNull($deferredListener($event));
    }
}
