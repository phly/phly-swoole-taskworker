# Deferred Listeners

> Formerly "Queued Listeners".

- Since 1.1.0
- DeferredServiceListener and DeferredServiceListenerDelegator since 2.0.0

When using a [PSR-14](https://github.com/php-fig/fig-standards/blob/bb8df27dba53fa5cbc653d1d446f850e5690f3cc/proposed/event-dispatcher.md)
event dispatcher, you may want to defer the work of a listener instead of
handling it immediately. As an example, if you have a listener that might be
updating statistics, but this is not time-critical, you could benefit from
deferring the calculations.

This package defines "deferred listeners" for this purpose. It will decorate an
existing listener, and, when invoked, create a Swoole task.

## DeferredListener

`Phly\Swoole\TaskWorker\DeferredListener` accepts the Swoole HTTP server as
its first constructor argument, and a callable listener as the second. When
invoked, it will create a `Task` instance using the listener as the handler, and
the event as the sole argument to it, and create a task on the server using it.

As an example:

```php
use Phly\Swoole\TaskWorker\DeferredListener;

// @var Swoole\Http\Server $server
$listener = new DeferredListener(
    $server,
    'functionToHandleTask'
);

// Assuming a provider with an "attach()" method:
$provider->attach(SomeEvent::class, $listener);
```

> ### Serializable Listeners only
>
> Listeners decorated in a `DeferredListener` must be serializable. This
> essentially excludes any class-based listeners that have dependencies. If you
> plan to use such a listener, skip ahead below to the [section on deferred
> service listeners](#deferredservicelistener).

## DeferredListenerDelegator

To help automate deferment, you can use [delegator factories](https://docs.zendframework.com/zend-expressive/v3/features/container/delegator-factories/).
This package provides a delegator via the class
`Phly\Swoole\TaskWorker\DeferredListenerDelegator`.

In typical usage, you will assign this to individual listener services within
your `config/autoload/local.php` file in production, so that deferment only
happens in production.

As an example:

```php
// in config/autoload/local.php
use Phly\Swoole\TaskWorker\DeferredListenerDelegator;

return [
    'dependencies' => [
        'factories' => [
            App\SomeEventListener::class => App\SomeEventListenerFactory::class
        ],
        'delegators' => [
            App\SomeEventListener::class => [
                DeferredListenerDelegator::class,
            ],
        ],
    ],
];
```

> ### Serializable Listeners only
>
> Listeners decorated via the delegator MUST be serializable, which
> essentially excludes any class-based listeners that have dependencies. If you
> plan to use such a listener, continue reading.

## DeferredServiceListener

- Since 2.0.0

Swoole only allows passing tasks that are serializable. Since most listeners
used to create Swoole tasks will likely have one or more dependencies (e.g., an
HTTP client, a database adapter, etc.), this can pose a problem if you want to
defer a listener to execute via a task worker.

To resolve that issue, we offer `Phly\Swoole\TaskWorker\DeferredServiceListener`. 
This class accepts the following constructor arguments:

- The Swoole HTTP server instance.
- The callable listener that you want to use for processing.
- The service name used to fetch the listener from the PSR-11 DI container.

As an example:

```php
use Phly\Swoole\TaskWorker\DeferredListener;

// @var Swoole\Http\Server $server
$listener = new DeferredListener(
    $server,
    $actualListener,
    $actualListener::class // assuming it is registered using the class name
);

// Assuming a provider with an "attach()" method:
$provider->attach(SomeEvent::class, $listener);
```

## DeferredServiceListenerDelegator

- Since 2.0.0

Just as with the [DeferredListenerDelegator](#deferredlistenerdelegator), we
provide `Phly\Swoole\TaskWorker\DeferredServiceListenerDelegator` to automate
decorating a listener registered in the container as a `DeferredServiceListener`.

In typical usage, you will assign this to individual listener services within
your `config/autoload/local.php` file in production, so that deferment only
happens in production.

As an example:

```php
// in config/autoload/local.php
use Phly\Swoole\TaskWorker\DeferredServiceListenerDelegator;

return [
    'dependencies' => [
        'factories' => [
            App\SomeEventListener::class => App\SomeEventListenerFactory::class
        ],
        'delegators' => [
            App\SomeEventListener::class => [
                DeferredServiceListenerDelegator::class,
            ],
        ],
    ],
];
```

This is the RECOMMENDED way to defer listeners for use with the task worker.
