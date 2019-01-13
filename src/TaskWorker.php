<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Log\LoggerInterface;
use Swoole\Http\Server as HttpServer;
use Throwable;

class TaskWorker
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(HttpServer $server, int $taskId, int $fromId, $task) : void
    {
        if (! $task instanceof Task) {
            $this->logger->error('Invalid task provided to task worker: {type}', [
                'type' => is_object($task) ? get_class($task) : gettype($task)
            ]);
            return;
        }

        $handler = $task->handler();
        $payload = $task->payload();

        $this->logger->notice(
            'Starting work on task {taskId} using handler of type {handlerType} and payload: {payload}',
            [
                'taskId'      => $taskId,
                'handlerType' => is_object($handler) ? get_class($handler) : gettype($handler),
                'payload'     => json_encode($payload),
            ]
        );

        try {
            $handler(...$payload);
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

    private function formatExceptionForLogging(Throwable $e) : string
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