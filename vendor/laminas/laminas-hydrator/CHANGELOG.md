# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 4.1.0 - 2020-12-16


-----

### Release Notes for [4.1.0](https://github.com/laminas/laminas-hydrator/milestone/10)

Feature release (minor)

### 4.1.0

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Documentation Needed,Enhancement

 - [43: Add NullableStrategy](https://github.com/laminas/laminas-hydrator/pull/43) thanks to @eugene-borovov

#### Feature Request

 - [42: HydratorStrategy extract empty value](https://github.com/laminas/laminas-hydrator/issues/42) thanks to @eugene-borovov

## 4.0.2 - 2020-12-16

### Release Notes for [4.0.2](https://github.com/laminas/laminas-hydrator/milestone/12)

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

- [36: Fix example in quick-start.md](https://github.com/laminas/laminas-hydrator/pull/36) thanks to @vjik

## 4.0.1 - 2020-11-11

### Release Notes for [4.0.1](https://github.com/laminas/laminas-hydrator/milestone/9)

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### Documentation

- [39: Provide v4 documentation](https://github.com/laminas/laminas-hydrator/pull/39) thanks to @weierophinney and @rieschl

## 4.0.0 - 2020-10-06

### Changed

- [#30](https://github.com/laminas/laminas-hydrator/pull/30) modifies all `Laminas\Hydrator\Filter\FilterInterface` implementations shipped with the package, marking them as `final`. If you previously extended them, you will need to copy and paste the implementations, or open an issue requesting removal of the `final` keyword, detailing your use case.

- [#30](https://github.com/laminas/laminas-hydrator/pull/30) changes the signature of `Laminas\Hydrator\Filter\FilterInterface::filter()` to now accept a second, optional argument, `?object $instance = null`.  This argument's primary use case is with anonymous objects, to facilitate reflection; the `ClassMethodsHydrator`, for instance, was updated to pass the `$instance` value only when an anonymous object is detected.  All filter implementations have been updated to the new signature.

### Fixed

- [#30](https://github.com/laminas/laminas-hydrator/pull/30) fixes the filter system to allow usage with anonymous objects.


-----

### Release Notes for [4.0.0](https://github.com/laminas/laminas-hydrator/milestone/2)

next backward compatibility break release (major)

### 4.0.0

- Total issues resolved: **2**
- Total pull requests resolved: **2**
- Total contributors: **3**

#### Enhancement

 - [35: Improve code base per Psalm suggestions](https://github.com/laminas/laminas-hydrator/pull/35) thanks to @weierophinney

#### BC Break,Bug,Enhancement

 - [30: Allow anonymous object usage with hydrators](https://github.com/laminas/laminas-hydrator/pull/30) thanks to @weierophinney and @luiz-brandao-jr

#### Enhancement,hacktoberfest-accepted

 - [29: PHP 8.0 support](https://github.com/laminas/laminas-hydrator/issues/29) thanks to @boesing

## 3.2.0 - 2020-10-06

### Added

- [#32](https://github.com/laminas/laminas-hydrator/pull/32) adds support for PHP 8.

### Changed

- [#32](https://github.com/laminas/laminas-hydrator/pull/32) changes the minimum supported version of laminas-stdlib from 3.2 to 3.3.

### Removed

- [#32](https://github.com/laminas/laminas-hydrator/pull/32) removes support for PHP versions prior to 7.3.

-----

### Release Notes for [3.2.0](https://github.com/laminas/laminas-hydrator/milestone/4)

- Total issues resolved: **1**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Enhancement

 - [33: Adds Psalm integration](https://github.com/laminas/laminas-hydrator/pull/33) thanks to @weierophinney and @boesing
 - [32: Feature/php 8 support](https://github.com/laminas/laminas-hydrator/pull/32) thanks to @weierophinney

## 3.1.1 -2020-10-06

-----

### Release Notes for [3.1.1](https://github.com/laminas/laminas-hydrator/milestone/5)

- Total issues resolved: **0**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### Documentation

 - [27: Update map-naming-strategy.md](https://github.com/laminas/laminas-hydrator/pull/27) thanks to @xorock

#### Documentation,Enhancement,Review Needed

 - [24: Rework the Index and Quick Start guide](https://github.com/laminas/laminas-hydrator/pull/24) thanks to @settermjd

#### Bug,Documentation

 - [23: Fixed StrategyInterface::hydrate() docblock because it is not optional](https://github.com/laminas/laminas-hydrator/pull/23) thanks to @svycka

## 3.1.0 - 2020-07-14

### Added

- [#17](https://github.com/laminas/laminas-hydrator/pull/17) adds a new strategy, `DateTimeImmutableFormatterStrategy`, to provide bidirectional conversion between strings and `DateTimeImmutable` instances.

- [#16](https://github.com/laminas/laminas-hydrator/pull/16) adds a new strategy implementation, `Laminas\Hydrator\Strategy\Hydrator`. It can be used to hydrate nested objects and vice versa.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.3 - 2020-07-14

### Added

- [#17](https://github.com/laminas/laminas-hydrator/pull/17) adds a new strategy, `DateTimeImmutableFormatterStrategy`, to provide bidirectional conversion between strings and `DateTimeImmutable` instances.

- [#16](https://github.com/laminas/laminas-hydrator/pull/16) adds a new strategy implementation, `Laminas\Hydrator\Strategy\Hydrator`. It can be used to hydrate nested objects and vice versa.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#5](https://github.com/laminas/laminas-hydrator/pull/5) fixes an error that occurs in `Laminas\Hydrator\Filter\FilterComposite` when used under the Swoole extension.

## 3.0.2 - 2019-03-15

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-hydrator#103](https://github.com/zendframework/zend-hydrator/pull/103) restores the original behavior of the UnderscoreNamingStrategy with
  regards to how numeric characters are treated. In version 2, they were
  **never** used as word boundaries, while version 3.0 used them as word
  boundaries in very specific, but hard to predict, scenarios. This release
  restores the original behavior from version 2.

## 3.0.1 - 2019-01-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-hydrator#97](https://github.com/zendframework/zend-hydrator/pull/97) adds a missing `static` keyword to `Laminas\Hydrator\NamingStrategy\MapNamingStrategy::createFromAsymmetricMap`,
  and simultaneously fixes a mis-spelling of the method name (it incorrectly
  used two "s" characters previously, and only one "m" in "asymmetric"). As the
  method could not be invoked as documented previously, these changes are
  considered bugfixes and not BC breaks.

- [zendframework/zend-hydrator#96](https://github.com/zendframework/zend-hydrator/pull/96) fixes issue with integer keys in `ArraySerializableHydrator`. Keys are now
  cast to strings as we have strict type declaration in the library.

## 3.0.0 - 2018-12-10

### Added

- [zendframework/zend-hydrator#87](https://github.com/zendframework/zend-hydrator/pull/87) adds `Laminas\Hydrator\HydratorPluginManagerInterface` to allow
  type-hinting on plugin manager implementations. The interface simply extends
  the [PSR-11 ContainerInterface](https://www.php-fig.org/psr/psr-11/).

- [zendframework/zend-hydrator#87](https://github.com/zendframework/zend-hydrator/pull/87) adds `Laminas\Hydrator\StandaloneHydratorPluginManager` as an implementation
  of each of `Psr\Container\ContainerInterface` and `Laminas\Hydrator\HydratorPluginManagerInterface`,
  along with a factory for creating it, `Laminas\Hydrator\StandaloneHydratorPluginManagerFactory`.
  It can act as a replacement for `Laminas\Hydrator\HydratorPluginManager`, but
  only supports the shipped hydrator implementations. See the [plugin manager documentation](https://docs.laminas.dev/laminas-hydrator/v3/plugin-managers/)
  for more details on usage.

- [zendframework/zend-hydrator#79](https://github.com/zendframework/zend-hydrator/pull/79) adds a third, optional parameter to the `DateTimeFormatterStrategy` constructor.
  The parameter is a boolean, and, when enabled, a string that can be parsed by
  the `DateTime` constructor will still result in a `DateTime` instance during
  hydration, even if the string does not follow the provided date-time format.

- [zendframework/zend-hydrator#14](https://github.com/zendframework/zend-hydrator/pull/14) adds the following `final` classes:
  - `\Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy\UnderscoreToCamelCaseFilter`
  - `\Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy\CamelCaseToUnderscoreFilter`

### Changed

- [zendframework/zend-hydrator#89](https://github.com/zendframework/zend-hydrator/pull/89) renames the various hydrators to use the "Hydrator" suffix:
  - `ArraySerializable` becomes `ArraySerializableHydrator`
  - `ClassMethods` becomes `ClassMethodsHydrator`
  - `ObjectProperty` becomes `ObjectPropertyHydrator`
  - `Reflection` becomes `ReflectionHydrator`
  In each case, the original class was re-added to the repository as a
  deprecated extension of the new class, to be removed in version 4.0.0.

  Aliases resolving the original class name to the new class were also added to
  the `HydratorPluginManager` to ensure you can still obtain instances.

- [zendframework/zend-hydrator#87](https://github.com/zendframework/zend-hydrator/pull/87) modifies `Laminas\Hydrator\ConfigProvider` to add a factory entry for
  `Laminas\Hydrator\StandaloneHydratorPluginManager`.

- [zendframework/zend-hydrator#87](https://github.com/zendframework/zend-hydrator/pull/87) modifies `Laminas\Hydrator\ConfigProvider` to change the target of the
  `HydratorManager` alias based on the presence of the laminas-servicemanager
  package; if the package is not available, the target points to
  `Laminas\Hydrator\StandaloneHydratorPluginManager` instead of
  `Laminas\Hydrator\HydratorPluginManager`.

- [zendframework/zend-hydrator#83](https://github.com/zendframework/zend-hydrator/pull/83) renames `Laminas\Hydrator\FilterEnabledInterface` to `Laminas\Hydrator\Filter\FilterEnabledInterface` (new namespace).

- [zendframework/zend-hydrator#83](https://github.com/zendframework/zend-hydrator/pull/83) renames `Laminas\Hydrator\NamingStrategyEnabledInterface` to `Laminas\Hydrator\NamingStrategy\NamingStrategyEnabledInterface` (new namespace).

- [zendframework/zend-hydrator#83](https://github.com/zendframework/zend-hydrator/pull/83) renames `Laminas\Hydrator\StrategyEnabledInterface` to `Laminas\Hydrator\Strategy\StrategyEnabledInterface` (new namespace).

- [zendframework/zend-hydrator#82](https://github.com/zendframework/zend-hydrator/pull/82) and [zendframework/zend-hydrator#85](https://github.com/zendframework/zend-hydrator/pull/85) change `Laminas\Hydrator\NamingStrategy\MapNamingStrategy`
  in the following ways:
  - The class is now marked `final`.
  - The constructor is marked private. You can no longer instantiate it directly.
  - The class offers three new named constructors; one of these MUST be used to
    create an instance, as the constructor is now final:
    - `MapNamingStrategy::createFromExtractionMap(array $extractionMap) : MapNamingStrategy`
      will use the provided extraction map for extraction operations, and flip it
      for hydration operations.
    - `MapNamingStrategy::createFromHydrationMap(array $hydrationMap) : MapNamingStrategy`
      will use the provided hydration map for hydration operations, and flip it
      for extraction operations.
    - `MapNamingStrategy::createFromAssymetricMap(array $extractionMap, array $hydrationMap) : MapNamingStrategy`
      will use the appropriate map based on the requested operation.

- [zendframework/zend-hydrator#80](https://github.com/zendframework/zend-hydrator/pull/80) bumps the minimum supported PHP version to 7.2.

- [zendframework/zend-hydrator#80](https://github.com/zendframework/zend-hydrator/pull/80) bumps the minimum supported laminas-eventmanager version to 3.2.1. laminas-eventmanager
  is only required if you are using the `AggregateHydrator`.

- [zendframework/zend-hydrator#80](https://github.com/zendframework/zend-hydrator/pull/80) bumps the minimum supported laminas-serializer version to 2.9.0. laminas-serializer is
  only required if you are using the `SerializableStrategy`.

- [zendframework/zend-hydrator#80](https://github.com/zendframework/zend-hydrator/pull/80) bumps the minimum supported laminas-servicemanager version to 3.3.2.
  laminas-servicemanager is only required if you are using the
  `HydratorPluginManager` or `DelegatingHydrator`. This change means that
  some service names supported by laminas-servicemanager v2 will no longer work.
  When in doubt, use the fully qualified class name, or the class name minus the
  namespace, with correct casing.

- [zendframework/zend-hydrator#80](https://github.com/zendframework/zend-hydrator/pull/80) adds scalar typehints both to parameters and return values, and object
  typehints to parameters, wherever possible. For consumers, this should pose no
  discernable change. **For those implementing interfaces or extending classes
  from this package, updates will be necessary to ensure your code will run.**
  [See the migration guide for details](https://docs.laminas.dev/laminas-hydrator/v3/migration/).

- [zendframework/zend-hydrator#14](https://github.com/zendframework/zend-hydrator/pull/14) replaces usage of laminas-filter with the hardcoded filters referenced in
  the above section.

- [zendframework/zend-hydrator#14](https://github.com/zendframework/zend-hydrator/pull/14) made the following visibility changes to `\Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy`:
  - static property `$underscoreToStudlyCaseFilter` was renamed to `$underscoreToCamelCaseFilter` and marked `private`
  - static property `$camelCaseToUnderscoreFilter` was marked `private`
  - method `getCamelCaseToUnderscoreFilter` was marked `private`
  - method `getUnderscoreToStudlyCaseFilter` was renamed to `getUnderscoreToCamelCaseFilter` and marked `private`

### Deprecated

- [zendframework/zend-hydrator#89](https://github.com/zendframework/zend-hydrator/pull/89) and
  [zendframework/zend-hydrator#93](https://github.com/zendframework/zend-hydrator/pull/93) deprecate the
  following classes, which will be removed in 4.0.0:
  - `Laminas\Hydrator\ArraySerializable` (becomes `ArraySerializableHydrator`)
  - `Laminas\Hydrator\ClassMethods` (becomes `ClassMethodsHydrator`)
  - `Laminas\Hydrator\ObjectProperty` (becomes `ObjectPropertyHydrator`)
  - `Laminas\Hydrator\Reflection` (becomes `ReflectionHydrator`)

### Removed

- [zendframework/zend-hydrator#83](https://github.com/zendframework/zend-hydrator/pull/83) removes the constructor in `Laminas\Hydrator\AbstractHydrator`. All
  initialization is now either performed via property definitions or lazy-loading.

- [zendframework/zend-hydrator#82](https://github.com/zendframework/zend-hydrator/pull/82) removes `Laminas\Hydrator\NamingStrategy\ArrayMapNamingStrategy`. The functionality
  it provided has been merged into `Laminas\Hydrator\NamingStrategy\MapNamingStrategy`;
  use `MapNamingStrategy::createFromExtractionMap()` to create an instance that
  has the same functionality as `ArrayMapNamingStrategy` previously provided.

### Fixed

- Nothing.

## 2.4.1 - 2018-11-19

### Added

- Nothing.

### Changed

- [zendframework/zend-hydrator#69](https://github.com/zendframework/zend-hydrator/pull/69) adds support for special pre/post characters in formats passed to the
  `DateTimeFormatterStrategy`. When used, the `DateTime` instances created
  during hydration will (generally) omit the time element, allowing for more
  accurate comparisons.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.4.0 - 2018-04-30

### Added

- [zendframework/zend-hydrator#70](https://github.com/zendframework/zend-hydrator/pull/70) updates the `DateTimeFormatterStrategy` to work with any `DateTimeInterface`,
  and not just `DateTime`.

### Changed

- [zendframework/zend-hydrator#75](https://github.com/zendframework/zend-hydrator/pull/75) ensures continuous integration _requires_ PHP 7.2 tests to pass;
  they already were.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.3.1 - 2017-10-02

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-hydrator#67](https://github.com/zendframework/zend-hydrator/pull/67) fixes an issue
  in the `ArraySerializable::hydrate()` logic whereby nested array data was
  _merged_ instead of _replaced_ during hydration. The hydrator now correctly
  replaces such data.

## 2.3.0 - 2017-09-20

### Added

- [zendframework/zend-hydrator#27](https://github.com/zendframework/zend-hydrator/pull/27) adds the
  interface `Laminas\Hydrator\HydratorProviderInterface` for use with the
  laminas-modulemanager `ServiceListener` implementation, and updates the
  `HydratorManager` definition for the `ServiceListener` to typehint on this new
  interface instead of the one provided in laminas-modulemanager.

  Users implementing the laminas-modulemanager `Laminas\ModuleManger\Feature\HydratorProviderInterface`
  will be unaffected, as the method it defines, `getHydratorConfig()`, will
  still be identified and used to inject he `HydratorPluginManager`. However, we
  recommend updating your `Module` classes to use the new interface instead.

- [zendframework/zend-hydrator#44](https://github.com/zendframework/zend-hydrator/pull/44) adds
  `Laminas\Hydrator\Strategy\CollectionStrategy`. This class allows you to provide
  a single hydrator to use with an array of objects or data that represent the
  same type.

  From the patch, if the "users" property of an object you will hydrate is
  expected to be an array of items of a type `User`, you could do the following:

  ```php
  $hydrator->addStrategy('users', new CollectionStrategy(
      new ReflectionHydrator(),
      User::class
  ));
  ```

- [zendframework/zend-hydrator#63](https://github.com/zendframework/zend-hydrator/pull/63) adds support for
  PHP 7.2.

### Changed

- [zendframework/zend-hydrator#44](https://github.com/zendframework/zend-hydrator/pull/44) updates the
  `ClassMethods` hydrator to add a second, optional, boolean argument to the
  constructor, `$methodExistsCheck`, and a related method
  `setMethodExistsCheck()`. These allow you to specify a flag indicating whether
  or not the name of a property must directly map to a _defined_ method, versus
  one that may be called via `__call()`. The default value of the flag is
  `false`, which retains the previous behavior of not checking if the method is
  defined. Set the flag to `true` to make the check more strict.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-hydrator#63](https://github.com/zendframework/zend-hydrator/pull/63) removes support for HHVM.

### Fixed

- Nothing.

## 2.2.3 - 2017-09-20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-hydrator#65](https://github.com/zendframework/zend-hydrator/pull/65) fixes the
  hydration behavior of the `ArraySerializable` hydrator when using
  `exchangeArray()`. Previously, the method would clear any existing values from
  the instance, which is problematic when a partial update is provided as values
  not in the update would disappear. The class now pulls the original values,
  and recursively merges the replacement with those values.

## 2.2.2 - 2017-05-17

### Added

### Changes

- [zendframework/zend-hydrator#42](https://github.com/zendframework/zend-hydrator/pull/42) updates the
  `ConfigProvider::getDependencies()` method to map the `HydratorPluginManager`
  class to the `HydratorPluginManagerFactory` class, and make the
  `HydratorManager` service an alias to the fully-qualified
  `HydratorPluginManager` class.
- [zendframework/zend-hydrator#45](https://github.com/zendframework/zend-hydrator/pull/45) changes the
  `ClassMethods` hydrator to take into account naming strategies when present,
  making it act consistently with the other hydrators.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-hydrator#59](https://github.com/zendframework/zend-hydrator/pull/59) fixes how the
  `HydratorPluginManagerFactory` factory initializes the plugin manager
  instance, ensuring it is injecting the relevant configuration from the
  `config` service and thus seeding it with configured hydrator services. This
  means that the `hydrators` configuration will now be honored in non-laminas-mvc
  contexts.

## 2.2.1 - 2016-04-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-hydrator#28](https://github.com/zendframework/zend-hydrator/pull/28) fixes the
  `Module::init()` method to properly receive a `ModuleManager` instance, and
  not expect a `ModuleEvent`.

## 2.2.0 - 2016-04-06

### Added

- [zendframework/zend-hydrator#26](https://github.com/zendframework/zend-hydrator/pull/26) exposes the
  package as a Laminas component and/or generic configuration provider, by adding the
  following:
  - `HydratorPluginManagerFactory`, which can be consumed by container-interop /
    laminas-servicemanager to create and return a `HydratorPluginManager` instance.
  - `ConfigProvider`, which maps the service `HydratorManager` to the above
    factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    laminas-mvc applications. It also provices a specification to
    `Laminas\ModuleManager\Listener\ServiceListener` to allow modules to provide
    hydrator configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.1.0 - 2016-02-18

### Added

- [zendframework/zend-hydrator#20](https://github.com/zendframework/zend-hydrator/pull/20) imports the
  documentation from laminas-stdlib, publishes it to
  https://docs.laminas.dev/laminas-hydrator/, and automates building and
  publishing the documentation.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-hydrator#6](https://github.com/zendframework/zend-hydrator/pull/6) add additional
  unit test coverage
- [zendframework/zend-hydrator#17](https://github.com/zendframework/zend-hydrator/pull/17) and
  [zendframework/zend-hydrator#23](https://github.com/zendframework/zend-hydrator/pull/23) update the code
  to be forwards compatible with laminas-servicemanager v3, and to depend on
  laminas-stdlib and laminas-eventmanager v3.

## 2.0.0 - 2015-09-17

### Added

- The following classes were marked `final` (per their original implementation
  in laminas-stdlib):
  - `Laminas\Hydrator\NamingStrategy\IdentityNamingStrategy`
  - `Laminas\Hydrator\NamingStrategy\ArrayMapNamingStrategy`
  - `Laminas\Hydrator\NamingStrategy\CompositeNamingStrategy`
  - `Laminas\Hydrator\Strategy\ExplodeStrategy`
  - `Laminas\Hydrator\Strategy\StrategyChain`
  - `Laminas\Hydrator\Strategy\DateTimeFormatterStrategy`
  - `Laminas\Hydrator\Strategy\BooleanStrategy`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2015-09-17

Initial release. This ports all hydrator classes and functionality from
[laminas-stdlib](https://github.com/laminas/laminas-stdlib) to a standalone
repository. All final keywords are removed, to allow a deprecation cycle in the
laminas-stdlib component.

Please note: the following classes will be marked as `final` for a version 2.0.0
release to immediately follow 1.0.0:

- `Laminas\Hydrator\NamingStrategy\IdentityNamingStrategy`
- `Laminas\Hydrator\NamingStrategy\ArrayMapNamingStrategy`
- `Laminas\Hydrator\NamingStrategy\CompositeNamingStrategy`
- `Laminas\Hydrator\Strategy\ExplodeStrategy`
- `Laminas\Hydrator\Strategy\StrategyChain`
- `Laminas\Hydrator\Strategy\DateTimeFormatterStrategy`
- `Laminas\Hydrator\Strategy\BooleanStrategy`

As such, you should not extend them.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
