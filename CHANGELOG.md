# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.0 - 2020-03-10

### Added

- This version provides features to simplify using arbitrary services from your PSR-11 container, particularly services that might compose other dependencies.  We recommend using these new features in almost every case, as they allow re-using services within your task workers regardless of the ability to serialize them. They include:

  - [#4](https://github.com/phly/phly-swoole-taskworker/pull/4) adds the `DeferredServiceListener`. It has a constructor that accepts a Swoole HTTP server, a listener for handling a task, and a service name by which that listener is pulled from a PSR-11 container. It also defines a `getListener()` method for retrieving the composed listener later. When invoked, it creates a `ServiceBasedTask` instance with the service name and payload arguments.

  - [#4](https://github.com/phly/phly-swoole-taskworker/pull/4) adds the `ServiceBasedTask` class, which implements `TaskInterface`. When invoked, it pulls the composed service from the container instance passed to its invocation method; if that instance is a `DeferredServiceListener`, it re-assigns the instance to the results of calling the `getListener()` method on that class. The service is then called to process the payload arguments.

  - [#4](https://github.com/phly/phly-swoole-taskworker/pull/4) adds the `DeferredServiceListenerDelegator`. It creates a `DeferredServiceListener` instance composing the Swoole HTTP server instance, the listener produced by the `$factory` argument, and the `$serviceName` passed to it. The class should be used as a delegator factory for any listener that is not directly serializable, including most services that compose other dependencies.

### Changed

- [#4](https://github.com/phly/phly-swoole-taskworker/pull/4) changes the constructor of `TaskWorker` to now accept an initial `Psr\Container\ContainerInterface` argument. This change should be transparent to most users if they are using the `ConfigProvider` shipped with the package.

- [#4](https://github.com/phly/phly-swoole-taskworker/pull/4) changes the signature of `TaskInterface::invoke()` to now require a `Psr\Container\ContainerInterface` argument.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.0 - 2019-01-24

### Added

- [#1](https://github.com/phly/phly-swoole-taskworker/pull/1) adds the class `DeferredListener`; it operates identically to `QueuedListener`,
  and replaces its functionality, albeit with a more accurate name.

- [#2](https://github.com/phly/phly-swoole-taskworker/pull/2) adds the delegator factory `Phly\Swoole\TaskWorker\DeferredListenerDelegator`.
  This factory can be attached to any listener service in order to allow it to
  be deferred when invoked. I suggest doing so in `config/autoload/local.php` to
  allow testing the listener in development, but deferring it in production.

### Changed

- Nothing.

### Deprecated

- [#1](https://github.com/phly/phly-swoole-taskworker/pull/1) deprecates the `QueueableListener` class in favor of the new
  `DeferredListener` class.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2019-01-14

### Added

- All functionality.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
