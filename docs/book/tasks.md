# Tasks and the Task Worker

The `TaskWorker` class expects a [PSR-3 Logger](https://www.php-fig.org/psr/psr-3/)
as its sole dependency. This is used to:

- log tasks that it cannot handle (any task payload that does not implement
  `TaskInterface`).
- log tasks that are being handled.
- log exceptions raised by tasks.

## Tasks

Tasks are any object implementing `Phly\Swoole\TaskWorker\TaskInterface`:

```php
namespace Phly\Swoole\TaskWorker;

use JsonSerializable;

interface TaskInterface extends JsonSerializable
{
    /**
     * Tasks are invokable; implement this method to do the work of the task.
     */
    public function __invoke() : void;
}
```

Note that tasks must implement `JsonSerializable` as well. This is done so that
the task worker can provide details on the task being dispatched. The
information you return from that method can be anything you need in order to
help you identify task types or payloads in your logs.

### The Task class

A basic implementation is provided by `Phly\Swoole\TaskWorker\Task`. This class
has the following constructor:

```php
namespace Phly\Swoole\TaskWorker;

final class Task implements TaskInterface
{
    public function __construct(callable $handler, ...$payload);
}
```

Essentially, it expects any PHP callable, and the _arguments to pass to it_.
These should be JSON serializable, as they are part of the serialization:

```php
public function jsonSerialize()
{
    return [
        'handler'   => /* serialization of handler */,
        'arguments' => $this->payload,
    ];
}
```

The handler will be serialized as follows:

- An invokable class will be represented by the class name.
- A string function name or static method name will be represented by that string.
- An anonymous function will be represnted by the class name `Closure`.
- Array callables will determine if the first element is a class name or object,
  and return a string that looks like a static method name:
  `ClassName::methodName`.

The `Task` class is a good, general-purpose choice for task implementations.

## The TaskWorker

The task worker listens to the `task` event of a Swoole HTTP server. when it
receives the task, it does the following:

- It checks to see if the task implements `TaskInterface`. If not, it logs an
  error, and finishes.
- It logs a notice containing both the task ID, and the serialized task.
- It invokes the task.
- If an exception is caught while invoking the task, it will log an error with
  the task ID and exception details.

To create the task worker manually and register it with the server:

```php
use Phly\Swoole\TaskWorker\TaskWorker

// @var Psr\Log\LoggerInterface $logger
$worker = new TaskWorker($logger);

// @var Swoole\Http\Server $server
$server->on('task', $worker);
$server->on('finish', function ($server, int $taskId, $data) use ($logger) {
    $logger->notice(
        'Task #{taskId} has finished processing',
        ['taskId' => $taskId]
    );
});
```

> The above is how the auto-registered `TaskWorkerDelegator` registers the task
> worker with the Swoole HTTP server
