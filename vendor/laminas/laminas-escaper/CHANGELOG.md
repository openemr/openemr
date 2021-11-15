# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2020-11-17

### Added

- [#10](https://github.com/laminas/laminas-escaper/pull/10) Adds Psalm as QA tool

- [#9](https://github.com/laminas/laminas-escaper/pull/9) Adds PHP 8.0 support


-----

### Release Notes for [2.7.0](https://github.com/laminas/laminas-escaper/milestone/2)

next feature release (minor)

### 2.7.0

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Enhancement,hacktoberfest-accepted

 - [10: Add Psalm integration](https://github.com/laminas/laminas-escaper/pull/10) thanks to @ocean
 - [9: PHP 8.0 Support](https://github.com/laminas/laminas-escaper/pull/9) thanks to @Gounlaf

## 2.6.1 - 2019-09-05

### Added

- [zendframework/zend-escaper#32](https://github.com/zendframework/zend-escaper/pull/32) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.0 - 2018-04-25

### Added

- [zendframework/zend-escaper#28](https://github.com/zendframework/zend-escaper/pull/28) adds support for PHP 7.1 and 7.2.

### Changed

- [zendframework/zend-escaper#25](https://github.com/zendframework/zend-escaper/pull/25) changes the behavior of the `Escaper` constructor; it now raises an
  exception for non-null, non-string `$encoding` arguments.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-escaper#28](https://github.com/zendframework/zend-escaper/pull/28) removes support for PHP 5.5.

- [zendframework/zend-escaper#28](https://github.com/zendframework/zend-escaper/pull/28) removes support for HHVM.

### Fixed

- Nothing.

## 2.5.2 - 2016-06-30

### Added

- [zendframework/zend-escaper#11](https://github.com/zendframework/zend-escaper/pull/11),
  [zendframework/zend-escaper#12](https://github.com/zendframework/zend-escaper/pull/12), and
  [zendframework/zend-escaper#13](https://github.com/zendframework/zend-escaper/pull/13) prepare and
  publish documentation to https://docs.laminas.dev/laminas-escaper/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-escaper#3](https://github.com/zendframework/zend-escaper/pull/3) updates the
  the escaping mechanism to add support for escaping characters outside the Basic
  Multilingual Plane when escaping for JS, CSS, or HTML attributes.
