<?php

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use JsonSerializable;
use Psr\Container\ContainerInterface;

interface TaskInterface extends JsonSerializable
{
    /**
     * Tasks are invokable; implement this method to do the work of the task.
     */
    public function __invoke(ContainerInterface $container): void;
}
