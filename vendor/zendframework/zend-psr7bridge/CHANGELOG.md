# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.2.2 - 2016-05-10

### Added

- [#8](https://github.com/zendframework/zend-psr8bridge/pull/8) adds and
  publishes the documentation to https://zendframework.github.io/zend-psr7bridge/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-psr7bridge/pull/7) fixes
  the logic in `Psr7ServerRequest::convertUploadedFiles()` to ensure that the
  `tmp_name` is provided to the `$_FILES` structure from the PSR-7 uploaded
  files.
- [#7](https://github.com/zendframework/zend-psr7bridge/pull/7) fixes
  the logic in `Psr7ServerRequest::convertFilesToUploaded()` to iterate the
  entire value provided it, instead of a fictitious `file` key.

## 0.2.1 - 2015-12-15

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#5](https://github.com/zendframework/zend-psr7bridge/pull/5) Updates
  `Psr7ServerRequest::fromZend()` to inject the generated PSR-7 request
  instance with the zend-http cookies.

## 0.2.0 - 2015-09-28

### Added

- [#3](https://github.com/zendframework/zend-psr7bridge/pull/3) Adds support for
  zend-http -&gt; PSR-7 request tanslation.
- [#3](https://github.com/zendframework/zend-psr7bridge/pull/3) Adds support for
  PSR-7 &lt;-&gt; zend-http response tanslation.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2015-08-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#2](https://github.com/zendframework/zend-psr7bridge/pull/2) updates
  `Zend\Psr7Bridge\Zend\Request`'s constructor to call `setUri()` instead of
  `setRequestUri()`.

## 0.1.0 - 2015-08-06

Initial release!

### Added

- `Zend\Psr7Bridge\Psr7ServerRequest::toZend($request, $shallow = false)` allows
  converting a `Psr\Http\Message\ServerRequestInterface` to a
  `Zend\Http\PhpEnvironment\Request` instance. The `$shallow` flag, when
  enabled, will omit the body content, body parameters, and upload files from
  the zend-http request (e.g., for routing purposes).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
