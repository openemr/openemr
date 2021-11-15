# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.14.4 - 2021-01-24


-----

### Release Notes for [2.14.4](https://github.com/laminas/laminas-validator/milestone/11)

2.14.x bugfix release (patch)

### 2.14.4

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [91: Fix immutability issue when validating a `DateTimeImmutable` instance with the `DateStep` validator](https://github.com/laminas/laminas-validator/pull/91) thanks to @netiul

## 2.14.3 - 2021-01-24


-----

### Release Notes for [2.14.3](https://github.com/laminas/laminas-validator/milestone/10)

2.14.x bugfix release (patch)

### 2.14.3

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [89: Fix Type Error when validating a `DateTimeImmutable` instance to the `DateStep` validator](https://github.com/laminas/laminas-validator/pull/89) thanks to @netiul

## 2.14.2 - 2021-01-22


-----

### Release Notes for [2.14.2](https://github.com/laminas/laminas-validator/milestone/9)

2.14.x bugfix release (patch)

### 2.14.2

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [87: Removed hardcoded `version` field from `composer.json`](https://github.com/laminas/laminas-validator/pull/87) thanks to @Ocramius

## 2.14.1 - 2021-01-13

### Fixed

- [#83](https://github.com/laminas/laminas-validator/pull/83) Fixes regression regarding boolean `strict` option in `InArray` validator


-----

### Release Notes for [2.14.1](https://github.com/laminas/laminas-validator/milestone/7)

2.14.x bugfix release (patch)

### 2.14.1

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### BC Break,Bug

 - [83: Fix regression regarding InArray validator with boolean strict options](https://github.com/laminas/laminas-validator/pull/83) thanks to @boesing and @cvigorsICBF

## 2.14.0 - 2021-01-07

### Added

- [#58](https://github.com/laminas/laminas-validator/pull/58) adds a `Laminas\Validator\BusinessIdentifierCode` validator, which can be used for validating SWIFT business identifier codes.

- [#75](https://github.com/laminas/laminas-validator/pull/75) adds support for PHP 8.0.

### Changed

- [#75](https://github.com/laminas/laminas-validator/pull/75) With the update to support PHP 8, please be aware that the `InArray` validator may work differently than under PHP 7 when doing non-strict comparisons involving numbers and strings. Under PHP versions prior to v8, strings would be cast to numbers before comparing to a numeric value; now, the opposite occurs, which means that while `"b" == 0` was true in PHP 7, it no longer is in PHP 8. We recommend only using the `InArray` validator with strict comparisons enabled to prevent issues moving from PHP 7 to PHP 8.

### Removed

- [#75](https://github.com/laminas/laminas-validator/pull/75) removes support for PHP versions 7.1 and 7.2.


-----

### Release Notes for [2.14.0](https://github.com/laminas/laminas-validator/milestone/1)

next feature release (minor)

### 2.14.0

- Total issues resolved: **0**
- Total pull requests resolved: **7**
- Total contributors: **6**

#### Enhancement

 - [77: add heic to valid isImage mime checks](https://github.com/laminas/laminas-validator/pull/77) thanks to @christaggart
 - [63: qa: Replace array by data providers in unit tests for the hostname validator](https://github.com/laminas/laminas-validator/pull/63) thanks to @codisart
 - [58: feat: Add the support for SWIFT Business Identifier Code (BIC) format](https://github.com/laminas/laminas-validator/pull/58) thanks to @codisart

#### Enhancement,hacktoberfest-accepted

 - [76: Add vimeo/psalm](https://github.com/laminas/laminas-validator/pull/76) thanks to @lukaslangen
 - [75: PHP 8.0 support](https://github.com/laminas/laminas-validator/pull/75) thanks to @jfernancordova

#### Bug,Documentation

 - [72: Fixed parameter types](https://github.com/laminas/laminas-validator/pull/72) thanks to @svycka

#### Documentation,Enhancement

 - [71: Fix php-fig.org &quot;PSR-7 UploadedFileInterface&quot; link](https://github.com/laminas/laminas-validator/pull/71) thanks to @jbelien

## 2.13.5 - 2021-01-06

### Release Notes for [2.13.5](https://github.com/laminas/laminas-validator/milestone/4)

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **2**

#### Bug

- [68: Remove duplicate keys in ValidatorPluginManager::factories](https://github.com/laminas/laminas-validator/pull/68) thanks to @samsonasik
- [67: Possible fixes #8 compilation failed regex too large](https://github.com/laminas/laminas-validator/pull/67) thanks to @samsonasik
- [62: qa: Replace array by data providers in unit tests](https://github.com/laminas/laminas-validator/pull/62) thanks to @codisart

## 2.13.4 - 2020-03-31

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#59](https://github.com/laminas/laminas-validator/pull/59) fixes `Uri` validator to accept any `Laminas\Uri\Uri` instance for the uri handler.

## 2.13.3 - 2020-03-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed `replace` version constraint in composer.json so repository can be used as replacement of `zendframework/zend-validator:^2.13.0`.

## 2.13.2 - 2020-03-16

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#57](https://github.com/laminas/laminas-validator/pull/57) removes redundant third argument in `UndisclosedPassword` validator constructor.

- [#53](https://github.com/laminas/laminas-validator/pull/53) fixes `UndisclosedPassword` validator to call parent constructor on instantiation. 

## 2.13.1 - 2020-01-15

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#32](https://github.com/laminas/laminas-validator/pull/32) fixes PHP 7.4 compatibility.

- [#34](https://github.com/laminas/laminas-validator/pull/34) fixes hostname validation for domain parts with 2+ dashes and with dash at the end.

- Updates the TLD list to the latest version from the IANA.

## 2.13.0 - 2019-12-27

### Added

- [zendframework/zend-validator#275](https://github.com/zendframework/zend-validator/pull/275) adds a new `strict` option to `Laminas\Validator\Date`; when `true`, the value being validated must both be a date AND in the same format as provided via the `format` option.

- [zendframework/zend-validator#264](https://github.com/zendframework/zend-validator/pull/264) adds `Laminas\Validator\UndisclosedPassword`, which can be used to determine if a password has been exposed in a known data breach as reported on the [Have I Been Pwned?](https://www.haveibeenpwned.com) website. [Documentation](https://docs.laminas.dev/laminas-validator/validators/undisclosed-password/)

- [zendframework/zend-validator#266](https://github.com/zendframework/zend-validator/pull/266) adds a new option to the `File\Extension` and `File\ExcludeExtension` validators, `allowNonExistentFile`. When set to `true`, the validators will continue validating the extension of the filename given even if the file does not exist. The default is `false`, to preserve backwards compatibility with previous versions.

### Changed

- [zendframework/zend-validator#264](https://github.com/zendframework/zend-validator/pull/264) bumps the minimum supported PHP version to 7.1.0.

- [zendframework/zend-validator#279](https://github.com/zendframework/zend-validator/pull/279) updates the `magic.mime` file used for file validations.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-validator#264](https://github.com/zendframework/zend-validator/pull/264) removes support for PHP versions prior to 7.1.0.

### Fixed

- Nothing.

## 2.12.2 - 2019-10-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#277](https://github.com/zendframework/zend-validator/pull/277) fixes `File\Hash` validator in case
  when the file hash contains only digits.

- [zendframework/zend-validator#277](https://github.com/zendframework/zend-validator/pull/277) fixes `File\Hash` validator to match 
  hash with the given hashing algorithm.

## 2.12.1 - 2019-10-12

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#272](https://github.com/zendframework/zend-validator/pull/272) changes
  curly braces in array and string offset access to square brackets
  in order to prevent issues under the upcoming PHP 7.4 release.

- [zendframework/zend-validator#231](https://github.com/zendframework/zend-validator/pull/231) fixes validation of input hashes in `Laminas\Validator\File\Hash` validator when provided as array.
  Only string hashes are allowed. If different type is provided `Laminas\Validator\Exception\InvalidArgumentException` is thrown.

## 2.12.0 - 2019-01-30

### Added

- [zendframework/zend-validator#250](https://github.com/zendframework/zend-validator/pull/250) adds support for PHP 7.3.

### Changed

- [zendframework/zend-validator#251](https://github.com/zendframework/zend-validator/pull/251) updates the logic of each of the various `Laminas\Validator\File` validators
  to allow validating against PSR-7 `UploadedFileInterface` instances, expanding
  the support originally provided in version 2.11.0.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-validator#250](https://github.com/zendframework/zend-validator/pull/250) removes support for laminas-stdlib v2 releases.

### Fixed

- Nothing.

## 2.11.1 - 2019-01-29

### Added

- [zendframework/zend-validator#249](https://github.com/zendframework/zend-validator/pull/249) adds support in the hostname validator for the `.rs` TLD.

### Changed

- [zendframework/zend-validator#253](https://github.com/zendframework/zend-validator/pull/253) updates the list of allowed characters for a `DE` domain name to match those published by IDN.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#256](https://github.com/zendframework/zend-validator/pull/256) fixes hostname validation when omitting the TLD from verification,
  ensuring validation of the domain segment considers all URI criteria.

## 2.11.0 - 2018-12-13

### Added

- [zendframework/zend-validator#237](https://github.com/zendframework/zend-validator/pull/237) adds support for the [PSR-7 UploadedFileInterface](https://www.php-fig.org/psr/psr-7/#uploadedfileinterface)
  to each of the `Upload` and `UploadFile` validators.

- [zendframework/zend-validator#220](https://github.com/zendframework/zend-validator/pull/220) adds image/webp to the list of known image types for the `IsImage` validator.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.10.3 - 2018-12-13

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#241](https://github.com/zendframework/zend-validator/pull/241) has the `Hostname` validator return an invalid result early when an empty
  domain segment is detected.

- [zendframework/zend-validator#232](https://github.com/zendframework/zend-validator/pull/232) updates the `Hostname` validator to allow underscores in subdomains.

- [zendframework/zend-validator#218](https://github.com/zendframework/zend-validator/pull/218) fixes a precision issue with the `Step` validator.

## 2.10.2 - 2018-02-01

### Added

- [zendframework/zend-validator#202](https://github.com/zendframework/zend-validator/pull/202) adds the
  ability to use custom constant types in extensions of
  `Laminas\Validator\CreditCard`, fixing an issue where users were unable to add
  new brands as they are created.

- [zendframework/zend-validator#203](https://github.com/zendframework/zend-validator/pull/203) adds support
  for the new Russian bank card "Mir".

- [zendframework/zend-validator#204](https://github.com/zendframework/zend-validator/pull/204) adds support
  to the IBAN validator for performing SEPA validation against Croatia and San
  Marino.

- [zendframework/zend-validator#209](https://github.com/zendframework/zend-validator/pull/209) adds
  documentation for the `Explode` validator.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#195](https://github.com/zendframework/zend-validator/pull/195) adds
  missing `GpsPoint` validator entries to the `ValidatorPluginManager`, ensuring
  they may be retrieved from it correctly.

- [zendframework/zend-validator#212](https://github.com/zendframework/zend-validator/pull/212) updates the
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

- [zendframework/zend-validator#194](https://github.com/zendframework/zend-validator/pull/194) modifies the
  `EmailAddress` validator to omit the `INTL_IDNA_VARIANT_UTS46` flag to
  `idn_to_utf8()` if the constant is not defined, fixing an issue on systems
  using pre-2012 releases of libicu.

## 2.10.0 - 2017-08-14

### Added

- [zendframework/zend-validator#175](https://github.com/zendframework/zend-validator/pull/175) adds support
  for PHP 7.2 (conditionally, as PHP 7.2 is currently in beta1).

- [zendframework/zend-validator#157](https://github.com/zendframework/zend-validator/pull/157) adds a new
  validator, `IsCountable`, which allows validating:
  - if a value is countable
  - if a countable value exactly matches a configured count
  - if a countable value is greater than a configured minimum count
  - if a countable value is less than a configured maximum count
  - if a countable value is between configured minimum and maximum counts

### Changed

- [zendframework/zend-validator#169](https://github.com/zendframework/zend-validator/pull/169) modifies how
  the various `File` validators check for readable files. Previously, they used
  `stream_resolve_include_path`, which led to false negative checks when the
  files did not exist within an `include_path` (which is often the case within a
  web application). These now use `is_readable()` instead.

- [zendframework/zend-validator#185](https://github.com/zendframework/zend-validator/pull/185) updates the
  laminas-session requirement (during development, and in the suggestions) to 2.8+,
  to ensure compatibility with the upcoming PHP 7.2 release.

- [zendframework/zend-validator#187](https://github.com/zendframework/zend-validator/pull/187) updates the
  `Between` validator to **require** that both a `min` and a `max` value are
  provided to the constructor, and that both are of the same type (both
  integer/float values and/or both string values). This fixes issues that could
  previously occur when one or the other was not set, but means an exception
  will now be raised during instantiation (versus runtime during `isValid()`).

- [zendframework/zend-validator#188](https://github.com/zendframework/zend-validator/pull/188) updates the
  `ConfigProvider` to alias the service name `ValidatorManager` to the class
  `Laminas\Validator\ValidatorPluginManager`, and now maps the the latter class to
  the `ValidatorPluginManagerFactory`. Previously, we mapped the service name
  directly to the factory. Usage should not change for anybody at this point.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-validator#175](https://github.com/zendframework/zend-validator/pull/175) removes
  support for HHVM.

### Fixed

- [zendframework/zend-validator#160](https://github.com/zendframework/zend-validator/pull/160) fixes how the
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

- [zendframework/zend-validator#180](https://github.com/zendframework/zend-validator/pull/180) fixes how
  `Laminas\Validator\File\MimeType` "closes" the open FileInfo handle for the file
  being validated, using `unset()` instead of `finfo_close()`; this resolves a
  segfault that occurs on older PHP versions.
- [zendframework/zend-validator#174](https://github.com/zendframework/zend-validator/pull/174) fixes how
  `Laminas\Validator\Between` handles two situations: (1) when a non-numeric value
  is validated against numeric min/max values, and (2) when a numeric value is
  validated against non-numeric min/max values. Previously, these incorrectly
  validated as true; now they are marked invalid.

## 2.9.1 - 2017-05-17

### Added

- Nothing.

### Changes

- [zendframework/zend-validator#154](https://github.com/zendframework/zend-validator/pull/154) updates the
  `CreditCard` validator to allow 19 digit Discover card values, and 13 and 19
  digit Visa card values, which are now allowed (see
  https://en.wikipedia.org/wiki/Payment_card_number).
- [zendframework/zend-validator#162](https://github.com/zendframework/zend-validator/pull/162) updates the
  `Hostname` validator to support `.hr` (Croatia) IDN domains.
- [zendframework/zend-validator#163](https://github.com/zendframework/zend-validator/pull/163) updates the
  `Iban` validator to support Belarus.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#168](https://github.com/zendframework/zend-validator/pull/168) fixes how the
  `ValidatorPluginManagerFactory` factory initializes the plugin manager instance,
  ensuring it is injecting the relevant configuration from the `config` service
  and thus seeding it with configured validator services. This means
  that the `validators` configuration will now be honored in non-laminas-mvc contexts.

## 2.9.0 - 2017-03-17

### Added

- [zendframework/zend-validator#78](https://github.com/zendframework/zend-validator/pull/78) added
  `%length%` as an optional message variable in StringLength validator

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-validator#151](https://github.com/zendframework/zend-validator/pull/151) dropped
  php 5.5 support

### Fixed

- [zendframework/zend-validator#147](https://github.com/zendframework/zend-validator/issues/147)
  [zendframework/zend-validator#148](https://github.com/zendframework/zend-validator/pull/148) adds further
  `"suggest"` clauses in `composer.json`, since some dependencies are not always
  required, and may lead to runtime failures.
- [zendframework/zend-validator#66](https://github.com/zendframework/zend-validator/pull/66) fixed
  EmailAddress validator applying IDNA conversion to local part 
- [zendframework/zend-validator#88](https://github.com/zendframework/zend-validator/pull/88) fixed NotEmpty
  validator incorrectly applying types bitmaps
- [zendframework/zend-validator#150](https://github.com/zendframework/zend-validator/pull/150) fixed Hostname
  validator not allowing some characters in .dk IDN

## 2.8.2 - 2017-01-29

### Added

- [zendframework/zend-validator#110](https://github.com/zendframework/zend-validator/pull/110) adds new
  Mastercard 2-series BINs

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#81](https://github.com/zendframework/zend-validator/pull/81) registers the
  Uuid validator into ValidatorPluginManager.

## 2.8.1 - 2016-06-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#92](https://github.com/zendframework/zend-validator/pull/92) adds message
  templates to the `ExcludeMimeType` validator, to allow differentiating
  validation error messages from the `MimeType` validator.

## 2.8.0 - 2016-05-16

### Added

- [zendframework/zend-validator#58](https://github.com/zendframework/zend-validator/pull/58) adds a new
  `Uuid` validator, capable of validating if Versions 1-5 UUIDs are well-formed.
- [zendframework/zend-validator#64](https://github.com/zendframework/zend-validator/pull/64) ports
  `Laminas\ModuleManager\Feature\ValidatorProviderInterface` to
  `Laminas\Validator\ValidatorProviderInterface`, and updates the `Module::init()`
  to typehint against the new interface instead of the one from
  laminas-modulemanager. Applications targeting laminas-mvc v3 can start updating
  their code to implement the new interface, or simply duck-type against it.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.3 - 2016-05-16

### Added

- [zendframework/zend-validator#67](https://github.com/zendframework/zend-validator/pull/67) adds support
  for Punycoded top-level domains in the `Hostname` validator.
- [zendframework/zend-validator#79](https://github.com/zendframework/zend-validator/pull/79) adds and
  publishes the documentation to https://docs.laminas.dev/laminas-validator/

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

- [zendframework/zend-validator#65](https://github.com/zendframework/zend-validator/pull/65) fixes the
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

- [zendframework/zend-validator#63](https://github.com/zendframework/zend-validator/pull/63) exposes the
  package as a Laminas component and/or generic configuration provider, by adding the
  following:
  - `ValidatorPluginManagerFactory`, which can be consumed by container-interop /
    laminas-servicemanager to create and return a `ValidatorPluginManager` instance.
  - `ConfigProvider`, which maps the service `ValidatorManager` to the above
    factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    laminas-mvc applications. It also provices a specification to
    `Laminas\ModuleManager\Listener\ServiceListener` to allow modules to provide
    validator configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.0 - 2016-02-17

### Added

- [zendframework/zend-validator#18](https://github.com/zendframework/zend-validator/pull/18) adds a `GpsPoint`
  validator for validating GPS coordinates.
- [zendframework/zend-validator#47](https://github.com/zendframework/zend-validator/pull/47) adds two new
  classes, `Laminas\Validator\Isbn\Isbn10` and `Isbn13`; these classes are the
  result of an extract class refactoring, and contain the logic specific to
  calcualting the checksum for each ISBN style. `Laminas\Validator\Isbn` now
  instantiates the appropriate one and invokes it.
- [zendframework/zend-validator#46](https://github.com/zendframework/zend-validator/pull/46) updates
  `Laminas\Validator\Db\AbstractDb` to implement `Laminas\Db\Adapter\AdapterAwareInterface`,
  by composing `Laminas\Db\Adapter\AdapterAwareTrait`.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-validator#55](https://github.com/zendframework/zend-validator/pull/55) removes some
  checks for `safe_mode` within the `MimeType` validator, as `safe_mode` became
  obsolete starting with PHP 5.4.

### Fixed

- [zendframework/zend-validator#45](https://github.com/zendframework/zend-validator/pull/45) fixes aliases
  mapping the deprecated `Float` and `Int` validators to their `Is*` counterparts.
- [zendframework/zend-validator#49](https://github.com/zendframework/zend-validator/pull/49)
  [zendframework/zend-validator#50](https://github.com/zendframework/zend-validator/pull/50), and
  [zendframework/zend-validator#51](https://github.com/zendframework/zend-validator/pull/51) update the
  code to be forwards-compatible with laminas-servicemanager and laminas-stdlib v3.
- [zendframework/zend-validator#56](https://github.com/zendframework/zend-validator/pull/56) fixes the regex
  in the `Ip` validator to escape `.` characters used as IP delimiters.

## 2.5.4 - 2016-02-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#44](https://github.com/zendframework/zend-validator/pull/44) corrects the
  grammar on the `NOT_GREATER_INCLUSIVE` validation error message.
- [zendframework/zend-validator#45](https://github.com/zendframework/zend-validator/pull/45) adds normalized
  aliases for the i18n isfloat/isint validators.
- Updates the hostname validator regexes per the canonical service on which they
  are based.
- [zendframework/zend-validator#52](https://github.com/zendframework/zend-validator/pull/52) updates the
  `Barcode` validator to cast empty options passed to the constructor to an
  empty array, fixing type mismatch errors.
- [zendframework/zend-validator#54](https://github.com/zendframework/zend-validator/pull/54) fixes the IP
  address detection in the `Hostname` validator to ensure that IPv6 is detected
  correctly.
- [zendframework/zend-validator#56](https://github.com/zendframework/zend-validator/pull/56) updates the
  regexes used by the `IP` validator when comparing ipv4 addresses to ensure a
  literal `.` is tested between network segments.

## 2.5.3 - 2015-09-03

### Added

- [zendframework/zend-validator#30](https://github.com/zendframework/zend-validator/pull/30) adds tooling to
  ensure that the Hostname TLD list stays up-to-date as changes are pushed for
  the repository.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#17](https://github.com/zendframework/zend-validator/pull/17) and
  [zendframework/zend-validator#29](https://github.com/zendframework/zend-validator/pull/29) provide more
  test coverage, and fix a number of edge cases, primarily in validator option
  verifications.
- [zendframework/zend-validator#26](https://github.com/zendframework/zend-validator/pull/26) fixes tests for
  `StaticValidator` such that they make correct assertions now. In doing so, we
  determined that it was possible to pass an indexed array of options, which
  could lead to unexpected results, often leading to false positives when
  validating. To correct this situation, `StaticValidator::execute()` now raises
  an `InvalidArgumentException` when an indexed array is detected for the
  `$options` argument.
- [zendframework/zend-validator#35](https://github.com/zendframework/zend-validator/pull/35) modifies the
  `NotEmpty` validator to no longer treat the float `0.0` as an empty value for
  purposes of validation.
- [zendframework/zend-validator#25](https://github.com/zendframework/zend-validator/pull/25) fixes the
  `Date` validator to check against `DateTimeImmutable` and not
  `DateTimeInterface` (as PHP has restrictions currently on how the latter can
  be used).

## 2.5.2 - 2015-07-16

### Added

- [zendframework/zend-validator#8](https://github.com/zendframework/zend-validator/pull/8) adds a "strict"
  configuration option; when enabled (the default), the length of the address is
  checked to ensure it follows the specification.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-validator#8](https://github.com/zendframework/zend-validator/pull/8) fixes bad
  behavior on the part of the `idn_to_utf8()` function, returning the original
  address in the case that the function fails.
- [zendframework/zend-validator#11](https://github.com/zendframework/zend-validator/pull/11) fixes
  `ValidatorChain::prependValidator()` so that it works on HHVM.
- [zendframework/zend-validator#12](https://github.com/zendframework/zend-validator/pull/12) adds "6772" to
  the Maestro range of the `CreditCard` validator.
