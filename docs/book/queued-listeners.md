# Queued Listeners

> ## Deprecated
>
> The `QueueableListener` class is deprecated as of version 1.1.0; I felt the
> naming was incorrect.  Please refer to the [Deferred
> Listeners](deferred-listeners.md) documentation instead.

When using a [PSR-14](https://github.com/php-fig/fig-standards/blob/bb8df27dba53fa5cbc653d1d446f850e5690f3cc/proposed/event-dispatcher.md)
event dispatcher, you may want to queue the work of a listener instead of
handling it immediately. As an example, if you have a listener that might be
updating statistics, but this is not time-critical, you could benefit from
deferring the calculations.

This package defines a "queueble listener" for this purpose. It will decorate an
existing listener, and, when invoked, create a Swoole task.

## QueueableListener

`Phly\Swoole\TaskWorker\QueueuableListener` accepts the Swoole HTTP server as
its first constructor argument, and a callable listener as the second. When
invoked, it will create a `Task` instance using the listener as the handler, and
the event as the sole argument to it, and create a task on the server using it.

As an example:

```php
use Phly\Swoole\TaskWorker\QueueableListener;

// @var Swoole\Http\Server $server
$listener = new QueueableListener(
    $server,
    new SomeListenerImplementingShouldQueue()
);

// Assuming a provider with an "attach()" method:
$provider->attach(SomeEvent::class, $listener);
```
