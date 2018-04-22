# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.1 - 2016-02-04

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#18](https://github.com/zendframework/zend-json/pull/18) updates dependencies
  to allow usage on PHP 7, as well as with zend-stdlib v3.

## 2.6.0 - 2015-11-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#5](https://github.com/zendframework/zend-json/pull/5) removes
  zendframework/zend-stdlib as a required dependency, marking it instead
  optional, as it is only used for the `Server` subcomponent.

### Fixed

- Nothing.

## 2.5.2 - 2015-08-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/zendframework/zend-json/pull/3) fixes an array key
  name from `intent` to `indent` to  ensure indentation works correctly during
  pretty printing.
