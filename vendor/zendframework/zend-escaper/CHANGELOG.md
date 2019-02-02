# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.0 - 2018-04-25

### Added

- [#28](https://github.com/zendframework/zend-escaper/pull/28) adds support for PHP 7.1 and 7.2.

### Changed

- [#25](https://github.com/zendframework/zend-escaper/pull/25) changes the behavior of the `Escaper` constructor; it now raises an
  exception for non-null, non-string `$encoding` arguments.

### Deprecated

- Nothing.

### Removed

- [#28](https://github.com/zendframework/zend-escaper/pull/28) removes support for PHP 5.5.

- [#28](https://github.com/zendframework/zend-escaper/pull/28) removes support for HHVM.

### Fixed

- Nothing.

## 2.5.2 - 2016-06-30

### Added

- [#11](https://github.com/zendframework/zend-escaper/pull/11),
  [#12](https://github.com/zendframework/zend-escaper/pull/12), and
  [#13](https://github.com/zendframework/zend-escaper/pull/13) prepare and
  publish documentation to https://zendframework.github.io/zend-escaper/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/zendframework/zend-escaper/pull/3) updates the
  the escaping mechanism to add support for escaping characters outside the Basic
  Multilingual Plane when escaping for JS, CSS, or HTML attributes.
