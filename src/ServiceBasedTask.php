<?php

declare(strict_types=1);

namespace Phly\Swoole\TaskWorker;

use Psr\Container\ContainerInterface;

/**
 * Representation of a task to execute via task worker.
 *
 * Contains the callable that will handle the task, and an array of arguments
 * with which to call it. Handlers are expected to return void; any return
 * values will be ignored.
 */
final class ServiceBasedTask implements TaskInterface
{
    private array $payload;

    private string $serviceName;

    /** @param mixed ...$payload */
    public function __construct(string $serviceName, ...$payload)
    {
        $this->serviceName = $serviceName;
        $this->payload     = $payload;
    }

    public function __invoke(ContainerInterface $container): void
    {
        $deferred = $container->get($this->serviceName);
        $listener = $deferred instanceof DeferredServiceListener
            ? $deferred->getListener()
            : $deferred;
        $listener(...$this->payload);
    }

    public function jsonSerialize(): array
    {
        return [
            'handler'   => $this->serviceName,
            'arguments' => $this->payload,
        ];
    }
}
