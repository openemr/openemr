# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.10.0 - 2021-01-21

### Changed

- [#6](https://github.com/laminas/laminas-xmlrpc/pull/6) updates the minimum supported version of PHP to 7.2.


-----

### Release Notes for [2.10.0](https://github.com/laminas/laminas-xmlrpc/milestone/1)



### 2.10.0

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### Enhancement

 - [10: Allow and test installation on PHP 8 ](https://github.com/laminas/laminas-xmlrpc/pull/10) thanks to @Ocramius
 - [9: updated for PHP8](https://github.com/laminas/laminas-xmlrpc/pull/9) thanks to @delboy1978uk

 - [6: QA: Update to PHP 7.2 + PHPUnit 8.5](https://github.com/laminas/laminas-xmlrpc/pull/6) thanks to @arueckauer

## 2.9.0 - 2019-12-27

### Added

- Nothing.

### Changed

- [zendframework/zend-xmlrpc#40](https://github.com/zendframework/zend-xmlrpc/pull/40) modifies detection of integer values on 64-bit systems. Previously, i8 values parsed by the client were always cast to BigInteger values. Now, on 64-bit systems, they are cast to integers.

Disables use of BigInteger for XMLRPC i8 type if host machine is 64-bit.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.0 - 2019-10-19

### Added

- [zendframework/zend-xmlrpc#38](https://github.com/zendframework/zend-xmlrpc/pull/38) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-xmlrpc#38](https://github.com/zendframework/zend-xmlrpc/pull/38) removes support for laminas-stdlib v2 releases.

### Fixed

- Nothing.

## 2.7.0 - 2018-05-14

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-xmlrpc#32](https://github.com/zendframework/zend-xmlrpc/pull/32) removes support for HHVM.

### Fixed

- Nothing.

## 2.6.2 - 2018-01-25

### Added

- [zendframework/zend-xmlrpc#29](https://github.com/zendframework/zend-xmlrpc/pull/29) adds support for
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

- [zendframework/zend-xmlrpc#27](https://github.com/zendframework/zend-xmlrpc/pull/27) fixed a memory leak
  caused by repetitive addition of `Accept` and `Content-Type` headers on subsequent
  HTTP requests produced by the `Laminas\XmlRpc\Client`.

## 2.6.0 - 2016-06-21

### Added

- [zendframework/zend-xmlrpc#19](https://github.com/zendframework/zend-xmlrpc/pull/19) adds support for
  laminas-math v3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2016-04-21

### Added

- [zendframework/zend-xmlrpc#11](https://github.com/zendframework/zend-xmlrpc/pull/11),
  [zendframework/zend-xmlrpc#12](https://github.com/zendframework/zend-xmlrpc/pull/12),
  [zendframework/zend-xmlrpc#13](https://github.com/zendframework/zend-xmlrpc/pull/13),
  [zendframework/zend-xmlrpc#14](https://github.com/zendframework/zend-xmlrpc/pull/14),
  [zendframework/zend-xmlrpc#15](https://github.com/zendframework/zend-xmlrpc/pull/15), and
  [zendframework/zend-xmlrpc#16](https://github.com/zendframework/zend-xmlrpc/pull/16)
  added and prepared the documentation for publication at
  https://docs.laminas.dev/laminas-xmlrpc/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-xmlrpc#17](https://github.com/zendframework/zend-xmlrpc/pull/17) updates
  dependencies to allow laminas-stdlib v3 releases.
