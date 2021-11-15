# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.3.1 - 2020-11-19

### Fixed

- [#13](https://github.com/laminas/laminas-stdlib/pull/13) fixes an issue whereby calling `Laminas\Stdlib\PriorityQueue::extract()` left the instance in an invalid state, causing other functionality like `count()` and `contains()` to reflect the prior state.


-----

### Release Notes for [3.3.1](https://github.com/laminas/laminas-stdlib/milestone/2)



### 3.3.1

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### Bug

 - [13: Ensure PriorityQueue::extract also removes item from internal list](https://github.com/laminas/laminas-stdlib/pull/13) thanks to @weierophinney and @lougv1

## 3.3.0 - 2020-08-25

### Added

- [#9](https://github.com/laminas/laminas-stdlib/pull/9) adds support for the upcoming PHP 8.0 release.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#10](https://github.com/laminas/laminas-stdlib/pull/10) removes support for PHP versions prior to PHP 7.3.

### Fixed

- Nothing.

## 3.2.1 - 2018-08-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#92](https://github.com/zendframework/zend-stdlib/pull/92) fixes serialization of `SplPriorityQueue` by ensuring its `$serial`
  property is also serialized.

- [zendframework/zend-stdlib#91](https://github.com/zendframework/zend-stdlib/pull/91) fixes behavior in the `ArrayObject` implementation that was not
  compatible with PHP 7.3.

## 3.2.0 - 2018-04-30

### Added

- [zendframework/zend-stdlib#87](https://github.com/zendframework/zend-stdlib/pull/87) adds support for PHP 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-stdlib#87](https://github.com/zendframework/zend-stdlib/pull/87) removes support for HHVM.

### Fixed

- Nothing.

## 3.1.1 - 2018-04-12

### Added

- Nothing.

### Changed

- [zendframework/zend-stdlib#67](https://github.com/zendframework/zend-stdlib/pull/67) changes the typehint of the `$content` property
  of the `Message` class to indicate it is a string. All known implementations
  already assumed this.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#60](https://github.com/zendframework/zend-stdlib/pull/60) fixes an issue whereby calling `remove()` would
  incorrectly re-calculate the maximum priority stored in the queue.

- [zendframework/zend-stdlib#60](https://github.com/zendframework/zend-stdlib/pull/60) fixes an infinite loop condition that can occur when
  inserting an item at 0 priority.

## 3.1.0 - 2016-09-13

### Added

- [zendframework/zend-stdlib#63](https://github.com/zendframework/zend-stdlib/pull/63) adds a new
  `Laminas\Stdlib\ConsoleHelper` class, providing minimal support for writing
  output to `STDOUT` and `STDERR`, with optional colorization, when the console
  supports that feature.

### Deprecated

- [zendframework/zend-stdlib#38](https://github.com/zendframework/zend-stdlib/pull/38) deprecates
  `Laminas\Stdlib\JsonSerializable`, as all supported version of PHP now support
  it.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.1 - 2016-04-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#59](https://github.com/zendframework/zend-stdlib/pull/59) fixes a notice
  when defining the `Laminas\Json\Json::GLOB_BRACE` constant on systems using
  non-gcc glob implementations.

## 3.0.0 - 2016-02-03

### Added

- [zendframework/zend-stdlib#51](https://github.com/zendframework/zend-stdlib/pull/51) adds PHP 7 as a
  supported PHP version.
- [zendframework/zend-stdlib#51](https://github.com/zendframework/zend-stdlib/pull/51) adds a migration
  document from v2 to v3. Hint: if you use hydrators, you need to be using
  laminas-hydrator instead!
- [zendframework/zend-stdlib#51](https://github.com/zendframework/zend-stdlib/pull/51) adds automated
  documentation builds to gh-pages.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-stdlib#33](https://github.com/zendframework/zend-stdlib/pull/33) - removed
  deprecated classes
  - *All Hydrator classes* see zendframework/zend-stdlib#22.
  - `Laminas\Stdlib\CallbackHandler` see zendframework/zend-stdlib#35
- [zendframework/zend-stdlib#37](https://github.com/zendframework/zend-stdlib/pull/37) - removed
  deprecated classes and polyfills:
  - `Laminas\Stdlib\DateTime`; this had been deprecated since 2.5, and only
    existed as a polyfill for the `createFromISO8601()` support, now standard
    in all PHP versions we support.
  - `Laminas\Stdlib\Exception\InvalidCallbackException`, which was unused since zendframework/zend-stdlib#33.
  - `Laminas\Stdlib\Guard\GuardUtils`, which duplicated `Laminas\Stdlib\Guard\AllGuardsTrait`
    to allow usage with pre-PHP 5.4 versions.
  - `src/compatibility/autoload.php`, which has been dprecated since 2.5.
- [zendframework/zend-stdlib#37](https://github.com/zendframework/zend-stdlib/pull/37) - removed
  unneeded dependencies:
  - laminas-config (used only in testing ArrayUtils, and the test was redundant)
  - laminas-serializer (no longer used)
- [zendframework/zend-stdlib#51](https://github.com/zendframework/zend-stdlib/pull/51) removes the
  documentation for hydrators, as those are part of the laminas-hydrator
  component.

### Fixed

- Nothing.

## 2.7.4 - 2015-10-15

### Added

- Nothing.

### Deprecated

- [zendframework/zend-stdlib#35](https://github.com/zendframework/zend-stdlib/pull/35) deprecates
  `Laminas\Stdlib\CallbackHandler`, as the one component that used it,
  laminas-eventmanager, will no longer depend on it starting in v3.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.3 - 2015-09-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#27](https://github.com/zendframework/zend-stdlib/pull/27) fixes a race
  condition in the `FastPriorityQueue::remove()` logic that occurs when removing
  items iteratively from the same priority of a queue.

## 2.7.2 - 2015-09-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#26](https://github.com/zendframework/zend-stdlib/pull/26) fixes a subtle
  inheritance issue with deprecation in the hydrators, and updates the
  `HydratorInterface` to also extend the laminas-hydrator `HydratorInterface` to
  ensure LSP is preserved.

## 2.7.1 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#24](https://github.com/zendframework/zend-stdlib/pull/24) fixes an import in
  `FastPriorityQueue` to alias `SplPriorityQueue` in order to disambiguate with
  the local override present in the component.

## 2.7.0 - 2015-09-22

### Added

- [zendframework/zend-stdlib#19](https://github.com/zendframework/zend-stdlib/pull/19) adds a new
  `FastPriorityQueue` implementation. It follows the same signature as
  `SplPriorityQueue`, but uses a performance-optimized algorithm:

  - inserts are 2x faster than `SplPriorityQueue` and 3x faster than the
    `Laminas\Stdlib\PriorityQueue` implementation.
  - extracts are 4x faster than `SplPriorityQueue` and 4-5x faster than the
    `Laminas\Stdlib\PriorityQueue` implementation.

  The intention is to use this as a drop-in replacement in the
  `laminas-eventmanager` component to provide performance benefits.

### Deprecated

- [zendframework/zend-stdlib#20](https://github.com/zendframework/zend-stdlib/pull/20) deprecates *all
  hydrator* classes, in favor of the new [laminas-hydrator](https://github.com/laminas/laminas-hydrator)
  component. All classes were updated to extend their laminas-hydrator equivalents,
  and marked as `@deprecated`, indicating the equivalent class from the other
  repository.

  Users *should* immediately start changing their code to use the laminas-hydrator
  equivalents; in most cases, this can be as easy as removing the `Stdlib`
  namespace from import statements or hydrator configuration. Hydrators will be
  removed entirely from laminas-stdlib in v3.0, and all future updates to hydrators
  will occur in the laminas-hydrator library.

  Changes with backwards compatibility implications:

  - Users implementing `Laminas\Stdlib\Hydrator\HydratorAwareInterface` will need to
    update their `setHydrator()` implementation to typehint on
    `Laminas\Hydrator\HydratorInterface`. This can be done by changing the import
    statement for that interface as follows:

    ```php
    // Replace this:
    use Laminas\Stdlib\Hydrator\HydratorInterface;
    // with this:
    use Laminas\Hydrator\HydratorInterface;
    ```

    If you are not using imports, change the typehint within the signature itself:

    ```php
    // Replace this:
    public function setHydrator(\Laminas\Stdlib\Hydrator\HydratorInterface $hydrator)
    // with this:
    public function setHydrator(\Laminas\Hydrator\HydratorInterface $hydrator)
    ```

    If you are using `Laminas\Stdlib\Hydrator\HydratorAwareTrait`, no changes are
    necessary, unless you override that method.

  - If you were catching hydrator-generated exceptions, these were previously in
    the `Laminas\Stdlib\Exception` namespace. You will need to update your code to
    catch exceptions in the `Laminas\Hydrator\Exception` namespace.

  - Users who *do* migrate to laminas-hydrator may end up in a situation where
    their code will not work with existing libraries that are still type-hinting
    on the laminas-stdlib interfaces. We will be attempting to address that ASAP,
    but the deprecation within laminas-stdlib is necessary as a first step.

    In the meantime, you can write hydrators targeting laminas-stdlib still in
    order to guarantee compatibility.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.0 - 2015-07-21

### Added

- [zendframework/zend-stdlib#13](https://github.com/zendframework/zend-stdlib/pull/13) adds
  `Laminas\Stdlib\Hydrator\Iterator`, which provides mechanisms for hydrating
  objects when iterating a traversable. This allows creating generic collection
  resultsets; the original idea was pulled from
  [PhlyMongo](https://github.com/phly/PhlyMongo), where it was used to hydrate
  collections retrieved from MongoDB.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2015-07-21

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#9](https://github.com/zendframework/zend-stdlib/pull/9) fixes an issue with
  count incrementation during insert in PriorityList, ensuring that incrementation only
  occurs when the item inserted was not previously present in the list.

## 2.4.4 - 2015-07-21

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-stdlib#9](https://github.com/zendframework/zend-stdlib/pull/9) fixes an issue with
  count incrementation during insert in PriorityList, ensuring that incrementation only
  occurs when the item inserted was not previously present in the list.
