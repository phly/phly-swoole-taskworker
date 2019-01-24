# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - TBD

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
