# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.


## 2.7.1 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#31](https://github.com/zendframework/zend-captcha/pull/31) fixes using the
  ReCaptcha response as the value parameter to isValid().

## 2.7.0 - 2017-02-20

### Added

- [#29](https://github.com/zendframework/zend-captcha/pull/29) adds support for
  zend-recaptch v3.


### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.0 - 2016-06-21

### Added

- Adds and publishes documentation to https://zendframework.github.io/zend-captcha/
- [#20](https://github.com/zendframework/zend-captcha/pull/20) adds support for
  zend-math v3.

### Deprecated

- Nothing.

### Removed

- [#20](https://github.com/zendframework/zend-captcha/pull/20) removes support for
  PHP 5.5

### Fixed

- Nothing.

## 2.5.4 - 2016-02-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#18](https://github.com/zendframework/zend-captcha/pull/18) updates
  dependencies to known-stable, forwards-compatible versions.

## 2.5.3 - 2016-02-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/zendframework/zend-captcha/pull/6) ensures that `null`
  values may be passed for options.

## 2.5.2 - 2015-11-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- **ZF2015-09**: `Zend\Captcha\Word` generates a "word" for a CAPTCHA challenge
  by selecting a sequence of random letters from a character set. Prior to this
  vulnerability announcement, the selection was performed using PHP's internal
  `array_rand()` function. This function does not generate sufficient entropy
  due to its usage of `rand()` instead of more cryptographically secure methods
  such as `openssl_pseudo_random_bytes()`. This could potentially lead to
  information disclosure should an attacker be able to brute force the random
  number generation. This release contains a patch that replaces the
  `array_rand()` calls to use `Zend\Math\Rand::getInteger()`, which provides
  better RNG.

## 2.4.9 - 2015-11-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- **ZF2015-09**: `Zend\Captcha\Word` generates a "word" for a CAPTCHA challenge
  by selecting a sequence of random letters from a character set. Prior to this
  vulnerability announcement, the selection was performed using PHP's internal
  `array_rand()` function. This function does not generate sufficient entropy
  due to its usage of `rand()` instead of more cryptographically secure methods
  such as `openssl_pseudo_random_bytes()`. This could potentially lead to
  information disclosure should an attacker be able to brute force the random
  number generation. This release contains a patch that replaces the
  `array_rand()` calls to use `Zend\Math\Rand::getInteger()`, which provides
  better RNG.
