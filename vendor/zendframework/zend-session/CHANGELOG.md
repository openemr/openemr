# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.6 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.5 - 2018-02-22

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed


- [#108](https://github.com/zendframework/zend-session/pull/108) fixes a dependency
  conflict in `composer.json` which prevented `phpunit/phpunit` 6.5 or newer from 
  being installed together with `zendframework/zend-session`.

## 2.8.4 - 2018-01-31

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#107](https://github.com/zendframework/zend-session/pull/107) fixes an error
  raised by `ini_set()` within `SessionConfig::setStorageOption()` that occurs
  for certain INI values that cannot be set if the session is active. When this
  situation occurs, the class performs a `session_write_close()`, sets the new
  INI value, and then restarts the session. As such, we recommend that you
  either set production INI values in your production `php.ini`, and/or always
  pass your fully configured session manager to container instances you create.

- [#105](https://github.com/zendframework/zend-session/pull/105) fixes an edge
  case whereby if the special `__ZF` session value is a non-array value,
  initializing the session would result in errors.

- [#102](https://github.com/zendframework/zend-session/pull/102) fixes an issue
  introduced with 2.8.0 with `AbstractContainer::offsetGet`. Starting in 2.8.0,
  if the provided `$key` did not exist, the method would raise an error
  regarding an invalid variable reference; this release provides a fix that
  resolves that issue.

## 2.8.3 - 2017-12-01

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#101](https://github.com/zendframework/zend-session/pull/101) fixes an issue
  created with the 2.8.2 release with regards to setting a session save path for
  non-files save handlers; prior to this patch, incorrect validations were run
  on the path provided, leading to unexpected exceptions being raised.

## 2.8.2 - 2017-11-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#85](https://github.com/zendframework/zend-session/pull/85) fixes an issue
  with how the expiration seconds are handled when a long-running request
  occurs. Previously, when called, it would use the value of
  `$_SERVER['REQUEST_TIME']` to calculate the expiration time; this would cause
  failures if the expiration seconds had been reached by the time the value was
  set. It now correctly uses the current `time()`.

- [#99](https://github.com/zendframework/zend-session/pull/99) fixes how
  `Zend\Session\Config\SessionConfig` handles attaching save handlers to ensure
  it will honor any handlers registered with the PHP engine (e.g., redis,
  rediscluster, etc.).

## 2.8.1 - 2017-11-28

### Added

- [#92](https://github.com/zendframework/zend-session/pull/92) adds PHP 7.2
  support.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#57](https://github.com/zendframework/zend-session/pull/57) and
  [#93](https://github.com/zendframework/zend-session/pull/93) provide a fix
  for when data found in the session is a `Traversable`; such data is now cast
  to an array before merging with new data.

## 2.8.0 - 2017-06-19

### Added

- [#78](https://github.com/zendframework/zend-session/pull/78) adds support for
  PHP 7.1, and specifically the following options:
  - `session.sid_length`
  - `session.sid_bits_per_character`

### Changed

- [#73](https://github.com/zendframework/zend-session/pull/73) modifies the
  `SessionManagerFactory` to take into account the `$requestedName`; if the
  `$requestedName` is the name of a class that implements `ManagerInterface`,
  that class will be instantiated instead of `SessionManager`, but using the
  same arguments (`$config, $storage, $savehandler, $validators, $options`).

- [#78](https://github.com/zendframework/zend-session/pull/78) updates the
  `SessionConfig` class to emit deprecation notices under PHP 7.1+ when a user
  attempts to set INI options no longer supported by PHP 7.1+, including:
  - `session.entropy_file`
  - `session.entropy_length`
  - `session.hash_function`
  - `session.hash_bits_per_character`

### Deprecated

- Nothing.

### Removed

- [#78](https://github.com/zendframework/zend-session/pull/78) removes support
  for PHP 5.5.

- [#78](https://github.com/zendframework/zend-session/pull/78) removes support
  for HHVM.

### Fixed

- Nothing.

## 2.7.4 - 2017-06-19

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#66](https://github.com/zendframework/zend-session/pull/66) fixes how the
  `Cache` save handler's `destroy()` method works, ensuring it does not attempt
  to remove an item by `$id` if it does not already exist in the cache.
- [#79](https://github.com/zendframework/zend-session/pull/79) updates the
  signature of `AbstractContainer::offsetGet()` to match
  `Zend\Stdlib\ArrayObject` and return by reference, fixing an issue when
  running under PHP 7.1+.

## 2.7.3 - 2016-07-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#51](https://github.com/zendframework/zend-session/pull/51) provides a fix to
  the `DbTableGateway` save handler to prevent infinite recursion when
  attempting to destroy an expired record during initial read operations.
- [#45](https://github.com/zendframework/zend-session/pull/45) updates the
  `SessionManager::regenerateId()` method to only regenerate the identifier if a
  session already exists.

## 2.7.2 - 2016-06-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#46](https://github.com/zendframework/zend-session/pull/46) provides fixes to
  each of the `Cache` and `DbTaleGateway` save handlers to ensure they work
  when used under PHP 7.

## 2.7.1 - 2016-05-11

### Added

- [#40](https://github.com/zendframework/zend-session/pull/40) adds and
  publishes the documentation to https://zendframework.github.io/zend-session/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#38](https://github.com/zendframework/zend-session/pull/38) ensures that the
  value from `session.gc_maxlifetime` is cast to an integer before assigning
  it as the `lifetime` value in the `MongoDB` adapter, ensuring sessions may be
  deleted.

## 2.7.0 - 2016-04-12

### Added

- [#23](https://github.com/zendframework/zend-session/pull/23) provides a new
  `Id` validator to ensure that the session identifier is not malformed. This
  validator is now enabled by default; to disable it, pass
  `['attach_default_validators' => false]` as the fifth argument to
  `SessionManager`, or pass an `options` array with that value under the
  `session_manager` configuration key.
- [#34](https://github.com/zendframework/zend-session/pull/34) adds the option
  to use `exporeAfterSeconds` with the `MongoDB` save handler.
- [#37](https://github.com/zendframework/zend-session/pull/37) exposes the
  package as a standalone config-provider/component, adding:
  - `Zend\Session\ConfigProvider`, which maps the default services offered by
    the package, including the `ContainerAbstractServiceFactory`.
  - `Zend\Session\Module`, which does the same, but for zend-mvc contexts.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#34](https://github.com/zendframework/zend-session/pull/34) updates the
  component to use ext/mongodb + the MongoDB PHP client library, instead of
  ext/mongo, for purposes of the `MongoDB` save handler, allowing the component
  to be used with modern MongoDB installations.

## 2.6.2 - 2016-02-25

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#32](https://github.com/zendframework/zend-session/pull/32) provides a better
  polfill for the `ValidatorChain` to ensure it can be represented in
  auto-generated classmaps (e.g., via `composer dump-autoload --optimize` and/or
  `composer dump-autoload --classmap-authoritative`).

## 2.6.1 - 2016-02-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#29](https://github.com/zendframework/zend-session/pull/29) extracts the
  constructor defined in `Zend\Session\Validator\ValidatorChainTrait` and pushes
  it into each of the `ValidatorChainEM2` and `ValidatorChainEM3`
  implementations, to prevent colliding constructor definitions due to
  inheritance + trait usage.

## 2.6.0 - 2016-02-23

### Added

- [#29](https://github.com/zendframework/zend-session/pull/29) adds two new
  classes: `Zend\Session\Validator\ValidatorChainEM2` and `ValidatorChainEM3`.
  Due to differences in the `EventManagerInterface::attach()` method between
  zend-eventmanager v2 and v3, and the fact that `ValidatorChain` overrides that
  method, we now need an implementation targeting each major version. To provide
  a consistent use case, we use a polyfill that aliases the appropriate version
  to the `Zend\Session\ValidatorChain` class.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#29](https://github.com/zendframework/zend-session/pull/29) updates the code
  to be forwards compatible with the v3 releases of zend-eventmanager and
  zend-servicemanager.
- [#7](https://github.com/zendframework/zend-session/pull/7) Mongo save handler
  was using sprintf formatting without sprintf.

## 2.5.2 - 2015-07-29

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/zendframework/zend-session/pull/3) Utilize
  SaveHandlerInterface vs. our own.

- [#2](https://github.com/zendframework/zend-session/pull/2) detect session
  exists by use of *PHP_SESSION_ACTIVE*
