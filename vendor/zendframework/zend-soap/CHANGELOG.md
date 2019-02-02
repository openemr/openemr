# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2018-01-29

### Added

- [#42](https://github.com/zendframework/zend-soap/pull/42) adds support for PHP
  versions 7.1 and 7.2.

- [#31](https://github.com/zendframework/zend-soap/pull/31) adds support for
  `xsd:date` elements.

- [#36](https://github.com/zendframework/zend-soap/pull/36) adds support for
  the libxml `LIBXML_PARSEHUGE` flag when creating a `Server` instance. When the
  support is enabled, the `Server` instance will pass that flag to
  `DOMDocument::loadXML()`. The flag may be set in one of two ways:

  - By passing the option `parse_huge` within the configuration `$options`
    passed to the constructor and/or `setOptions()` method.
  - Via a new mutator method, `Server::setParseHuge()`.

### Changed

- [#38](https://github.com/zendframework/zend-soap/pull/38) adds `ext-soap` as
  an explicit package dependency. While it was previously implied; installation
  will now fail if that dependency is missing.

### Deprecated

- Nothing.

### Removed

- [#42](https://github.com/zendframework/zend-soap/pull/42) removes support for
  PHP 5.5.

- [#42](https://github.com/zendframework/zend-soap/pull/42) removes support for
  HHVM.

- [#49](https://github.com/zendframework/zend-soap/pull/49) removes all
  arguments besides `$errno` and `$errstr` from the `Server::handlePhpError()`
  method, as they were unused.

### Fixed

- Nothing.

## 2.6.0 - 2016-04-21

### Added

- [#1](https://github.com/zendframework/zend-soap/pull/1) adds
  support for the `SoapClient` options `keep_alive` and `ssl_method`.
- [#20](https://github.com/zendframework/zend-soap/pull/20) adds support for
  the  `SoapServer` `send_errors` constructor option.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2016-04-21

### Added

- Adds GitHub Pages documentation at https://zendframework.github.io/zend-soap/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-soap/pull/7) fixes
  behavior when the request contains empty content.
- [#21](https://github.com/zendframework/zend-soap/pull/21) updates the
  dependencies to allow usage with zend-stdlib v3 releases.
