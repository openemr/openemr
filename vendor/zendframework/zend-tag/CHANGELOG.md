# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2018-04-30

### Added

- [#21](https://github.com/zendframework/zend-tag/pull/21) adds support for PHP 7.1 and 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#21](https://github.com/zendframework/zend-tag/pull/21) removes support for PHP 5.5.

- [#21](https://github.com/zendframework/zend-tag/pull/21) removes support for HHVM.

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

- [#13](https://github.com/zendframework/zend-tag/pull/13) updates the
  zend-stdlib dependency to `^2.7 || ^3.0`, instead of just `^3.0`, allowing
  it to work with users of zf2 v2 versions. Since the functionality consumed is
  present and unchanged in both versions, this is a safe constraint.

## 2.6.0 - 2016-02-03

### Added

- [#11](https://github.com/zendframework/zend-tag/pull/11) adds documentation
  and publishes it to https://zendframework.github.io/zend-tag/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/zendframework/zend-tag/pull/3) and
  [#10](https://github.com/zendframework/zend-tag/pull/10) update the component
  to be forward-compatible with zend-servicemanager v3.
