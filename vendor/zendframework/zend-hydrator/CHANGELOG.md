# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - 2016-02-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#24](https://github.com/zendframework/zend-hydrator/pull/24) updates the code
  to be forwards-compatible with the v3 releases of zend-stdlib,
  zend-eventmanager, and zend-servicemanager.

## 1.0.0 - 2015-09-17

Initial release. This ports all hydrator classes and functionality from
[zend-stdlib](https://github.com/zendframework/zend-stdlib) to a standalone
repository. All final keywords are removed, to allow a deprecation cycle in the
zend-stdlib component.

Please note: the following classes will be marked as `final` for a version 2.0.0
release to immediately follow 1.0.0:

- `Zend\Hydrator\NamingStrategy\IdentityNamingStrategy`
- `Zend\Hydrator\NamingStrategy\ArrayMapNamingStrategy`
- `Zend\Hydrator\NamingStrategy\CompositeNamingStrategy`
- `Zend\Hydrator\Strategy\ExplodeStrategy`
- `Zend\Hydrator\Strategy\StrategyChain`
- `Zend\Hydrator\Strategy\DateTimeFormatterStrategy`
- `Zend\Hydrator\Strategy\BooleanStrategy`

As such, you should not extend them.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
