<?php
/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

/**
 * Representation of a task to execute via task worker.
 *
 * Contains the callable that will handle the task, and an array of arguments
 * with which to call it. Handlers are expected to return void; any return
 * values will be ignored.
 */
final class Task
{
    /**
     * @var callable
     */
    private $handler;

    /**
     * @var array
     */
    private $payload;

    public function __construct(callable $handler, ...$payload)
    {
        $this->handler = $handler;
        $this->payload = $payload;
    }

    public function handler() : callable
    {
        return $this->handler;
    }

    public function payload() : array
    {
        return $this->payload;
    }
}
