<?php

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Closure;
use Phly\Swoole\TaskWorker\TaskWorker;
use Phly\Swoole\TaskWorker\TaskWorkerDelegator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Http\Server as HttpServer;

class TaskWorkerDelegatorTest extends TestCase
{
    use ProphecyTrait;

    public function testDelegatorAttachesTaskListenersToServer()
    {
        $server = $this->createMock(HttpServer::class);
        $server
            ->expects($this->exactly(2))
            ->method('on')
            ->withConsecutive(
                [
                    $this->equalTo('task'),
                    $this->callback(function ($listener) {
                        return $listener instanceof TaskWorker;
                    }),
                ],
                [
                    $this->equalTo('finish'),
                    $this->callback(function ($listener) {
                        return $listener instanceof Closure;
                    }),
                ]
            );

        $logger = $this->prophesize(LoggerInterface::class);
        $worker = $this->prophesize(TaskWorker::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(LoggerInterface::class)->will([$logger, 'reveal']);
        $container->get(TaskWorker::class)->will([$worker, 'reveal']);

        $factory = function () use ($server) {
            return $server;
        };

        $delegator = new TaskWorkerDelegator();

        $this->assertSame($server, $delegator(
            $container->reveal(),
            HttpServer::class,
            $factory
        ));
    }
}
