<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use JsonSerializable;
use Psr\Container\ContainerInterface;

interface TaskInterface extends JsonSerializable
{
    /**
     * Tasks are invokable; implement this method to do the work of the task.
     */
    public function __invoke(ContainerInterface $container) : void;
}
