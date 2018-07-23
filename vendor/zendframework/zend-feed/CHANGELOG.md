# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.10.2 - 2018-06-18

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#81](https://github.com/zendframework/zend-feed/pull/81) updates the `Zend\Feed\Reader\Reader` and `Zend\Feed\Writer\Writer` classes to
  conditionally register their respective "GooglePlayPodcast" extensions only if
  their extension managers are aware of it. This is done due to the fact that
  existing `ExtensionManagerInterface` implementations may not register it by
  default as the extension did not exist in releases prior to 2.10.0. By having
  the registration conditional, we prevent an exception from being raised; users
  are not impacted by its absence, as the extension features were not exposed
  previously.
  
  Both `Reader` and `Writer` emit an `E_USER_NOTICE` when the extension is not
  found in the extension manager, indicating that the
  `ExtensionManagerInterface` implementation should be updated to add entries
  for the "GooglePlayPodcast" entry, feed, and/or renderer classes.

## 2.10.1 - 2018-06-05

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#79](https://github.com/zendframework/zend-feed/pull/79) fixes an issue in the `setType()` method of the iTunes feed renderer whereby it was setting
  the DOM content with an uninitialized variable.

## 2.10.0 - 2018-05-24

### Added

- [#78](https://github.com/zendframework/zend-feed/pull/78) adds support for the Google Play Podcasts 1.0 DTD in both the Reader and
  Writer subcomponents. The following new classes provide the support:

  - `Zend\Feed\Reader\Extension\GooglePlayPodcast\Entry`
  - `Zend\Feed\Reader\Extension\GooglePlayPodcast\Feed`
  - `Zend\Feed\Writer\Extension\GooglePlayPodcast\Entry`
  - `Zend\Feed\Writer\Extension\GooglePlayPodcast\Feed`
  - `Zend\Feed\Writer\Extension\GooglePlayPodcast\Renderer\Entry`
  - `Zend\Feed\Writer\Extension\GooglePlayPodcast\Renderer\Feed`

  The extensions are registered by default with both `Zend\Feed\Reader\Reader`
  and `Zend\Feed\Writer\Writer`.

- [#77](https://github.com/zendframework/zend-feed/pull/77) adds support for `itunes:image` for each of:
  - `Zend\Feed\Reader\Extension\Podcast\Entry`, via `getItunesImage()`; previously only the `Feed` supported it.
  - `Zend\Feed\Writer\Extension\ITunes\Entry`, via `setItunesImage()`; previously only the `Feed` supported it.
  - `Zend\Feed\Writer\Extension\ITunes\Renderer\Entry`; previously on the `Feed` supported it.

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Writer\Extension\ITunes\Entry::setItunesSeason()`, corresponding to the
  `itunes:season` tag, and allowing setting the season number of the episode the
  entry represents.

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Writer\Extension\ITunes\Entry::setItunesIsClosedCaptioned()`, corresponding to the
  `itunes:isClosedCaptioned` tag, and allowing setting the status of closed
  captioning support in the episode the entry represents.

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Writer\Extension\ITunes\Entry::setItunesEpisodeType()`, corresponding to the
  `itunes:episodeType` tag, and allowing setting the type of episode the entry represents
  (one of "full", "trailer", or "bonus", and defaulting to "full").

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Writer\Extension\ITunes\Entry::setEpisode()`, corresponding to the
  `itunes:episode` tag, and allowing setting the number of the episode the entry represents.

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Writer\Extension\ITunes\Feed::setItunesComplete()`, corresponding to the
  `itunes:complete` tag. It allows setting a boolean flag, indicating whether or not the
  podcast is complete (will not air new episodes).

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Writer\Extension\ITunes\Feed::setItunesType()`, corresponding to the
  `itunes:type` tag, and allowing setting the podcast type (one of "serial" or "episodic").

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Reader\Extension\Podcast\Entry::getEpisodeType()`, corresponding to the
  `itunes:episodeType` tag, and returning the type of episode the entry represents
  (one of "full", "trailer", or "bonus", and defaulting to "full").

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Reader\Extension\Podcast\Entry::getSeason()`, corresponding to the
  `itunes:season` tag, and returning the season number of the episode the entry represents.

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Reader\Extension\Podcast\Entry::isClsoedCaptioned()`, corresponding to the
  `itunes:isClosedCaptioned` tag, and returning the status of closed captioning
  in the episode the entry represents.

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Reader\Extension\Podcast\Entry::getEpisode()`, corresponding to the
  `itunes:episode` tag, and returning the number of the episode the entry represents.

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Reader\Extension\Podcast\Feed::isComplete()`, corresponding to the
  `itunes:complete` tag. It returns a boolean, indicating whether or not the podcast is
  complete (will not air new episodes).

- [#75](https://github.com/zendframework/zend-feed/pull/75) adds `Zend\Feed\Reader\Extension\Podcast\Feed::getPodcastType()`, corresponding to the
  `itunes:type` tag, and providing the podcast type (one of "serial" or "episodic", defaulting
  to the latter).

### Changed

- [#77](https://github.com/zendframework/zend-feed/pull/77) updates URI validation for `Zend\Feed\Writer\Extension\ITunes\Feed::setItunesImage()` to
  first check that we have received a string value before proceeding.

### Deprecated

- [#75](https://github.com/zendframework/zend-feed/pull/75) deprecates each of:
  - `Zend\Feed\Reader\Extension\Podcast\Entry::getKeywords()`
  - `Zend\Feed\Reader\Extension\Podcast\Feed::getKeywords()`
  - `Zend\Feed\Writer\Extension\ITunes\Entry::setKeywords()`
  - `Zend\Feed\Writer\Extension\ITunes\Feed::setKeywords()`
  as the iTunes Podcast RSS specification no longer supports keywords.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.9.1 - 2018-05-14

### Added

- Nothing.

### Changed

- [#16](https://github.com/zendframework/zend-feed/pull/16) updates the `Zend\Feed\Pubsubhubbub\AbstractCallback` to no longer use the
  `$GLOBALS['HTTP_RAW_POST_DATA']` value as a fallback when `php://input` is
  empty. The fallback existed because, prior to PHP 5.6, `php://input` could
  only be read once. As we now require PHP 5.6, the fallback is unnecessary,
  and best removed as the globals value is deprecated.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#68](https://github.com/zendframework/zend-feed/pull/68) fixes the behavior of `Zend\Feed\Writer\AbstractFeed::setTitle()` and
  `Zend\Feed\Writer\Entry::setTitle()` to accept the string `"0"`.

- [#68](https://github.com/zendframework/zend-feed/pull/68) updates both `Zend\Feed\Writer\AbstractFeed` and `Zend\Feed\Writer\Entry`
  to no longer throw an exception for entry titles which have a string value of `0`.

## 2.9.0 - 2017-12-04

### Added

- [#52](https://github.com/zendframework/zend-feed/pull/52) adds support for PHP
  7.2

- [#53](https://github.com/zendframework/zend-feed/pull/53) adds a number of
  additional aliases to the `Writer\ExtensionPluginManager` to ensure plugins
  will be pulled as expected.

- [#63](https://github.com/zendframework/zend-feed/pull/63) adds the feed title
  to the attributes incorporated in the `FeedSet` instance, per what was already
  documented.

- [#55](https://github.com/zendframework/zend-feed/pull/55) makes two API
  additions to the `StandaloneExtensionManager` implementations of both the reader
  and writer subcomponents:

  - `$manager->add($name, $class)` will add an extension class using the
    provided name.
  - `$manager->remove($name)` will remove an existing extension by the provided
    name.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#52](https://github.com/zendframework/zend-feed/pull/52) removes support for
  HHVM.

### Fixed

- [#50](https://github.com/zendframework/zend-feed/pull/50) fixes a few issues
  in the PubSubHubbub `Subscription` model where counting was being performed on
  uncountable data; this ensures the subcomponent will work correctly under PHP
  7.2.

## 2.8.0 - 2017-04-02

### Added

- [#27](https://github.com/zendframework/zend-feed/pull/27) adds a documentation
  chapter demonstrating wrapping a PSR-7 client to use with `Zend\Feed\Reader`.
- [#22](https://github.com/zendframework/zend-feed/pull/22) adds missing
  ExtensionManagerInterface on Writer\ExtensionPluginManager.
- [#32](https://github.com/zendframework/zend-feed/pull/32) adds missing
  ExtensionManagerInterface on Reader\ExtensionPluginManager.

### Deprecated

- Nothing.

### Removed

- [#38](https://github.com/zendframework/zend-feed/pull/38) dropped php 5.5
  support

### Fixed

- [#35](https://github.com/zendframework/zend-feed/pull/35) fixed
  "A non-numeric value encountered" in php 7.1
- [#39](https://github.com/zendframework/zend-feed/pull/39) fixed protocol
  relative link absolutisation
- [#40](https://github.com/zendframework/zend-feed/pull/40) fixed service
  manager v3 compatibility aliases in extension plugin managers

## 2.7.0 - 2016-02-11

### Added

- [#21](https://github.com/zendframework/zend-feed/pull/21) edits, revises, and
  prepares the documentation for publication at https://zendframework.github.io/zend-feed/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#20](https://github.com/zendframework/zend-feed/pull/20) makes the two
  zend-servicemanager extension manager implementations forwards compatible
  with version 3, and the overall code base forwards compatible with zend-stdlib
  v3.

## 2.6.0 - 2015-11-24

### Added

- [#13](https://github.com/zendframework/zend-feed/pull/13) introduces
  `Zend\Feed\Writer\StandaloneExtensionManager`, an implementation of
  `Zend\Feed\Writer\ExtensionManagerInterface` that has no dependencies.
  `Zend\Feed\Writer\ExtensionManager` now composes this by default, instead of
  `Zend\Feed\Writer\ExtensionPluginManager`, for managing the various feed and
  entry extensions. If you relied on `ExtensionPluginManager` previously, you
  will need to create an instance manually and inject it into the `Writer`
  instance.
- [#14](https://github.com/zendframework/zend-feed/pull/14) introduces:
  - `Zend\Feed\Reader\Http\HeaderAwareClientInterface`, which extends
    `ClientInterface` and adds an optional argument to the `get()` method,
    `array $headers = []`; this argument allows specifying request headers for
    the client to send. `$headers` should have header names for keys, and the
    values should be arrays of strings/numbers representing the header values
    (if only a single value is necessary, it should be represented as an single
    value array).
  - `Zend\Feed\Reader\Http\HeaderAwareResponseInterface`, which extends
    `ResponseInterface` and adds the method `getHeader($name, $default = null)`.
    Clients may return either a `ResponseInterface` or
    `HeaderAwareResponseInterface` instance.
  - `Zend\Feed\Reader\Http\Response`, which is an implementation of
    `HeaderAwareResponseInterface`. Its constructor accepts the status code,
    body, and, optionally, headers.
  - `Zend\Feed\Reader\Http\Psr7ResponseDecorator`, which is an implementation of
    `HeaderAwareResponseInterface`. Its constructor accepts a PSR-7 response
    instance, and the various methdos then proxy to those methods. This should
    make creating wrappers for PSR-7 HTTP clients trivial.
  - `Zend\Feed\Reader\Http\ZendHttpClientDecorator`, which decorates a
    `Zend\Http\Client` instance, implements `HeaderAwareClientInterface`, and
    returns a `Response` instance seeded from the zend-http response upon
    calling `get()`. The class exposes a `getDecoratedClient()` method to allow
    retrieval of the decorated zend-http client instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#5](https://github.com/zendframework/zend-feed/pull/5) fixes the enclosure
  length check to allow zero and integer strings.
- [#2](https://github.com/zendframework/zend-feed/pull/2) ensures that the
  routine for "absolutising" a link in `Reader\FeedSet` always generates a URI
  with a scheme.
- [#14](https://github.com/zendframework/zend-feed/pull/14) makes the following
  changes to fix behavior around HTTP clients used within
  `Zend\Feed\Reader\Reader`:
  - `setHttpClient()` now ensures that the passed client is either a
    `Zend\Feed\Reader\Http\ClientInterface` or `Zend\Http\Client`, raising an
    `InvalidArgumentException` if neither. If a `Zend\Http\Client` is passed, it
    is passed to the constructor of `Zend\Feed\Reader\Http\ZendHttpClientDecorator`,
    and the decorator instance is used.
  - `getHttpClient()` now *always* returns a `Zend\Feed\Reader\Http\ClientInterface`
    instance. If no instance is currently registered, it lazy loads a
    `ZendHttpClientDecorator` instance.
  - `import()` was updated to consume a `ClientInterface` instance; when caches
    are in play, it checks the client against `HeaderAwareClientInterface` to
    determine if it can check for HTTP caching headers, and, if so, to retrieve
    them.
  - `findFeedLinks()` was updated to consume a `ClientInterface`.
