<?php

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\DeferredServiceListener;
use Phly\Swoole\TaskWorker\ServiceBasedTask;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Swoole\Http\Server as HttpServer;

class ServiceBasedTaskTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->handler   = 'service-name';
        $this->task      = new ServiceBasedTask($this->handler, 'first', 'second');
    }

    public function testTaskIsInvokableAndCallsServiceDirectlyIfNotADeferredServiceListener()
    {
        $spy = (object) ['payload' => null];
        $this->container->get($this->handler)->willReturn(function (...$args) use ($spy) {
            $spy->payload = $args;
        });

        ($this->task)($this->container->reveal());

        $this->assertSame([
            'first',
            'second',
        ], $spy->payload);
    }

    public function testTaskIsInvokableAndCallsListenerComposedInDeferredServiceListener()
    {
        $spy      = (object) ['payload' => null];
        $server   = $this->createMock(HttpServer::class);
        $deferred = new DeferredServiceListener($server, function (...$args) use ($spy) {
            $spy->payload = $args;
        }, $this->handler);
        $this->container->get($this->handler)->willReturn($deferred);

        ($this->task)($this->container->reveal());

        $this->assertSame([
            'first',
            'second',
        ], $spy->payload);
    }

    public function testTaskIsSerializable()
    {
        $expected = [
            'handler'   => $this->handler,
            'arguments' => [
                'first',
                'second',
            ],
        ];

        $this->assertSame($expected, $this->task->jsonSerialize());
    }
}
