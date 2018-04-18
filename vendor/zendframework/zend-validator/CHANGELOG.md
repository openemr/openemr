# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.10.2 - 2018-02-01

### Added

- [#202](https://github.com/zendframework/zend-validator/pull/202) adds the
  ability to use custom constant types in extensions of
  `Zend\Validator\CreditCard`, fixing an issue where users were unable to add
  new brands as they are created.

- [#203](https://github.com/zendframework/zend-validator/pull/203) adds support
  for the new Russian bank card "Mir".

- [#204](https://github.com/zendframework/zend-validator/pull/204) adds support
  to the IBAN validator for performing SEPA validation against Croatia and San
  Marino.

- [#209](https://github.com/zendframework/zend-validator/pull/209) adds
  documentation for the `Explode` validator.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#195](https://github.com/zendframework/zend-validator/pull/195) adds
  missing `GpsPoint` validator entries to the `ValidatorPluginManager`, ensuring
  they may be retrieved from it correctly.

- [#212](https://github.com/zendframework/zend-validator/pull/212) updates the
  `CSRF` validator to automatically mark any non-string values as invalid,
  preventing errors such as array to string conversion.

## 2.10.1 - 2017-08-22

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#194](https://github.com/zendframework/zend-validator/pull/194) modifies the
  `EmailAddress` validator to omit the `INTL_IDNA_VARIANT_UTS46` flag to
  `idn_to_utf8()` if the constant is not defined, fixing an issue on systems
  using pre-2012 releases of libicu.

## 2.10.0 - 2017-08-14

### Added

- [#175](https://github.com/zendframework/zend-validator/pull/175) adds support
  for PHP 7.2 (conditionally, as PHP 7.2 is currently in beta1).

- [#157](https://github.com/zendframework/zend-validator/pull/157) adds a new
  validator, `IsCountable`, which allows validating:
  - if a value is countable
  - if a countable value exactly matches a configured count
  - if a countable value is greater than a configured minimum count
  - if a countable value is less than a configured maximum count
  - if a countable value is between configured minimum and maximum counts

### Changed

- [#169](https://github.com/zendframework/zend-validator/pull/169) modifies how
  the various `File` validators check for readable files. Previously, they used
  `stream_resolve_include_path`, which led to false negative checks when the
  files did not exist within an `include_path` (which is often the case within a
  web application). These now use `is_readable()` instead.

- [#185](https://github.com/zendframework/zend-validator/pull/185) updates the
  zend-session requirement (during development, and in the suggestions) to 2.8+,
  to ensure compatibility with the upcoming PHP 7.2 release.

- [#187](https://github.com/zendframework/zend-validator/pull/187) updates the
  `Between` validator to **require** that both a `min` and a `max` value are
  provided to the constructor, and that both are of the same type (both
  integer/float values and/or both string values). This fixes issues that could
  previously occur when one or the other was not set, but means an exception
  will now be raised during instantiation (versus runtime during `isValid()`).

- [#188](https://github.com/zendframework/zend-validator/pull/188) updates the
  `ConfigProvider` to alias the service name `ValidatorManager` to the class
  `Zend\Validator\ValidatorPluginManager`, and now maps the the latter class to
  the `ValidatorPluginManagerFactory`. Previously, we mapped the service name
  directly to the factory. Usage should not change for anybody at this point.

### Deprecated

- Nothing.

### Removed

- [#175](https://github.com/zendframework/zend-validator/pull/175) removes
  support for HHVM.

### Fixed

- [#160](https://github.com/zendframework/zend-validator/pull/160) fixes how the
  `EmailAddress` validator handles the local part of an address, allowing it to
  support unicode.

## 2.9.2 - 2017-07-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#180](https://github.com/zendframework/zend-validator/pull/180) fixes how
  `Zend\Validator\File\MimeType` "closes" the open FileInfo handle for the file
  being validated, using `unset()` instead of `finfo_close()`; this resolves a
  segfault that occurs on older PHP versions.
- [#174](https://github.com/zendframework/zend-validator/pull/174) fixes how
  `Zend\Validator\Between` handles two situations: (1) when a non-numeric value
  is validated against numeric min/max values, and (2) when a numeric value is
  validated against non-numeric min/max values. Previously, these incorrectly
  validated as true; now they are marked invalid.

## 2.9.1 - 2017-05-17

### Added

- Nothing.

### Changes

- [#154](https://github.com/zendframework/zend-validator/pull/154) updates the
  `CreditCard` validator to allow 19 digit Discover card values, and 13 and 19
  digit Visa card values, which are now allowed (see
  https://en.wikipedia.org/wiki/Payment_card_number).
- [#162](https://github.com/zendframework/zend-validator/pull/162) updates the
  `Hostname` validator to support `.hr` (Croatia) IDN domains.
- [#163](https://github.com/zendframework/zend-validator/pull/163) updates the
  `Iban` validator to support Belarus.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#168](https://github.com/zendframework/zend-validator/pull/168) fixes how the
  `ValidatorPluginManagerFactory` factory initializes the plugin manager instance,
  ensuring it is injecting the relevant configuration from the `config` service
  and thus seeding it with configured validator services. This means
  that the `validators` configuration will now be honored in non-zend-mvc contexts.

## 2.9.0 - 2017-03-17

### Added

- [#78](https://github.com/zendframework/zend-validator/pull/78) added
  `%length%` as an optional message variable in StringLength validator

### Deprecated

- Nothing.

### Removed

- [#151](https://github.com/zendframework/zend-validator/pull/151) dropped
  php 5.5 support

### Fixed

- [#147](https://github.com/zendframework/zend-validator/issues/147)
  [#148](https://github.com/zendframework/zend-validator/pull/148) adds further
  `"suggest"` clauses in `composer.json`, since some dependencies are not always
  required, and may lead to runtime failures.
- [#66](https://github.com/zendframework/zend-validator/pull/66) fixed
  EmailAddress validator applying IDNA conversion to local part 
- [#88](https://github.com/zendframework/zend-validator/pull/88) fixed NotEmpty
  validator incorrectly applying types bitmaps
- [#150](https://github.com/zendframework/zend-validator/pull/150) fixed Hostname
  validator not allowing some characters in .dk IDN

## 2.8.2 - 2017-01-29

### Added

- [#110](https://github.com/zendframework/zend-validator/pull/110) adds new
  Mastercard 2-series BINs

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#81](https://github.com/zendframework/zend-validator/pull/81) registers the
  Uuid validator into ValidatorPluginManager.

## 2.8.1 - 2016-06-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#92](https://github.com/zendframework/zend-validator/pull/92) adds message
  templates to the `ExcludeMimeType` validator, to allow differentiating
  validation error messages from the `MimeType` validator.

## 2.8.0 - 2016-05-16

### Added

- [#58](https://github.com/zendframework/zend-validator/pull/58) adds a new
  `Uuid` validator, capable of validating if Versions 1-5 UUIDs are well-formed.
- [#64](https://github.com/zendframework/zend-validator/pull/64) ports
  `Zend\ModuleManager\Feature\ValidatorProviderInterface` to
  `Zend\Validator\ValidatorProviderInterface`, and updates the `Module::init()`
  to typehint against the new interface instead of the one from
  zend-modulemanager. Applications targeting zend-mvc v3 can start updating
  their code to implement the new interface, or simply duck-type against it.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.3 - 2016-05-16

### Added

- [#67](https://github.com/zendframework/zend-validator/pull/67) adds support
  for Punycoded top-level domains in the `Hostname` validator.
- [#79](https://github.com/zendframework/zend-validator/pull/79) adds and
  publishes the documentation to https://zendframework.github.io/zend-validator/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.2 - 2016-04-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#65](https://github.com/zendframework/zend-validator/pull/65) fixes the
  `Module::init()` method to properly receive a `ModuleManager` instance, and
  not expect a `ModuleEvent`.

## 2.7.1 - 2016-04-06

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- This release updates the TLD list to the latest version from the IANA.

## 2.7.0 - 2016-04-06

### Added

- [#63](https://github.com/zendframework/zend-validator/pull/63) exposes the
  package as a ZF component and/or generic configuration provider, by adding the
  following:
  - `ValidatorPluginManagerFactory`, which can be consumed by container-interop /
    zend-servicemanager to create and return a `ValidatorPluginManager` instance.
  - `ConfigProvider`, which maps the service `ValidatorManager` to the above
    factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    zend-mvc applications. It also provices a specification to
    `Zend\ModuleManager\Listener\ServiceListener` to allow modules to provide
    validator configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.0 - 2016-02-17

### Added

- [#18](https://github.com/zendframework/zend-validator/pull/18) adds a `GpsPoint`
  validator for validating GPS coordinates.
- [#47](https://github.com/zendframework/zend-validator/pull/47) adds two new
  classes, `Zend\Validator\Isbn\Isbn10` and `Isbn13`; these classes are the
  result of an extract class refactoring, and contain the logic specific to
  calcualting the checksum for each ISBN style. `Zend\Validator\Isbn` now
  instantiates the appropriate one and invokes it.
- [#46](https://github.com/zendframework/zend-validator/pull/46) updates
  `Zend\Validator\Db\AbstractDb` to implement `Zend\Db\Adapter\AdapterAwareInterface`,
  by composing `Zend\Db\Adapter\AdapterAwareTrait`.

### Deprecated

- Nothing.

### Removed

- [#55](https://github.com/zendframework/zend-validator/pull/55) removes some
  checks for `safe_mode` within the `MimeType` validator, as `safe_mode` became
  obsolete starting with PHP 5.4.

### Fixed

- [#45](https://github.com/zendframework/zend-validator/pull/45) fixes aliases
  mapping the deprecated `Float` and `Int` validators to their `Is*` counterparts.
- [#49](https://github.com/zendframework/zend-validator/pull/49)
  [#50](https://github.com/zendframework/zend-validator/pull/50), and
  [#51](https://github.com/zendframework/zend-validator/pull/51) update the
  code to be forwards-compatible with zend-servicemanager and zend-stdlib v3.
- [#56](https://github.com/zendframework/zend-validator/pull/56) fixes the regex
  in the `Ip` validator to escape `.` characters used as IP delimiters.

## 2.5.4 - 2016-02-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#44](https://github.com/zendframework/zend-validator/pull/44) corrects the
  grammar on the `NOT_GREATER_INCLUSIVE` validation error message.
- [#45](https://github.com/zendframework/zend-validator/pull/45) adds normalized
  aliases for the i18n isfloat/isint validators.
- Updates the hostname validator regexes per the canonical service on which they
  are based.
- [#52](https://github.com/zendframework/zend-validator/pull/52) updates the
  `Barcode` validator to cast empty options passed to the constructor to an
  empty array, fixing type mismatch errors.
- [#54](https://github.com/zendframework/zend-validator/pull/54) fixes the IP
  address detection in the `Hostname` validator to ensure that IPv6 is detected
  correctly.
- [#56](https://github.com/zendframework/zend-validator/pull/56) updates the
  regexes used by the `IP` validator when comparing ipv4 addresses to ensure a
  literal `.` is tested between network segments.

## 2.5.3 - 2015-09-03

### Added

- [#30](https://github.com/zendframework/zend-validator/pull/30) adds tooling to
  ensure that the Hostname TLD list stays up-to-date as changes are pushed for
  the repository.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#17](https://github.com/zendframework/zend-validator/pull/17) and
  [#29](https://github.com/zendframework/zend-validator/pull/29) provide more
  test coverage, and fix a number of edge cases, primarily in validator option
  verifications.
- [#26](https://github.com/zendframework/zend-validator/pull/26) fixes tests for
  `StaticValidator` such that they make correct assertions now. In doing so, we
  determined that it was possible to pass an indexed array of options, which
  could lead to unexpected results, often leading to false positives when
  validating. To correct this situation, `StaticValidator::execute()` now raises
  an `InvalidArgumentException` when an indexed array is detected for the
  `$options` argument.
- [#35](https://github.com/zendframework/zend-validator/pull/35) modifies the
  `NotEmpty` validator to no longer treat the float `0.0` as an empty value for
  purposes of validation.
- [#25](https://github.com/zendframework/zend-validator/pull/25) fixes the
  `Date` validator to check against `DateTimeImmutable` and not
  `DateTimeInterface` (as PHP has restrictions currently on how the latter can
  be used).

## 2.5.2 - 2015-07-16

### Added

- [#8](https://github.com/zendframework/zend-validator/pull/8) adds a "strict"
  configuration option; when enabled (the default), the length of the address is
  checked to ensure it follows the specification.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#8](https://github.com/zendframework/zend-validator/pull/8) fixes bad
  behavior on the part of the `idn_to_utf8()` function, returning the original
  address in the case that the function fails.
- [#11](https://github.com/zendframework/zend-validator/pull/11) fixes
  `ValidatorChain::prependValidator()` so that it works on HHVM.
- [#12](https://github.com/zendframework/zend-validator/pull/12) adds "6772" to
  the Maestro range of the `CreditCard` validator.
