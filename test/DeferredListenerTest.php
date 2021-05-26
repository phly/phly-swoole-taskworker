<?php

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\DeferredListener;
use Phly\Swoole\TaskWorker\Task;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Swoole\Http\Server as HttpServer;

class DeferredListenerTest extends TestCase
{
    public function testListenerInvocationCreatesAServerTaskWithTheListenerAndEvent()
    {
        $listener = function () {
        };
        $event    = (object) ['event' => true];

        $server = $this->createMock(HttpServer::class);
        $server
            ->expects($this->once())
            ->method('task')
            ->with($this->callback(function ($task) use ($listener, $event) {
                if (! $task instanceof Task) {
                    return false;
                }

                $r = new ReflectionProperty($task, 'handler');
                $r->setAccessible(true);
                if ($listener !== $r->getValue($task)) {
                    return false;
                }

                $r = new ReflectionProperty($task, 'payload');
                $r->setAccessible(true);
                if ([$event] !== $r->getValue($task)) {
                    return false;
                }

                return true;
            }));

        $deferredListener = new DeferredListener($server, $listener);

        $this->assertNull($deferredListener($event));
    }
}
