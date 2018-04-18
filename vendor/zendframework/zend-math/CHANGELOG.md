# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2016-04-07

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#16](https://github.com/zendframework/zend-math/pull/16) updates
  `Zend\Math\Rand` to use PHP 7's `random_bytes()` and `random_int()` or mcrypt
  when detected, and fallback to `ircmaxell/RandomLib` otherwise, instead of using
  openssl. This provides more cryptographically secure pseudo-random generation.


## 2.6.0 - 2016-02-02

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#5](https://github.com/zendframework/zend-math/pull/5) removes
  `Zend\Math\BigInteger\AdapterPluginManager`, and thus the zend-servicemanager
  dependency. Essentially, no other possible plugins are likely to ever be
  needed outside of those shipped with the component, so using a plugin manager
  was overkill. The functionality for loading the two shipped adapters has been

### Fixed

- Nothing.

## 2.5.2 - 2015-12-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-math/pull/7) fixes how base
  conversions are accomplished within the bcmath adapter, ensuring PHP's native
  `base_convert()` is used for base36 and below, while continuing to use the
  base62 alphabet for anything above.
