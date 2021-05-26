<?php

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class TaskWorkerFactory
{
    public function __invoke(ContainerInterface $container): TaskWorker
    {
        return new TaskWorker(
            $container,
            $container->get(LoggerInterface::class)
        );
    }
}
