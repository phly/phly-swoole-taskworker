<?php

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\TaskWorker;
use Phly\Swoole\TaskWorker\TaskWorkerFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class TaskWorkerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryProducesTaskWorkerWithLoggerComposed()
    {
        $logger    = $this->prophesize(LoggerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(LoggerInterface::class)->willReturn($logger);

        $factory = new TaskWorkerFactory();

        $taskWorker = $factory($container->reveal());

        $this->assertInstanceOf(TaskWorker::class, $taskWorker);
    }
}
