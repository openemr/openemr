# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.2.0 - 2021-02-25


-----

### Release Notes for [1.2.0](https://github.com/laminas/laminas-zendframework-bridge/milestone/7)

Feature release (minor)

## Added

- Added Psalm support

## Removed

- Dropped support for PHP prior 7.3

### 1.2.0

- Total issues resolved: **1**
- Total pull requests resolved: **2**
- Total contributors: **3**

#### Enhancement

 - [78: GitHub Actions &amp; Psalm](https://github.com/laminas/laminas-zendframework-bridge/pull/78) thanks to @gsteel and @boesing

#### Bug

 - [72: Merge release 1.1.1 into 1.2.x](https://github.com/laminas/laminas-zendframework-bridge/pull/72) thanks to @github-actions[bot]

## 1.1.1 - 2020-09-14

### Fixed

- [#71](https://github.com/laminas/laminas-zendframework-bridge/pull/71) fixes detection of the vendor directory when the `COMPOSER_VENDOR_DIR` env variable is missing or empty. Previously, this could lead to scenarios where a non-existent path was used for finding the bridge autoloader.

-----

### Release Notes for [1.1.1](https://github.com/laminas/laminas-zendframework-bridge/milestone/5)

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [71: Verify `COMPOSER&#95;VENDOR&#95;DIR` in conditional](https://github.com/laminas/laminas-zendframework-bridge/pull/71) thanks to @aaronbushnell

## 1.1.0 - 2020-08-18

### Added

- [#68](https://github.com/laminas/laminas-zendframework-bridge/pull/68) adds support for the upcoming PHP 8.0 release.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#67](https://github.com/laminas/laminas-zendframework-bridge/pull/67) fixes how the Composer autoload file is found, looking first in the `COMPOSER_VENDOR_DIR` before trying to resolve relative to where the package is installed.

## 1.0.4 - 2020-05-20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#66](https://github.com/laminas/laminas-zendframework-bridge/pull/66) ensures that references to BjyAuthorize templates are not rewritten, so that they can be resolved during runtime.

## 1.0.3 - 2020-04-03

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#63](https://github.com/laminas/laminas-zendframework-bridge/pull/63) fixes handling of dependency configuration to ensure each of delegators, initializers, and abstract factories are properly handled during configuraiton post processing. The new approach should allow delegators to work post-migration to Laminas or Mezzio.

- [#61](https://github.com/laminas/laminas-zendframework-bridge/pull/61) ensures configuration for delegator factories gets rewritten; the functionality broke in version 1.0.1.

## 1.0.2 - 2020-03-26

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#55](https://github.com/laminas/laminas-zendframework-bridge/pull/55) adds provisions to ensure that references to legacy classes/interfaces in dependency configuration always create aliases from the legacy to the new classes. Previously, we did straight replacements in the configuration, which could lead to the legacy service no longer being available. Now it will remain available.

- [#59](https://github.com/laminas/laminas-zendframework-bridge/pull/59) fixes the replacement rules such as to avoid replacing references to API Skeletons packages, classes, or configuration keys.

- [#57](https://github.com/laminas/laminas-zendframework-bridge/pull/57) fixes how references to the "zf-apigility" key are replaced. Previously, they were rewritten to "laminas-api-tools", but the correct replacement is "api-tools".

- [#56](https://github.com/laminas/laminas-zendframework-bridge/pull/56) provides a mechanism to add additional maps with multiple levels of namespace separator escaping, in order to ensure that all various known permutations are matched. The escaping is applied to both the original and target, to ensure that rewrites conform to the original escaping.

- [#56](https://github.com/laminas/laminas-zendframework-bridge/pull/56) makes changes to the replacement rules to ensure we do not replace references to "Zend" or "ZF" if they occur as subnamespaces OR as class names (formerly, we only enforced subnamespaces). Additional rules were provided for cases where one or both occur within our own packages.

- [#52](https://github.com/laminas/laminas-zendframework-bridge/pull/52) fixes a scenario whereby factory _values_ were not being rewritten during configuration post processing.

- [#52](https://github.com/laminas/laminas-zendframework-bridge/pull/52) fixes an issue that occurs with the configuration post processor. Previously, when a service name used as a factory or invokable was encountered that referenced a legacy class, it would get rewritten. This would cause issues if the service was not exposed in the original legacy package, however, as there would now be no alias of the legacy service to the new one. This patch modifies the configuration post processor such that it now tests to see if a service name it will rename exists as an alias; if not, it also creates the alias.

## 1.0.1 - 2020-01-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#47](https://github.com/laminas/laminas-zendframework-bridge/pull/47) adds entries for rewriting the various `::*Zend()` methods exposed in the psr7bridge to `::*Laminas()` during migrations.

- [#46](https://github.com/laminas/laminas-zendframework-bridge/pull/46) adds a rule to rewrite the config key `use_zend_loader` to `use_laminas_loader`.

- [#45](https://github.com/laminas/laminas-zendframework-bridge/pull/45) adds a rule to exclude rewriting of view paths provided by the various Doctrine modules targeting the developer tools.

## 1.0.0 - 2019-12-31

### Added

- First stable release.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.5 - 2019-12-23

### Added

- Nothing.

### Changed

- [#42](https://github.com/laminas/laminas-zendframework-bridge/pull/42) modifies the replacement rules to no longer rewrite zf-deploy; the package will not be coming to the new organizations.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.4 - 2019-12-18

### Added

- Nothing.

### Changed

- [#40](https://github.com/laminas/laminas-zendframework-bridge/pull/40) adds exclusion rules for subnamespaces that reference Zend, ZF, ZendService, or ZendOAuth to ensure they are not rewritten.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#40](https://github.com/laminas/laminas-zendframework-bridge/pull/40) adds exclusions for classes referencing Zend Server product features to ensure they are not rewritten (e.g., `ZendServerDisk`, `ZendServerShm`, `ZendMonitor`).

## 0.4.3 - 2019-12-17

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#39](https://github.com/laminas/laminas-zendframework-bridge/pull/39) fixes an issue when using the Auryn DI container. The class `Northwoods\Container\Zend\Config` was incorrectly being renamed to `Northwoods\Container\Laminas\Config` (which should not happen, as it is not a class under our control).

## 0.4.2 - 2019-12-16

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#36](https://github.com/laminas/laminas-zendframework-bridge/pull/36) adds some cases for classes that contain the verbiage "Expressive" and "Apigility" ot ensure they are rewritten correctly.

## 0.4.1 - 2019-12-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#35](https://github.com/laminas/laminas-zendframework-bridge/pull/35) removes zend-debug from the replacement list, as it is not being brought over to Laminas.

## 0.4.0 - 2019-11-27

### Added

- Nothing.

### Changed

- [#32](https://github.com/laminas/laminas-zendframework-bridge/pull/32) changes all references to Expressive to instead reference Mezzio.

- [#32](https://github.com/laminas/laminas-zendframework-bridge/pull/32) changes all references to Apigility to instead reference Laminas API Tools. The vendor becomes laminas-api-tools, the URL becomes api-tools.getlaminas.org, packages and repos are prefixed with api-tools, and namespaces become `Laminas\ApiTools`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.8 - 2019-11-14

### Added

- [#29](https://github.com/laminas/laminas-zendframework-bridge/pull/29) adds entries to translate `ZendDeveloperTools` to `Laminas\DeveloperTools`, and vice-versa.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.7 - 2019-11-12

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#28](https://github.com/laminas/laminas-zendframework-bridge/pull/28) updates the `zenddevelopertools` string to rewrite to `laminas-developer-tools` instead of `laminasdevelopertools`.

## 0.3.6 - 2019-11-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#27](https://github.com/laminas/laminas-zendframework-bridge/pull/27) adds a rewrite rule for zend-framework.flf => laminas-project.flf.

## 0.3.5 - 2019-11-06

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#25](https://github.com/laminas/laminas-zendframework-bridge/pull/25) adds entries for ZendHttp and ZendModule, which are file name segments in files from the zend-feed and zend-config-aggregator-module packages, respectively.

## 0.3.4 - 2019-11-06

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#24](https://github.com/laminas/laminas-zendframework-bridge/pull/24) adds a rule to never rewrite the string `Doctrine\Zend`.

- [#23](https://github.com/laminas/laminas-zendframework-bridge/pull/23) adds a missing map for each of ZendAcl and ZendRbac, which occur in the zend-expressive-authorization-acl and zend-expressive-authorization-rbac packages, respectively.

## 0.3.3 - 2019-11-06

### Added

- [#22](https://github.com/laminas/laminas-zendframework-bridge/pull/22) adds configuration post-processing features, exposed both as a laminas-config-aggregator post processor (for use with Expressive applications) and as a laminas-modulemanager `EVENT_MERGE_CONFIG` listener (for use with MVC applications). When registered, it will post-process the configuration, replacing known Zend Framework-specific strings with their Laminas replacements. A ruleset is provided that ensures dependency configuration is rewritten in a safe manner, routing configuration is skipped, and certain top-level configuration keys are matched exactly (instead of potentially as substrings or word stems). A later release of laminas-migration will auto-register these tools in applications when possible.

### Changed

- [#22](https://github.com/laminas/laminas-zendframework-bridge/pull/22) removes support for PHP versions prior to PHP 5.6. We have decided to only support supported PHP versions, whether that support is via php.net or commercial. The lowest supported PHP version we have found is 5.6. Users wishing to migrate to Laminas must at least update to PHP 5.6 before doing so.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.2 - 2019-10-30

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#21](https://github.com/laminas/laminas-zendframework-bridge/pull/21) removes rewriting of the Amazon library, as it is not moving to Laminas.

- [#21](https://github.com/laminas/laminas-zendframework-bridge/pull/21) removes rewriting of the GCM and APNS libraries, as they are not moving to Laminas.

### Fixed

- [#21](https://github.com/laminas/laminas-zendframework-bridge/pull/21) fixes how the recaptcha and twitter library package and namespaces are rewritten.

## 0.3.1 - 2019-04-25

### Added

- [#20](https://github.com/laminas/laminas-zendframework-bridge/pull/20) provides an additional autoloader that is _prepended_ to the autoloader
  stack. This new autoloader will create class aliases for interfaces, classes,
  and traits referenced in type hints and class declarations, ensuring PHP is
  able to resolve them correctly during class_alias operations.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2019-04-12

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#16](https://github.com/laminas/laminas-zendframework-bridge/pull/16) removes the `RewriteRules::classRewrite()` method, as it is no longer
  needed due to internal refactoring.

### Fixed

- [#16](https://github.com/laminas/laminas-zendframework-bridge/pull/16) fixes how the rewrite rules detect the word `Zend` in subnamespaces and
  class names to be both more robust and simpler.

## 0.2.5 - 2019-04-11

### Added

- [#12](https://github.com/laminas/laminas-zendframework-bridge/pull/12) adds functionality for ensuring we alias namespaces and classes that
  include the word `Zend` in them; e.g., `Zend\Expressive\ZendView\ZendViewRendererFactory`
  will now alias to `Expressive\LaminasView\LaminasViewRendererFactory`.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.4 - 2019-04-11

### Added

- [#11](https://github.com/laminas/laminas-zendframework-bridge/pull/11) adds maps for the Expressive router adapter packages.

- [#10](https://github.com/laminas/laminas-zendframework-bridge/pull/10) adds a map for the Psr7Bridge package, as it used `Zend` within a subnamespace.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.3 - 2019-04-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#9](https://github.com/laminas/laminas-zendframework-bridge/pull/9) fixes the mapping for the Problem Details package.

## 0.2.2 - 2019-04-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Added a check that the discovered alias exists as a class, interface, or trait
  before attempting to call `class_alias()`.

## 0.2.1 - 2019-04-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#8](https://github.com/laminas/laminas-zendframework-bridge/pull/8) fixes mappings for each of zend-expressive-authentication-zendauthentication,
  zend-expressive-zendrouter, and zend-expressive-zendviewrenderer.

## 0.2.0 - 2019-04-01

### Added

- Nothing.

### Changed

- [#4](https://github.com/laminas/laminas-zendframework-bridge/pull/4) rewrites the autoloader to be class-based, via the class
  `Laminas\ZendFrameworkBridge\Autoloader`. Additionally, the new approach
  provides a performance boost by using a balanced tree algorithm, ensuring
  matches occur faster.

### Deprecated

- Nothing.

### Removed

- [#4](https://github.com/laminas/laminas-zendframework-bridge/pull/4) removes function aliasing. Function aliasing will move to the packages that
  provide functions.

### Fixed

- Nothing.

## 0.1.0 - 2019-03-27

### Added

- Adds an autoloader file that registers with `spl_autoload_register` a routine
  for aliasing legacy ZF class/interface/trait names to Laminas Project
  equivalents.

- Adds autoloader files for aliasing legacy ZF package functions to Laminas
  Project equivalents.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
