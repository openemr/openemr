# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.1 - 2016-03-02

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#20](https://github.com/zendframework/zend-test/pull/20) updates the zend-mvc
  requirement to 2.7.1, ensuring deprecation notices will not occur in the
  majority of circumstances.

## 2.6.0 - 2016-03-01

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#19](https://github.com/zendframework/zend-test/pull/19) updates the
  code to be forwards compatible with:
  - zend-eventmanager v3
  - zend-servicemanager v3
  - zend-stdlib v3
  - zend-mvc v2.7

## 2.5.3 - 2016-03-01

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/zendframework/zend-test/pull/6) updates the
  `AbstractControllerTestCase` to mark a test as failed if no route match occurs
  in a number of assertions that require a route match.
- [#7](https://github.com/zendframework/zend-test/pull/7) modifies the `reset()`
  method of the `AbstractControllerTestCase` to prevent rewriting the
  `$_SESSION` superglobal if it has not previously been enabled.

## 2.5.2 - 2015-12-09

### Added

- [#4](https://github.com/zendframework/zend-test/pull/4) PHPUnit v5 Support.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
