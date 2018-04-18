# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.2 - 2018-01-25

### Added

- [#29](https://github.com/zendframework/zend-xmlrpc/pull/29) adds support for
  PHP 7.2, by replacing deprecated `list`/`each` syntax with a functional
  equivalent.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2017-08-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#27](https://github.com/zendframework/zend-xmlrpc/pull/19) fixed a memory leak
  caused by repetitive addition of `Accept` and `Content-Type` headers on subsequent
  HTTP requests produced by the `Zend\XmlRpc\Client`.

## 2.6.0 - 2016-06-21

### Added

- [#19](https://github.com/zendframework/zend-xmlrpc/pull/19) adds support for
  zend-math v3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2016-04-21

### Added

- [#11](https://github.com/zendframework/zend-xmlrpc/pull/11),
  [#12](https://github.com/zendframework/zend-xmlrpc/pull/12),
  [#13](https://github.com/zendframework/zend-xmlrpc/pull/13),
  [#14](https://github.com/zendframework/zend-xmlrpc/pull/14),
  [#15](https://github.com/zendframework/zend-xmlrpc/pull/15), and
  [#16](https://github.com/zendframework/zend-xmlrpc/pull/16)
  added and prepared the documentation for publication at
  https://zendframework.github.io/zend-xmlrpc/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#17](https://github.com/zendframework/zend-xmlrpc/pull/17) updates
  dependencies to allow zend-stdlib v3 releases.
