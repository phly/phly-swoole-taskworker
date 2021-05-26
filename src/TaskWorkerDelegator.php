<?php

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Http\Server as HttpServer;

class TaskWorkerDelegator
{
    /** @param mixed $serviceName */
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback): HttpServer
    {
        $server = $callback();
        $logger = $container->get(LoggerInterface::class);

        $server->on('task', $container->get(TaskWorker::class));
        $server->on('finish', function (HttpServer $server, int $taskId, $data) use ($logger) {
            $logger->notice('Task #{taskId} has finished processing', ['taskId' => $taskId]);
        });

        return $server;
    }
}
