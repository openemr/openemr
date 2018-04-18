# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.1 - 2017-01-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#34](https://github.com/zendframework/zend-file/pull/34) ensures that
  anonymous classes are ignored by the `ClassFileLocator` when identifying files
  with class declarations.

## 2.7.0 - 2016-04-28

### Added

- [#25](https://github.com/zendframework/zend-file/pull/25) adds and publishes
  documentation to https://zendframework.github.io/zend-file/

### Deprecated

- [#25](https://github.com/zendframework/zend-file/pull/25) deprecates the
  `Zend\File\Transfer` subcomponent. Its functionality is now split between each
  of:
  - zend-filter, for moving uploaded files to their final location, renaming
    them, and potentially transforming them.
  - zend-validator, for validating upload succes, file type, hash, etc.
  - zend-progressbar, for managing upload status.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-03-02

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#21](https://github.com/zendframework/zend-file/pull/21) updates the codebase
  to re-enable tests against zend-progressbar, and fixes static calls inside
  `Zend\File\Transfer\Adapter\Http::getProgress` for testing APC and/or
  uploadprogress availability to ensure they work correctly.

## 2.6.0 - 2016-02-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#18](https://github.com/zendframework/zend-file/pull/18) updates the codebase
  to be forwards compatible with zend-servicemanager and zend-stdlib v3.

## 2.5.2 - 2016-02-16

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#13](https://github.com/zendframework/zend-file/pull/13) fixes the behavior
  of the `Zend\File\Transfer` component when multiple uploads using the same
  client name are provided, and no filename filtering is performed; the code now
  ensures that unique names are used in such situations.
- [#14](https://github.com/zendframework/zend-file/pull/14) updates the
  `FilterPluginManager` to work with the updated zend-filter 2.6.0 changes,
  fixing an issue where the zend-file filters were replacing (instead of
  merging) with those in the parent zend-filter implementation.
