# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.2 - 2018-05-01

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#168](https://github.com/zendframework/zend-cache/pull/168) fixes a typo in a variable name within the `Filesystem::setTags()` method which
  prevented clearing of tags when using that adapter.

## 2.8.1 - 2018-04-26

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#165](https://github.com/zendframework/zend-cache/issues/165) fixes an issue
  with the memcached adapter ensuring that retrieval returns boolean false when
  unable to retrieve the requested item.

## 2.8.0 - 2018-04-24

### Added

- [#148](https://github.com/zendframework/zend-cache/pull/148) adds support for PHP 7.1 and 7.2.

- [#46](https://github.com/zendframework/zend-cache/issues/46), [#155](https://github.com/zendframework/zend-cache/issues/155), and [#161](https://github.com/zendframework/zend-cache/issues/161) add support for [PSR-6](https://www.php-fig.org/psr/psr-6/) (Caching Interface).
  They provides an implementation of `Psr\Cache\CacheItemPoolInterface` via
  `Zend\Cache\Psr\CacheItemPool\CacheItemPoolDecorator`, which accepts a
  `Zend\Cache\Storage\StorageInterface` instance to its constructor, and proxies
  the various PSR-6 methods to it. It also provides a
  `Psr\Cache\CacheItemInterface` implementation via `Zend\Cache\Psr\CacheItemPool\CacheItem`,
  which provides a value object for both introspecting cache fetch results, as
  well as providing values to cache.

- [#152](https://github.com/zendframework/zend-cache/pull/152), [#155](https://github.com/zendframework/zend-cache/pull/155), [#159](https://github.com/zendframework/zend-cache/pull/159), and [#161](https://github.com/zendframework/zend-cache/issues/161)
  add an adapter providing [PSR-16](https://www.php-fig.org/psr/psr-16/) (Caching Library Interface) support.
  The new class, `Zend\Cache\Psr\SimpleCache\SimpleCacheDecorator`, accepts a
  `Zend\Cache\Storage\StorageInterface` instance to its constructor, and proxies
  the various PSR-16 methods to it.

- [#154](https://github.com/zendframework/zend-cache/pull/154) adds an ext-mongodb adapter, `Zend\Cache\Storage\Adapter\ExtMongoDb`.
  You may use the `StorageFactory` to create an instance using either the fully qualified class
  name as the adapter name, or the strings `ext_mongo_db` or `ExtMongoDB` (or most variations
  on case of the latter string). The options it accepts are the same as for the existing
  `Zend\Cache\Storage\Adapter\MongoDb`, and it provides the same capabilities. The adapter
  requires the mongodb/mongodb package to operate.

- [#120](https://github.com/zendframework/zend-cache/pull/120) adds the ability to configure alternate file suffixes for both
  cache and tag cache files within the Filesystem adapter. Use the `suffix` and `tag_suffix`
  options to set them; they will default to `dat` and `tag`, respectively.

- [#79](https://github.com/zendframework/zend-cache/issues/79)
  Add capability for the "lock-on-expire" feature (úsed by Zend Data Cache)

### Changed

- [#116](https://github.com/zendframework/zend-cache/pull/116) adds docblock method chaining consistency.

### Deprecated

- Nothing.

### Removed

- [#101](https://github.com/zendframework/zend-cache/pull/101) removes support for PHP 5.5.

- [#148](https://github.com/zendframework/zend-cache/pull/148) removes support for HHVM.

### Fixed

- [#151](https://github.com/zendframework/zend-cache/pull/151) adds logic to normalize options before creating the underlying Redis
  resource when using a Redis adapter, fixing issues when using an array with the server and port
  to use for connecting to the server.

- [#151](https://github.com/zendframework/zend-cache/pull/151) adds logic to prevent changing the underlying resource within Redis adapter instances.

- [#150](https://github.com/zendframework/zend-cache/pull/150) fixes an issue with how CAS tokens are handled when using the memcached adapter.

- [#61](https://github.com/zendframework/zend-cache/pull/61) sets the Zend Data Cache minTtl value to 1.

- [#147](https://github.com/zendframework/zend-cache/pull/147) fixes the Redis extension by ensuring it casts the results of `exists()` to a
  boolean when testing if the storage contains an item.

- [#146](https://github.com/zendframework/zend-cache/pull/146) fixes several methods to change `@return` annotations to `@throws` where applicable.

- [#134](https://github.com/zendframework/zend-cache/pull/134) adds a missing import statement for `Traversable` within the `AdapterOptions` class.

- [#128](https://github.com/zendframework/zend-cache/pull/128)
  Fixed incorrect variable usage in MongoDbResourceManager

## 2.7.2 - 2016-12-16

### Added

- [#124](https://github.com/zendframework/zend-cache/pull/124)
  New coding standard

### Deprecated

- [#123](https://github.com/zendframework/zend-cache/pull/123)
  Deprecate capability "expiredRead".
  It's basically providing the same information as staticTtl but from a wrong PoV

### Removed

- Nothing.

### Fixed

- [#122](https://github.com/zendframework/zend-cache/pull/122)
  Fixed redis doc for lib_options (not lib_option)
- [#118](https://github.com/zendframework/zend-cache/pull/118)
  fixed redis tests in case running with different server
- [#119](https://github.com/zendframework/zend-cache/pull/119)
  Redis: Don't call method Redis::info() every time
- [#113](https://github.com/zendframework/zend-cache/pull/113)
  Travis: Moved coverage reporting to latest env
- [#114](https://github.com/zendframework/zend-cache/pull/114)
  Travis: removed fast_finish flag
- [#107](https://github.com/zendframework/zend-cache/issues/107)
  fixed redis server version test in Redis::internalGetMetadata()
- [#111](https://github.com/zendframework/zend-cache/pull/111)
  Fixed typo in storage adapter doc
- [#102](https://github.com/zendframework/zend-cache/pull/102)
  filesystem: fixes a lot of possible race conditions

## 2.7.1 - 2016-05-12

### Added

- [#35](https://github.com/zendframework/zend-cache/pull/35)
  Added benchmarks using PHPBench

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#76](https://github.com/zendframework/zend-cache/pull/76)
  ZendServer: fixed return null on missing item
- [#88](https://github.com/zendframework/zend-cache/issues/88)
  Redis: fixed segfault on storing NULL and fixed supported datatypes capabilities
- [#95](https://github.com/zendframework/zend-cache/issues/95)
  don't try to unserialize missing items
- [#66](https://github.com/zendframework/zend-cache/issues/66)
  fixed Memcached::internalSetItems in PHP-7 by reducing variables by reference
- [#57](https://github.com/zendframework/zend-cache/pull/57)
  Memcached: HHVM compatibility and reduced duplicated code
- [#91](https://github.com/zendframework/zend-cache/pull/91)
  fixed that order of adapter options may cause exception
- [#98](https://github.com/zendframework/zend-cache/pull/98) updates the plugin
  manager alias list to ensure all adapter name permutations commonly used are
  accepted.

## 2.7.0 - 2016-04-12

### Added

- [#59](https://github.com/zendframework/zend-cache/pull/59)
  XCache >= 3.1.0 works in CLI mode
- [#23](https://github.com/zendframework/zend-cache/issues/23)
  [#47](https://github.com/zendframework/zend-cache/issues/47)
  Added an Apcu storage adapter as future replacement for Apc
- [#63](https://github.com/zendframework/zend-cache/pull/63)
  Implemented ClearByNamespaceInterface in Stoage\Adapter\Redis
- [#94](https://github.com/zendframework/zend-cache/pull/94) adds factories for
  each of the `PatternPluginManager`, `AdapterPluginManager`, and storage
  `PluginManager`.
- [#94](https://github.com/zendframework/zend-cache/pull/94) exposes the package
  as a standalone config-provider / ZF component, by adding:
  - `Zend\Cache\ConfigProvider`, which enables the
    `StorageCacheAbstractServiceFactory`, and maps factories for all plugin
    managers.
  - `Zend\Cache\Module`, which does the same, for zend-mvc contexts.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#44](https://github.com/zendframework/zend-cache/issues/44)
  Filesystem: fixed race condition in method clearByTags
- [#59](https://github.com/zendframework/zend-cache/pull/59)
  XCache: fixed broken internalSetItem() with empty namespace
- [#58](https://github.com/zendframework/zend-cache/issues/58)
  XCache: Fatal error storing objects
- [#94](https://github.com/zendframework/zend-cache/pull/94) updates the
  `PatternPluginManager` to accept `$options` to `get()` and `build()`, cast
  them to a `PatternOptions` instance, and inject them into the generated plugin
  instance. This change allows better standalone usage of the plugin manager.
- [#94](https://github.com/zendframework/zend-cache/pull/94) updates the
  `StorageCacheFactory` and `StorageCacheAbstractServiceFactory` to seed the
  `StorageFactory` with the storage plugin manager and/or adapter plugin manager
  as pulled from the provided container, if present. This change enables re-use
  of pre-configured plugin managers (e.g., those seeded with custom plugins
  and/or adapters).

## 2.6.1 - 2016-02-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#73](https://github.com/zendframework/zend-cache/pull/73) fixes how the
  `EventManager` instance is lazy-instantiated in
  `Zend\Cache\Storage\Adapter\AbstractAdapter::getEventManager()`. In 2.6.0, it
  was using the v3-specific syntax; it now uses syntax compatible with both v2
  and v3.

## 2.6.0 - 2016-02-11

### Added

- [#70](https://github.com/zendframework/zend-cache/pull/70) adds, revises, and
  publishes the documentation to https://zendframework.github.io/zend-cache/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#22](https://github.com/zendframework/zend-cache/pull/22),
  [#64](https://github.com/zendframework/zend-cache/pull/64),
  [#68](https://github.com/zendframework/zend-cache/pull/68), and
  [#69](https://github.com/zendframework/zend-cache/pull/69) update the
  component to be forwards-compatible with zend-eventmanager,
  zend-servicemanager, and zend-stdlib v3.
- [#31](https://github.com/zendframework/zend-cache/issues/31)
  Check Documentation Code Blocks
- [#53](https://github.com/zendframework/zend-cache/pull/53)
  fixed seg fault in redis adapter on PHP 7
- [#50](https://github.com/zendframework/zend-cache/issues/50)
  fixed APC tests not running on travis-ci since apcu-5 was released
- [#36](https://github.com/zendframework/zend-cache/pull/36)
  fixed AbstractAdapter::internalDecrementItems
- [#38](https://github.com/zendframework/zend-cache/pull/38)
  better test coverage of AbstractAdapter
- [#45](https://github.com/zendframework/zend-cache/pull/45)
  removed unused internal function Filesystem::readInfoFile
- [#25](https://github.com/zendframework/zend-cache/pull/25)
  MongoDd: fixed expiration support and removed duplicated tests
- [#40](https://github.com/zendframework/zend-cache/pull/40)
  Fixed TTL support of `Redis::addItem`
- [#18](https://github.com/zendframework/zend-cache/issues/18)
  Fixed `Redis::getCapabilities` and `RedisResourceManager::getMajorVersion`
  if resource wasn't initialized before

## 2.5.3 - 2015-09-15

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#15](https://github.com/zendframework/zend-cache/pull/15) fixes an issue
  observed on HHVM when merging a list of memcached servers to add to the
  storage resource.
- [#17](https://github.com/zendframework/zend-cache/pull/17) Composer: moved
  `zendframework/zend-serializer` from `require` to `require-dev` as using the
  serializer is optional.
- A fix was provided for [ZF2015-07](http://framework.zend.com/security/advisory/ZF2015-07),
  ensuring that any directories or files created by the component use umask 0002
  in order to prevent arbitrary local execution and/or local privilege
  escalation.

## 2.5.2 - 2015-07-16

### Added

- [#10](https://github.com/zendframework/zend-cache/pull/10) adds TTL support
  for the Redis adapter.
- [#6](https://github.com/zendframework/zend-cache/pull/6) adds more suggestions
  to the `composer.json` for PHP extensions supported by storage adapters.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#9](https://github.com/zendframework/zend-cache/pull/9) fixes an issue when
  connecting to a Redis instance with the `persistent_id` option.
