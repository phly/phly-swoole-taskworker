# phly-swoole-taskworker

This component provides a task worker implementation for
[Swoole](https://www.swoole.co.uk) server instances.

The task runner expects `Phly\Swoole\TaskWorker\TaskInterface` instances as the
payload to process. `TaskInterface` instances must:

- Define `__invoke(\Psr\Container\ContainerInterface) :void`.
- Implement `JsonSerializable`'s `jsonSerialize()` method (`TaskInterface`
  extends this interface). This latter is used by the task worker to log the
  task being handled.

## Installation

Install the component via [Composer](https://getcomposer.org):

```bash
$ composer require phly/phly-swoole-taskworker
```

The component opts-in to the
[zend-component-installer](https://docs.zendframework.com/zend-component-installer)
workflow, and, if that plugin is available, Composer will prompt if you want to
install its `Phly\Swoole\TaskWorker\ConfigProvider`. This class will register
a factory for the task worker, and auto-register it as the task worker handler
for your Swoole HTTP Server instance.
