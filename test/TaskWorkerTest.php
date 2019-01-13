<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace PhlyTest\Swoole\TaskWorker;

use Phly\Swoole\TaskWorker\TaskInterface;
use Phly\Swoole\TaskWorker\TaskWorker;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Http\Server as HttpServer;
use Throwable;

class TaskWorkerTest extends TestCase
{
    public function setUp()
    {
        $this->server = $this->createMock(HttpServer::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->taskWorker = new TaskWorker($this->logger->reveal());
    }

    public function testLogsErrorWhenTaskIsNotATask()
    {
        $this->server
            ->expects($this->once())
            ->method('finish');

        $spy = (object) ['triggered' => false];
        $task = function () use ($spy) {
            $spy->triggered = true;
        };

        ($this->taskWorker)($this->server, 1, 2, $task);

        $this->logger
            ->error(
                Argument::containingString('Invalid task provided to task worker'),
                Argument::that(function ($context) {
                    Assert::assertInternalType('array', $context);
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

        $spy = (object) ['triggered' => false];
        $task = $this->prophesize(TaskInterface::class);
        $task->
            __invoke()
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
                    Assert::assertInternalType('array', $context);
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
            __invoke()
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
                    Assert::assertInternalType('array', $context);
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
                    Assert::assertInternalType('array', $context);
                    Assert::assertArrayHasKey('taskId', $context);
                    Assert::assertArrayHasKey('error', $context);
                    Assert::assertContains($throwable->getMessage(), $context['error']);
                    return $context;
                })
            )
            ->shouldHaveBeenCalled();
    }
}
