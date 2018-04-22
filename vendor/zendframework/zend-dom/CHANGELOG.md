# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.1 - 2018-04-09

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#21](https://github.com/zendframework/zend-dom/pull/21) fixes an issue with
  matching against nested attribute selectors (e.g., `div[class="foo"] div
  [class="bar"]`), ensuring such syntax will transform to expected XPath.

- [#22](https://github.com/zendframework/zend-dom/pull/22) adds a missing import
  statement for the `DOMNode` class to the (deprecated) `Zend\Dom\Query` class
  definition.

- [#24](https://github.com/zendframework/zend-dom/pull/24) updates how the
  tokenizer marks multiple words within attribute values in order to be
  more robust.

- [#23](https://github.com/zendframework/zend-dom/pull/23) fixes an issue with
  how descendant selectors work, ensuring spaces may be used around the `>`
  operator.

## 2.7.0 - 2018-03-27

### Added

- [#20](https://github.com/zendframework/zend-dom/pull/20) adds support for
  attribute selectors that contain spaces, such as `input[value="Marty McFly"]`.
  Previously, spaces within the selector value would result in a query per
  space-separated word; they now, correctly, result in a single query for the
  exact value.

- [#19](https://github.com/zendframework/zend-dom/pull/19) adds support for PHP
  versions 7.1 and 7.2.

- Adds documentation and publishes it to https://docs.zendframework.com/zend-dom/

### Deprecated

- Nothing.

### Removed

- [#13](https://github.com/zendframework/zend-dom/pull/13) and
  [#19](https://github.com/zendframework/zend-dom/pull/19) remove support for PHP
  versions prior to 5.6.

- [#13](https://github.com/zendframework/zend-dom/pull/13) and
  [#19](https://github.com/zendframework/zend-dom/pull/19) remove support for HHVM.

### Fixed

- Nothing.

## 2.6.0 - 2015-10-13

### Added

- [#2](https://github.com/zendframework/zend-dom/pull/2) adds context node
  support for DOMXPath->query that supports querying in the context of a
  specific node.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#5](https://github.com/zendframework/zend-dom/pull/5) - Increase test converage and improve tests.
