<?php

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Closure;
use Psr\Container\ContainerInterface;

use function array_shift;
use function get_class;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Representation of a task to execute via task worker.
 *
 * Contains the callable that will handle the task, and an array of arguments
 * with which to call it. Handlers are expected to return void; any return
 * values will be ignored.
 */
final class Task implements TaskInterface
{
    private Closure $handler;

    private array $payload;

    /** @param mixed ...$payload */
    public function __construct(callable $handler, ...$payload)
    {
        $this->handler = $handler;
        $this->payload = $payload;
    }

    public function __invoke(ContainerInterface $container): void
    {
        ($this->handler)(...$this->payload);
    }

    public function jsonSerialize(): array
    {
        return [
            'handler'   => $this->serializeHandler($this->handler),
            'arguments' => $this->payload,
        ];
    }

    /** @param mixed $handler */
    private function serializeHandler($handler): string
    {
        if (is_object($handler)) {
            return get_class($handler);
        }

        if (is_string($handler)) {
            return $handler;
        }

        if (! is_array($handler)) {
            return '<unknown>';
        }

        $classOrObject = array_shift($handler);
        $method        = array_shift($handler);
        return sprintf('%s::%s', $this->serializeHandler($classOrObject), $method);
    }
}
