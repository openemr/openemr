# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.0 - 2018-04-11

### Added

- [#26](https://github.com/zendframework/zend-filter/pull/26) adds the interface
  `Zend\Filter\FilterProviderInterface`, which can be used to provide
  configuration for the `FilterPluginManager` via zend-mvc `Module` classes.

- [#61](https://github.com/zendframework/zend-filter/pull/61) adds support for
  PHP 7.2.

### Deprecated

- Nothing.

### Removed

- [#61](https://github.com/zendframework/zend-filter/pull/61) removes support
  for PHP 5.5.

- [#61](https://github.com/zendframework/zend-filter/pull/61) removes support
  for HHVM.

- [#61](https://github.com/zendframework/zend-filter/pull/61) removes support
  for zend-crypt versions prior to 3.0. This was done as PHP deprecated the
  mcrypt extension starting in PHP 7.1, and does not ship it by default
  starting in PHP 7.2. zend-crypt 3.0 adds an OpenSSL adapter for its
  BlockCipher capabilities, and acts as a polyfill for mcrypt usage. Since this
  functionality has been used by default since 2.7.2, users should be able to
  upgrade seamlessly.

### Fixed

- Nothing.

## 2.7.2 - 2017-05-17

### Added

- Nothing.

### Changes

- [#40](https://github.com/zendframework/zend-filter/pull/40) updates the
  `Callback` filter's `setCallback()` method to allow passing a string name of a
  class that is instantiable without constructor arguments, and which defines
  `__invoke()`.
- [#43](https://github.com/zendframework/zend-filter/pull/43) updates the
  exception thrown by the `File\Rename` filter when the target already exists to
  indicate the target filename path.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#56](https://github.com/zendframework/zend-filter/pull/56) fixes how the
  `FilterPluginManagerFactory` factory initializes the plugin manager instance,
  ensuring it is injecting the relevant configuration from the `config` service
  and thus seeding it with configured translator loader services. This means
  that the `filters` configuration will now be honored in non-zend-mvc contexts.
- [#36](https://github.com/zendframework/zend-filter/pull/36) fixes an issue in
  the constructor whereby a discovered option was not removed from the options
  list after being used to set the compression algorithm.
- [#49](https://github.com/zendframework/zend-filter/pull/49) and
  [#51](https://github.com/zendframework/zend-filter/pull/51) fix logic within
  the `Boolean` and `ToNull` filters to use boolean rather than arithmetic
  operations, ensuring that if the same type is specified multiple times via the
  options, it will be aggregated correctly internally, and thus ensure correct
  operation of the filter.
- [#55](https://github.com/zendframework/zend-filter/pull/55) adds a missing
  import statement to the `Word\SeparatorToSeparatorFactory`.

## 2.7.1 - 2016-04-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#27](https://github.com/zendframework/zend-filter/pull/27) fixes the
  `Module::init()` method to properly receive a `ModuleManager` instance, and
  not expect a `ModuleEvent`.

## 2.7.0 - 2016-04-06

### Added

- [#25](https://github.com/zendframework/zend-filter/pull/25) exposes the
  package as a ZF component and/or generic configuration provider, by adding the
  following:
  - `FilterPluginManagerFactory`, which can be consumed by container-interop /
    zend-servicemanager to create and return a `FilterPluginManager` instance.
  - `ConfigProvider`, which maps the service `FilterManager` to the above
    factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    zend-mvc applications. It also provices a specification to
    `Zend\ModuleManager\Listener\ServiceListener` to allow modules to provide
    filter configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-02-08

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#24](https://github.com/zendframework/zend-filter/pull/24) updates the
  `FilterPluginManager` to reference the `NumberFormat` **filter**, instead of
  the **view helper**.

## 2.6.0 - 2016-02-04

### Added

- [#14](https://github.com/zendframework/zend-filter/pull/14) adds the
  `UpperCaseWords` filter to the default list of filters known to the
  `FilterPluginManager`.
- [#22](https://github.com/zendframework/zend-filter/pull/22) adds
  documentation, and automatically publishes it to
  https://zendframework.github.io/zend-filter/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#15](https://github.com/zendframework/zend-filter/pull/15),
  [#19](https://github.com/zendframework/zend-filter/pull/19), and
  [#21](https://github.com/zendframework/zend-filter/pull/21)
  update the component to be forwards-compatible with zend-servicemanager v3,
  and reduce the number of development dependencies required for testing.
