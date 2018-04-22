# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.2 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.1 - 2017-11-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#26](https://github.com/zendframework/zend-serializer/pull/26)
  Travis: Remove fast finish flag
- [#32](https://github.com/zendframework/zend-serializer/pull/32)
  Travis: fix doctrine/instantiator on PHP-5.6
- [#27](https://github.com/zendframework/zend-serializer/pull/27)
  CS fixes for new coding standard
- [#25](https://github.com/zendframework/zend-serializer/pull/25)
  Travis: Move coverage to latest-env to make composer.lock usefull again
- [#30](https://github.com/zendframework/zend-serializer/issues/30) 
  Fixed unserialize PythonPickle on big endian

## 2.8.0 - 2016-06-21

### Added

- [#20](https://github.com/zendframework/zend-serializer/pull/20) adds support
  for zend-json v3.0.

### Deprecated

- Nothing.

### Removed

- [#20](https://github.com/zendframework/zend-serializer/pull/20) removes support
  for PHP 5.5.

### Fixed

- Nothing.

## 2.7.2 - 2016-05-11

### Added

- [#17](https://github.com/zendframework/zend-serializer/pull/17) adds and
  publishes the documentation to https://zendframework.github.com/zend-serializer/

### Deprecated

- Nothing.

### Removed

- [#16](https://github.com/zendframework/zend-serializer/pull/16) removes the
  dependency on zend-math, making it optional. (This was done as the requirement
  is only when using the `PythonPickle` adapter.)

### Fixed

- [#9](https://github.com/zendframework/zend-serializer/pull/9) fixes the
  behavior of the `PhpSerialize` adapter to raise an exception during
  deserialization if the value is not serialized, restoring behavior to match
  the other adapters.

## 2.7.1 - 2016-04-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#15](https://github.com/zendframework/zend-serializer/pull/15) fixes the
  `Module::init()` method to properly receive a `ModuleManager` instance, and
  not expect a `ModuleEvent`.

## 2.7.0 - 2016-04-06

### Added

- [#14](https://github.com/zendframework/zend-serializer/pull/14) exposes the
  package as a ZF component and/or generic configuration provider, by adding the
  following:
  - `AdapterPluginManagerFactory`, which can be consumed by container-interop /
    zend-servicemanager to create and return a `AdapterPluginManager` instance.
  - `ConfigProvider`, which maps the service `SerializerAdapterManager` to the above
    factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    zend-mvc applications. It also provices a specification to
    `Zend\ModuleManager\Listener\ServiceListener` to allow modules to provide
    serializer configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-02-03

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#13](https://github.com/zendframework/zend-serializer/pull/13) updates the
  zend-stdlib dependency to `^2.7 || ^3.0`, as it can work with either version.

## 2.6.0 - 2016-02-02

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#2](https://github.com/zendframework/zend-serializer/pull/2) updates the component
  to use zend-servicemanager v3. This involves updating the `AdapterPluginManager`
  to follow changes to `Zend\ServiceManager\AbstractPluginManager`, and updating
  the `Serializer` class to inject an empty `ServiceManager` into instances of
  the `AbstractPluginManager` that it creates.
