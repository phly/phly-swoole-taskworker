<?php

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Http\Server as HttpServer;
use Throwable;

use function get_class;
use function gettype;
use function is_object;
use function json_encode;
use function sprintf;

class TaskWorker
{
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger    = $logger;
    }

    /** @param mixed $task */
    public function __invoke(HttpServer $server, int $taskId, int $fromId, $task): void
    {
        if (! $task instanceof TaskInterface) {
            $this->logger->error('Invalid task provided to task worker: {type}', [
                'type' => is_object($task) ? get_class($task) : gettype($task),
            ]);
            $server->finish('');
            return;
        }

        $this->logger->notice(
            'Starting work on task {taskId} using: {task}',
            [
                'taskId' => $taskId,
                'task'   => json_encode($task),
            ]
        );

        try {
            $task($this->container);
        } catch (Throwable $e) {
            $this->logNotifierException($e, $taskId);
        } finally {
            // Notify the server that processing of the task has finished:
            $server->finish('');
        }
    }

    private function logNotifierException(Throwable $e, int $taskId)
    {
        $this->logger->error('Error processing task {taskId}: {error}', [
            'taskId' => $taskId,
            'error'  => $this->formatExceptionForLogging($e),
        ]);
    }

    private function formatExceptionForLogging(Throwable $e): string
    {
        return sprintf(
            "[%s - %d] %s\n%s",
            get_class($e),
            $e->getCode(),
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
}
