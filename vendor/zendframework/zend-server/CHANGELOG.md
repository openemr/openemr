# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.0 - 2018-04-30

### Added

- [#26](https://github.com/zendframework/zend-server/pull/26) adds support for PHP 7.1 and 7.2.

- [#19](https://github.com/zendframework/zend-server/pull/19) adds the ability to register any PHP callable with `Zend\Server\Method\Callback`.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#26](https://github.com/zendframework/zend-server/pull/26) removes support for HHVM.

### Fixed

- [#20](https://github.com/zendframework/zend-server/pull/20) fixes how `Cache::save()` works when `Server::getFunctions()` returns an
  associative array instead of a `Definition`, ensuring it will also skip
  any blacklisted methods when used in this way.

## 2.7.0 - 2016-06-20

### Added

- [#13](https://github.com/zendframework/zend-server/pull/13) adds and publishes
  the documentation to https://zendframework.github.io/zend-server
- [#14](https://github.com/zendframework/zend-server/pull/14) adds support for
  zend-code v3 (while retaining support for zend-code v2).

### Deprecated

- [#14](https://github.com/zendframework/zend-server/pull/14) deprecates all
  underscore-prefixed methods of `AbstractServer`; they will be renamed in
  version 3 to remove the prefix (though, in the case of `_dispatch()`, it will
  be renamed entirely, likely to `performDispatch()`).

### Removed

- [#14](https://github.com/zendframework/zend-server/pull/14) removes support
  for PHP 5.5; the new minimum supported version of PHP is 5.6.

### Fixed

- Nothing.

## 2.6.1 - 2016-02-04

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#11](https://github.com/zendframework/zend-server/pull/11) updates the
  dependencies to use zend-stdlib `^2.5 || ^3.0`.

## 2.6.0 - 2015-12-17

### Added

- [#3](https://github.com/zendframework/zend-server/pull/3) adds support for
  resolving `{@inheritdoc}` annotations to the original parent during
  reflection.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#2](https://github.com/zendframework/zend-server/pull/2) fixes misleading
  exception in reflectFunction that referenced reflectClass.
