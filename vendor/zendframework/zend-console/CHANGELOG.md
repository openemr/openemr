# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2018-01-25

### Added

- [#32](https://github.com/zendframework/zend-console/pull/32) adds a new route
  match type, the "catch-all". Such types are always optional (thus, appear in
  `[]` sets), and are specified using `...` within: `command [...options]`.

  Parameters matched this way will always be returned as an array of values.

- [#39](https://github.com/zendframework/zend-console/pull/39) adds support for
  PHP 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#39](https://github.com/zendframework/zend-console/pull/39) removes support
  for PHP 5.5.

- [#39](https://github.com/zendframework/zend-console/pull/39) removes support
  for HHVM.

### Fixed

- [#19](https://github.com/zendframework/zend-console/pull/19) updated link
  to the documentation in the [README](README.md)

## 2.6.0 - 2016-02-9

### Added

- [#16](https://github.com/zendframework/zend-console/pull/16) updates,
  reorganizes, and publishes the documentation to
  https://zendframework.github.io/zend-console

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#13](https://github.com/zendframework/zend-console/pull/13) updates the
  component to make it forwards-compatible with the zend-stdlib and
  zend-servicemanager v3 versions.
- [#4](https://github.com/zendframework/zend-console/pull/4) fixes an error in
  `getTitle()` whereby the `$output` array was being incorrectly used as a
  string.
- [#12](https://github.com/zendframework/zend-console/pull/12) updates the
  `Zend\Console\Prompt\Char::show()` method to call on the composed adapter's
  `write()`/`writeLine()` methods instead of calling `echo()`.
