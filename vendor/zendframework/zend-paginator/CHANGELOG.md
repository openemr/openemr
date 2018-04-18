# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.1 - 2018-01-30

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#45](https://github.com/zendframework/zend-paginator/pull/45) fixes an error
  in the `DbSelectFactory` whereby it ignored the fourth option passed via
  `$options`, which can be used to specify a zend-db `Select` instance for
  purposes of counting the rows that will be returned.

## 2.8.0 - 2017-11-01

### Added

- [#20](https://github.com/zendframework/zend-paginator/pull/20) adds
  and publishes the documentation to https://docs.zendframework.com/zend-paginator/

- [#38](https://github.com/zendframework/zend-paginator/pull/38) adds support
  for PHP 7.1.

- [#38](https://github.com/zendframework/zend-paginator/pull/38) adds
  support for PHP 7.2. This is dependent on fixes in the upstream zend-db
  package if you are using the various database-backed paginators; other
  paginators work on 7.2 at this time.

### Changed

- [#32](https://github.com/zendframework/zend-paginator/pull/32) updates the
  `DbTableGateway` adapter's constructor to allow any
  `Zend\Db\TableGateway\AbstractTableGateway` implementation, and not just
  `Zend\Db\TableGateway\TableGateway` instances. This is a parameter widening,
  which poses no backwards compatibility break, but does provide users the
  ability to consume their own `AbstractTableGateway` extensions.

### Deprecated

- Nothing.

### Removed

- [#35](https://github.com/zendframework/zend-paginator/pull/35) removes support
  for PHP 5.5.

- [#35](https://github.com/zendframework/zend-paginator/pull/35) removes support
  for HHVM.

### Fixed

- [#33](https://github.com/zendframework/zend-paginator/pull/33) fixes how cache
  identifiers are generated to work propertly with non-serializable pagination
  adapters.

- [#26](https://github.com/zendframework/zend-paginator/pull/26) fixes an issue
  in `Paginator::count()` whereby it would re-count when zero pages had been
  previously detected.

## 2.7.0 - 2016-04-11

### Added

- [#19](https://github.com/zendframework/zend-paginator/pull/19) adds:
  - `Zend\Paginator\AdapterPluginManagerFactory`
  - `Zend\Paginator\ScrollingStylePluginManagerFactory`
  - `ConfigProvider`, which maps the `AdapterPluginManager` and
    `ScrollingStylePluginManager` services to the above factories.
  - `Module`, which does the same, for zend-mvc contexts.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-04-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-paginator/pull/7) adds aliases for
  the old `Null` adapter, mapping them to the new `NullFill` adapter.

## 2.6.0 - 2016-02-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#4](https://github.com/zendframework/zend-paginator/pull/4),
  [#8](https://github.com/zendframework/zend-paginator/pull/8), and
  [#18](https://github.com/zendframework/zend-paginator/pull/18) update the code
  base to be forwards-compatible with the v3 releases of zend-servicemanager and
  zend-stdlib.
