<?php

/**
 * @see       https://github.com/phly/phly-swoole-taskworker for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Swoole\Http\Server as HttpServer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'delegators' => [
                HttpServer::class => [
                    TaskWorkerDelegator::class,
                ],
            ],
            'factories'  => [
                TaskWorker::class => TaskWorkerFactory::class,
            ],
        ];
    }
}
