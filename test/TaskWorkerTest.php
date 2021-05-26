<?php

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\TaskInterface;
use Phly\Swoole\TaskWorker\TaskWorker;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Http\Server as HttpServer;

class TaskWorkerTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
        $this->server     = $this->createMock(HttpServer::class);
        $this->logger     = $this->prophesize(LoggerInterface::class);
        $this->taskWorker = new TaskWorker($this->container->reveal(), $this->logger->reveal());
    }

    public function testLogsErrorWhenTaskIsNotATask()
    {
        $this->server
            ->expects($this->once())
            ->method('finish');

        $spy  = (object) ['triggered' => false];
        $task = function () use ($spy) {
            $spy->triggered = true;
        };

        ($this->taskWorker)($this->server, 1, 2, $task);

        $this->logger
            ->error(
                Argument::containingString('Invalid task provided to task worker'),
                Argument::that(function ($context) {
                    Assert::assertIsArray($context);
                    Assert::assertArrayHasKey('type', $context);
                    return $context;
                })
            )
            ->shouldHaveBeenCalled();
        $this->logger
            ->notice(Argument::any(), Argument::any())
            ->shouldNotHaveBeenCalled();
    }

    public function testInvokesTaskWhenTaskIsValid()
    {
        $this->server
            ->expects($this->once())
            ->method('finish');

        $spy  = (object) ['triggered' => false];
        $task = $this->prophesize(TaskInterface::class);
        $task->
            __invoke($this->container->reveal())
            ->will(function () use ($spy) {
                $spy->triggered = true;
            });
        $task->
            jsonSerialize()
            ->willReturn(['task' => 'task'])
            ->shouldBeCalled();

        ($this->taskWorker)($this->server, 1, 2, $task->reveal());

        $this->logger
            ->error(Argument::any(), Argument::any())
            ->shouldNotHaveBeenCalled();
        $this->logger
            ->notice(
                Argument::containingString('Starting work on task'),
                Argument::that(function ($context) {
                    Assert::assertIsArray($context);
                    Assert::assertArrayHasKey('taskId', $context);
                    Assert::assertArrayHasKey('task', $context);
                    return $context;
                })
            )
            ->shouldHaveBeenCalled();
        $this->assertTrue($spy->triggered);
    }

    public function testLogsThrowableWhenInvokedTaskRaisesOne()
    {
        $this->server
            ->expects($this->once())
            ->method('finish');

        $throwable = new RuntimeException('for task');

        $task = $this->prophesize(TaskInterface::class);
        $task->
            __invoke($this->container->reveal())
            ->will(function () use ($throwable) {
                throw $throwable;
            });
        $task->
            jsonSerialize()
            ->willReturn(['task' => 'task'])
            ->shouldBeCalled();

        ($this->taskWorker)($this->server, 1, 2, $task->reveal());

        $this->logger
            ->error(
                Argument::containingString('Invalid task provided'),
                Argument::any()
            )
            ->shouldNotHaveBeenCalled();
        $this->logger
            ->notice(
                Argument::containingString('Starting work on task'),
                Argument::that(function ($context) {
                    Assert::assertIsArray($context);
                    Assert::assertArrayHasKey('taskId', $context);
                    Assert::assertArrayHasKey('task', $context);
                    return $context;
                })
            )
            ->shouldHaveBeenCalled();
        $this->logger
            ->error(
                Argument::containingString('Error processing task'),
                Argument::that(function ($context) use ($throwable) {
                    Assert::assertIsArray($context);
                    Assert::assertArrayHasKey('taskId', $context);
                    Assert::assertArrayHasKey('error', $context);
                    Assert::assertStringContainsString($throwable->getMessage(), $context['error']);
                    return $context;
                })
            )
            ->shouldHaveBeenCalled();
    }
}
