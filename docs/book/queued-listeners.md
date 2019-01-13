# Queued Listeners

[phly/phly-event-dispatcher](https://github.com/phly/phly-event-dispatcher)
defines the interface `Phly\EventDispatcher\ListenerShouldQueue`, which can be
used to flag listeners that can or should be queued for later processing.

This package defines a couple of facilities around this interface to allow event
listeners to be processed via the task worker.

## QueueableListener

The first facility is `Phly\Swoole\TaskWorker\QueueuableListener`. This class
accepts the Swoole HTTP server as its first constructor argument, and a callable
listener as the second. When invoked, it will create a `Task` instance using the
listener as the handler, and the event as the sole argument to it, and create a
task on the server using it.

As an example:

```php
use Phly\Swoole\TaskWorker\QueueableListener;

// @var Swoole\Http\Server $server
$listener = new QueueableListener(
    $server,
    new SomeListenerImplementingShouldQueue()
);
```

## QueueableListenerProvider

The second facility is `Phly\Swoole\TaskWorker\QueueableListenerProvider`. This
class expects both a Swoole HTTP server instance and a
`Psr\EventDispatcher\ListenerProviderInterface` instance as constructor
arguments.

When its `getListenersForEvent()` method is called, it loops over the listeners
returned by the decorated provider, and decorates any listeners that implement
`ListenerShouldQueue` using `QueueuableListener`.

```php
use Phly\Swoole\TaskWorker\QueueableListenerProvider;

// @var Swoole\Http\Server $server
// @var Psr\EventDispatcher\ListenerProviderInterface $provider
$queueableProvider = new QueueableListenerProvider(
    $server,
    $provider
);
```

## QueueableListenerProviderDelegator

To automate registration of queuable listeners, you can optionally add the
`Phly\Swoole\TaskWorker\QueueableListenerProviderDelegator` as a delegator
factory for your application's `ListenerProviderInterface` service:

```php
use Phly\Swoole\TaskWorker\QueueableListenerProviderDelegator;
use Psr\EventDispatcher\ListenerProviderInterface;

return [
    'dependencies' => [
        'delegators' => [
            ListenerProviderInterface::class => [
                QueueableListenerProviderDelegator::class,
            ],
        ],
    ],
];
```

Once registered, this will decorate your existing listener provider as detailed
in the section above.

> While this is useful for prototyping, we do not recommend it for production,
> as the listeners must be inspected and decorated on each invocation. Instead,
> decorate the listeners at the time you inject them in your provider.
