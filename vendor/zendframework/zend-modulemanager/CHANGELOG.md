# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.2 - 2017-12-02

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#74](https://github.com/zendframework/zend-modulemanager/pull/74) Fixes
  exception message in ConfigListener

## 2.8.1 - 2017-11-01

### Added

- Nothing.

### Changed

- [#73](https://github.com/zendframework/zend-modulemanager/pull/73) modifies
  the `ModuleResolverListener` slightly. In
  [#5](https://github.com/zendframework/zend-modulemanager/pull/5),
  released in 2.8.0, we added the ability to use classes named after the module
  itself as a module class. However, in some specific cases, primarily when the
  module is a top-level namespace, this can lead to conflicts with
  globally-scoped classes. The patch in this release modifies the logic to first
  check if a `Module` class exists under the module namespace, and will use
  that; otherwise, it will then check if a class named after the namespace
  exists. Additionally, the class now implements a blacklist of specific classes
  known to be non-instantiable, including the `Generator` class shipped with the
  PHP language itself.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.0 - 2017-07-11

### Added

- [#4](https://github.com/zendframework/zend-modulemanager/pull/4) adds a new
  `ListenerOptions` option, `use_zend_loader`. The option defaults to `true`,
  which keeps the current behavior of registering the `ModuleAutoloader` and
  `AutoloaderProvider`. If you disable it, these features will no longer be
  loaded, allowing `ModuleManager` to be used without zend-loader.
- [#5](https://github.com/zendframework/zend-modulemanager/pull/5) adds the
  ability to use a class of any name for a module, so long as you provide the
  fully qualified class name when registering the module with the module
  manager.

### Deprecated

- Nothing.

### Removed

- [#62](https://github.com/zendframework/zend-modulemanager/pull/62) removes
  support for PHP 5.5 and HHVM.

### Fixed

- [#53](https://github.com/zendframework/zend-modulemanager/pull/53) preventing race conditions
  when writing cache files (merged configuration)

## 2.7.3 - 2017-07-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#39](https://github.com/zendframework/zend-modulemanager/pull/39) and
  [#53](https://github.com/zendframework/zend-modulemanager/pull/53) prevent
  race conditions when writing cache files (merged configuration).
- [#36](https://github.com/zendframework/zend-modulemanager/pull/36) removes a
  throw from `ServiceListener::onLoadModulesPost()` that was previously emitted
  when a named plugin manager did not have an associated service present yet.
  Doing so allows plugin managers to be registered after configuration is fully
  merged, instead of requiring they be defined early. This change allows
  components to define their plugin managers via their `Module` classes.
- [#58](https://github.com/zendframework/zend-modulemanager/pull/58) corrects
  the typehint for the `ServiceListener::$listeners` property.

## 2.7.2 - 2016-05-16

### Added

- [#38](https://github.com/zendframework/zend-modulemanager/pull/38) prepares
  and publishes the documentation to https://zendframework.github.io/zend-modulemanager/
- [#40](https://github.com/zendframework/zend-modulemanager/pull/40) adds a
  requirement on zend-config. Since the default use case centers around config
  merging and requires the component, it should be required by
  zend-modulemanager.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.1 - 2016-02-27

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#31](https://github.com/zendframework/zend-modulemanager/pull/31) updates the
  `ServiceListener:onLoadModulesPost()` workflow to override existing services
  on a given service/plugin manager instance when configuring it. Since the
  listener operates as part of bootstrapping, this is a requirement.

## 2.7.0 - 2016-02-25

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#13](https://github.com/zendframework/zend-modulemanager/pull/13) and
  [#28](https://github.com/zendframework/zend-modulemanager/pull/28) update the
  component to be forwards-compatible with zend-servicemanager v3. This
  primarily affects how configuration is aggregated within the
  `ServiceListener` (as v3 has a dedicated method in the
  `Zend\ServiceManager\ConfigInterface` for retrieving it).

- [#12](https://github.com/zendframework/zend-modulemanager/pull/12),
  [#28](https://github.com/zendframework/zend-modulemanager/pull/28), and
  [#29](https://github.com/zendframework/zend-modulemanager/pull/29) update the
  component to be forwards-compatible with zend-eventmanager v3. Primarily, this
  involves:
  - Changing trigger calls to `triggerEvent()` and/or `triggerEventUntil()`, and
    ensuring the event instance is injected with the new event name prior.
  - Ensuring aggregates are attached using the `$aggregate->attach($events)`
    signature instead of the `$events->attachAggregate($aggregate)` signature.
  - Using zend-eventmanager's `EventListenerIntrospectionTrait` to test that
    listeners are attached at expected priorities.

## 2.6.1 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed a condition where the `ModuleEvent` target was not properly populated
  with the `ModuleManager` as the target.

## 2.6.0 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#10](https://github.com/zendframework/zend-modulemanager/pull/10) pins the
  zend-stdlib version to `~2.7`, allowing it to use that version forward, and
  ensuring compatibility with consumers of the new zend-hydrator library.

## 2.5.3 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed a condition where the `ModuleEvent` target was not properly populated
  with the `ModuleManager` as the target.

## 2.5.2 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#9](https://github.com/zendframework/zend-modulemanager/pull/9) pins the
  zend-stdlib version to `>=2.5.0,<2.7.0`, as 2.7.0 deprecates the hydrators (in
  favor of the new zend-hydrator library).
