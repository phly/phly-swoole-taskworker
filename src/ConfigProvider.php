<?php
/**
 * @see       https://github.com/phly/phly-swoole-taskworker for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/phly/phly-swoole-taskworker/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\EventDispatcher\ListenerProviderInterface;
use Swoole\Http\Server as HttpServer;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'delegators' => [
                HttpServer::class => [
                    TaskWorkerDelegator::class,
                ],
                ListenerProviderInterface::class => [
                    QueueableListenerProviderDelegator::class,
                ],
            ],
            'factories' => [
                TaskWorker::class => TaskWorkerFactory::class,
            ],
        ];
    }
}
