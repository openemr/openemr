# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.0 - 2016-02-04

### Added

- [#6](https://github.com/zendframework/zend-config/pull/6) adds the ability for
  the `PhpArray` writer to optionally translate strings that evaluate to known
  classes to `ClassName::class` syntax; the feature works for both keys and
  values.
- [#21](https://github.com/zendframework/zend-config/pull/21) adds revised
  documentation, and publishes it to https://zendframework.github.io/zend-config/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#8](https://github.com/zendframework/zend-config/pull/8),
  [#18](https://github.com/zendframework/zend-config/pull/18), and
  [#20](https://github.com/zendframework/zend-config/pull/20) update the
  code base to make it forwards-compatible with the v3.0 versions of
  zend-stdlib and zend-servicemanager. Primarily, this involved:
  - Updating the `AbstractConfigFactory` to implement the new methods in the
    v3 `AbstractFactoryInterface` definition, and updating the v2 methods to
    proxy to those.
  - Updating `ReaderPluginManager` and `WriterPluginManager` to follow the
    changes to `AbstractPluginManager`. In particular, instead of defining
    invokables, they now define a combination of aliases and factories (using
    the new `InvokableFactory`); additionally, they each now implement both
    `validatePlugin()` from v2 and `validate()` from v3.
  - Pinning to stable versions of already updated components.
  - Selectively omitting zend-i18n-reliant tests when testing against
    zend-servicemanager v3.
