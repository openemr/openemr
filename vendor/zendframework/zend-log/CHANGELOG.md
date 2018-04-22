# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.10.0 - 2018-04-09

### Added

- [#58](https://github.com/zendframework/zend-log/pull/58) adds the class
  `Zend\Log\Formatter\Json`, which will format log lines as individual JSON
  objects.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.9.3 - 2018-04-09

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#79](https://github.com/zendframework/zend-log/pull/79) and
  [#86](https://github.com/zendframework/zend-log/pull/86) provide fixes to
  ensure the `FingersCrossed`, `Mongo`, and `MongoDB` writers work under PHP
  7.2.

## 2.9.2 - 2017-05-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#74](https://github.com/zendframework/zend-log/pull/74) fixes how the various
  plugin manager factories initialize the plugin manager instances, ensuring
  they are injecting the relevant configuration from the `config` service and
  thus seeding them with configured plugin services. This means that the
  `log_processors`, `log_writers`, `log_filters`, and `log_formatters`
  configuration will now be honored in non-zend-mvc contexts.
- [#62](https://github.com/zendframework/zend-log/pull/62) fixes registration of
  the alias and factory for the `PsrPlaceholder` processor plugin.
- [#66](https://github.com/zendframework/zend-log/pull/66) fixes the namespace
  of the `LogFormatterProviderInterface` when registering the
  `LogFormatterManager` with the zend-modulemanager `ServiceListener`.
- [#67](https://github.com/zendframework/zend-log/pull/67) ensures that content
  being injected into a DOM node by `Zend\Log\Formatter\Xml` is escaped so that
  XML entities will be properly emitted.
- [#73](https://github.com/zendframework/zend-log/pull/73) adds a missing import
  statement to the `Psr` log writer.

## 2.9.1 - 2016-08-11

### Added

- [#53](https://github.com/zendframework/zend-log/pull/53) adds a suggestion to
  the package definition of ext/mongodb, for those who want to use the MongoDB
  writer.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#56](https://github.com/zendframework/zend-log/pull/56) fixes an edge case
  with the `AbstractWriter` whereby instantiating a
  `Zend\Log\Writer\FormatterPluginManager` or `FilterPluginManager` prior to
  creating a writer instance would lead to a naming conflict. New aliases were
  added to prevent the conflict going forwards.

## 2.9.0 - 2016-06-22

### Added

- [#46](https://github.com/zendframework/zend-log/pull/46) adds the ability to
  specify log writer, formatter, filter, and processor plugin configuration via
  the new top-level keys:
  - `log_filters`
  - `log_formatters`
  - `log_processors`
  - `log_writers`
  These follow the same configuration patterns as any other service
  manager/plugin manager as implemented by zend-servicemanager.

  Additionally, you can now specify filer, formatter, and processor *services*
  when specifying writer configuration for a logger, as these are now backed
  by the above plugin managers.

### Deprecated

- Nothing.

### Removed

- Removes support for PHP 5.5.

### Fixed

- [#38](https://github.com/zendframework/zend-log/pull/38) adds the `MongoDb`
  writer to the list of available writer plugins; the writer was added in a
  previous release, but never enabled within the default set of writers.

## 2.8.3 - 2016-05-25

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Corrected licence headers across files within the project

## 2.8.2 - 2016-04-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#43](https://github.com/zendframework/zend-log/pull/43) fixes the
  `Module::init()` method to properly receive a `ModuleManager` instance, and
  not expect a `ModuleEvent`.

## 2.8.1 - 2016-04-06

### Added

- [#40](https://github.com/zendframework/zend-log/pull/40) adds the
  `LogFilterProviderInterface` and `LogFormatterProviderInterface` referenced in
  the `Module` class starting in 2.8.0.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.0 - 2016-04-06

### Added

- [#39](https://github.com/zendframework/zend-log/pull/39) adds the following
  factory classes for the exposed plugin managers in the component:
  - `Zend\Log\FilterPluginManagerFactory`, which returns `FilterPluginManager` instances.
  - `Zend\Log\FormatterPluginManagerFactory`, which returns `FormatterPluginManager` instances.
  - `Zend\Log\ProcessorPluginManagerFactory`, which returns `ProcessorPluginManager` instances.
  - `Zend\Log\WriterPluginManagerFactory`, which returns `WriterPluginManager` instances.
- [#39](https://github.com/zendframework/zend-log/pull/39) exposes the
  package as a ZF component and/or generic configuration provider, by adding the
  following:
  - `ConfigProvider`, which maps the available plugin managers to the
    corresponding factories as listed above, maps the `Logger` class to the
    `LoggerServiceFactory`, and registers the `LoggerAbstractServiceFactory` as
    an abstract factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    zend-mvc applications. It also provices a specifications to
    `Zend\ModuleManager\Listener\ServiceListener` to allow modules to provide
    configuration for log filters, formatters, processors, and writers.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.2 - 2016-04-06

### Added

- [#30](https://github.com/zendframework/zend-log/pull/30) adds documentation
  for each of the supported log writers.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#33](https://github.com/zendframework/zend-log/pull/33) fixes an issue with
  executing `chmod` on files mounted via NFS on an NTFS partition when using the
  stream writer.

## 2.7.1 - 2016-02-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#28](https://github.com/zendframework/zend-log/pull/28) restores the "share
  by default" flag settings of all plugin managers back to boolean `false`,
  allowing multiple instances of each plugin type. (This restores backwards
  compatibility with versions prior to 2.7.)

## 2.7.0 - 2016-02-09

### Added

- [#7](https://github.com/zendframework/zend-log/pull/7) and
  [#15](https://github.com/zendframework/zend-log/pull/15) add a new argument
  and option to `Zend\Log\Writer\Stream` to allow setting the permission mode
  for the stream. You can pass it as the optional fourth argument to the
  constructor, or as the `chmod` option if using an options array.
- [#10](https://github.com/zendframework/zend-log/pull/10) adds `array` to the
  expected return types from `Zend\Log\Formatter\FormatterInterface::format()`,
  codifying what we're already allowing.
- [#24](https://github.com/zendframework/zend-log/pull/24) prepares the
  documentation for publication, adds a chapter on processors, and publishes it
  to https://zendframework.github.io/zend-log/

### Deprecated

- [#14](https://github.com/zendframework/zend-log/pull/14) deprecates the
  following, suggesting the associated replacements:
  - `Zend\Log\Writer\FilterPluginManager` is deprecated; use
    `Zend\Log\FilterPluginManager` instead.
  - `Zend\Log\Writer\FormatterPluginManager` is deprecated; use
    `Zend\Log\FormatterPluginManager` instead.

### Removed

- Nothing.

### Fixed

- [#14](https://github.com/zendframework/zend-log/pull/14) and
  [#17](https://github.com/zendframework/zend-log/pull/17) update the component
  to be forwards-compatible with zend-stdlib and zend-servicemanager v3.

## 2.6.0 - 2015-07-20

### Added

- [#6](https://github.com/zendframework/zend-log/pull/6) adds
  [PSR-3](http://www.php-fig.org/psr/psr-3/) support to zend-log:
  - `Zend\Log\PsrLoggerAdapter` allows you to decorate a
    `Zend\Log\LoggerInterface` instance so it can be used wherever a PSR-3
    logger is expected.
  - `Zend\Log\Writer\Psr` allows you to decorate a PSR-3 logger instance for use
    as a log writer with `Zend\Log\Logger`.
  - `Zend\Log\Processor\PsrPlaceholder` allows you to use PSR-3-compliant
    message placeholders in your log messages; they will be substituted from
    corresponding keys of values passed in the `$extra` array when logging the
    message.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2015-07-06

### Added

- [#2](https://github.com/zendframework/zend-log/pull/2) adds
  the ability to specify the mail transport via the configuration options for a
  mail log writer, using the same format supported by
  `Zend\Mail\Transport\Factory::create()`; as an example:

  ```php
  $writer = new MailWriter([
      'mail' => [
          // message options
      ],
      'transport' => [
          'type' => 'smtp',
          'options' => [
               'host' => 'localhost',
          ],
      ],
  ]);
  ```

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#4](https://github.com/zendframework/zend-log/pull/4) adds better, more
  complete verbiage to the `composer.json` `suggest` section, to detail why
  and when you might need additional dependencies.
- [#1](https://github.com/zendframework/zend-log/pull/1) updates the code to
  remove conditionals related to PHP versions prior to PHP 5.5, and use bound
  closures in tests (not possible before 5.5).
