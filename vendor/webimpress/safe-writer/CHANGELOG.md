# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.2.0 - 2021-04-19

### Added

- Nothing.

### Changed

- [#50](https://github.com/webimpress/safe-writer/pull/50) changes suppressing warnings in `FileWriter::writeFile` method. Custom error handler is used instead of `@`, so it is not possible to handle them outside anymore.

### Deprecated

- Nothing.

### Removed

- [#51](https://github.com/webimpress/safe-writer/pull/51) removes support for PHP 7.2.

### Fixed

- Nothing.

## 2.1.0 - 2020-08-25

### Added

- [#7](https://github.com/webimpress/safe-writer/pull/7) adds support for PHP 8.0.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.1 - 2020-03-21

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/webimpress/safe-writer/pull/6) fixes issue when target directory is not writeable - throws exception earlier and prevents fallback to system temp directory.

## 1.0.2 - 2020-03-21

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/webimpress/safe-writer/pull/6) fixes issue when target directory is not writeable - throws exception earlier and prevents fallback to system temp directory.

## 2.0.0 - 2019-11-27

### Added

- [#5](https://github.com/webimpress/safe-writer/pull/5) adds `\Throwable` extension for package-specific exception marker `Webimpress\SafeWriter\Exception\ExceptionInterface`.

### Changed

- [#5](https://github.com/webimpress/safe-writer/pull/5) changes all exception classes to be non-instantiable and all theirs public method to be internal.
  Library exceptions can only be caught in the user code, cannot be thrown.  

- [#5](https://github.com/webimpress/safe-writer/pull/5) changes all method declarations to have type hints and return types.

### Deprecated

- Nothing.

### Removed

- [#5](https://github.com/webimpress/safe-writer/pull/5) removes support for PHP versions prior to 7.2.

### Fixed

- Nothing.

## 1.0.1 - 2019-11-16

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#4](https://github.com/webimpress/safe-writer/pull/4) fixes exception message when temporary file cannot be created.

## 1.0.0 - 2019-11-15

### Added

- Adds function to safely writing files to avoid race conditions when
  the same file is written multiple times in a short time period,
  and errors on reading not fully written files. Example usage:

  ```php
  use Webimpress\SafeWriter\FileWriter;

  $targetFile = __DIR__ . '/config-cache.php';
  $content = "<?php\nreturn " . var_export($data, true) . ';';

  FileWriter::writeFile($targetFile, $content, 0666);
  ```

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
