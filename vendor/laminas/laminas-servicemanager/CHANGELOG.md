# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.6.4 - 2021-02-03


-----

### Release Notes for [3.6.4](https://github.com/laminas/laminas-servicemanager/milestone/12)

3.6.x bugfix release (patch)

### 3.6.4

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [81: Bugfix: has alias causing endless loop](https://github.com/laminas/laminas-servicemanager/pull/81) thanks to @boesing

## 3.6.3 - 2021-01-25


-----

### Release Notes for [3.6.3](https://github.com/laminas/laminas-servicemanager/milestone/11)

3.6.x bugfix release (patch)

### 3.6.3

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [78: Regression in 3.6.x regarding aliased services in combination with non-shared services](https://github.com/laminas/laminas-servicemanager/pull/78) thanks to @boesing

## 3.6.2 - 2021-01-17


-----

### Release Notes for [3.6.2](https://github.com/laminas/laminas-servicemanager/milestone/9)

3.6.x bugfix release (patch)

### 3.6.2

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Bug

 - [76: Merge release 3.5.2 into 3.6.x](https://github.com/laminas/laminas-servicemanager/pull/76) thanks to @github-actions[bot]
 - [74: Fix invokables merging precedence in `3.6.x`](https://github.com/laminas/laminas-servicemanager/pull/74) thanks to @driehle

## 3.6.1 - 2021-01-10


-----

### Release Notes for [3.6.1](https://github.com/laminas/laminas-servicemanager/milestone/7)

3.6.x bugfix release (patch)

### 3.6.1

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### BC Break,Bug

- [71: Regression fix regarding initializers](https://github.com/laminas/laminas-servicemanager/pull/71) thanks to @boesing and @Grundik

## 3.6.0 - 2021-01-10

### Added

- [#51](https://github.com/laminas/laminas-servicemanager/pull/51) Adds PHP 8.0 support

- [#64](https://github.com/laminas/laminas-servicemanager/pull/64) Backports changes from upcoming 4.0 release and re-adds PHP 7.3 support.

### Changed

- [#40](https://github.com/laminas/laminas-servicemanager/pull/40) modifies the behavior of the `FactoryCreator` to alphabetize import statements.

- [zendframework/zend-servicemanager#221](https://github.com/zendframework/zend-servicemanager/pull/221) provides enormous performance improvements for each of the various mutator methods (`setAlias()`, `setFactory()`, etc.), `has()` lookups, and initial container configuration.

### Removed

- [#50](https://github.com/laminas/laminas-servicemanager/pull/50) Removes PHP support prior 7.4.0

- [zendframework/zend-servicemanager#197](https://github.com/zendframework/zend-servicemanager/pull/197) drops
  support for PHP versions prior to 7.1.

- [zendframework/zend-servicemanager#193](https://github.com/zendframework/zend-servicemanager/pull/193) drops
  support for HHVM.

### Fixed

- [zendframework/zend-servicemanager#230](https://github.com/zendframework/zend-servicemanager/pull/230) fixes a
  problem in detecting cyclic aliases, ensuring they are detected correctly.


-----

### Release Notes for [3.6.0](https://github.com/laminas/laminas-servicemanager/milestone/5)

Feature release (minor)

### 3.6.0

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **1**

#### Enhancement

- [64: Backport 4.x](https://github.com/laminas/laminas-servicemanager/pull/64) thanks to @boesing
- [63: Backport of 4afad5b660b8544bcf279aea09730d4f07f4fac0](https://github.com/laminas/laminas-servicemanager/pull/63) thanks to @boesing

## 3.5.2 - 2021-01-17


-----

### Release Notes for [3.5.2](https://github.com/laminas/laminas-servicemanager/milestone/6)

3.5.x bugfix release (patch)

### 3.5.2

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [75: backported testConfigureInvokablesTakePrecedenceOverFactories to 3.5.x](https://github.com/laminas/laminas-servicemanager/pull/75) thanks to @driehle

## 3.5.1 - 2020-12-01

### Release Notes for [3.5.1](https://github.com/laminas/laminas-servicemanager/milestone/4)

This is a full revert of release `3.5.0`.

`3.5.0` contained multiple backwards-incompatible (BC) breakages that were not
supposed to be released in a minor version, and therefore had to be reverted.

Due to unfortunate branch naming issues when migrating from the old `develop`
branch, `develop` was renamed `3.5.x`, but contained multiple BC breaks that
were fundamentally incompatible with the `3.0.0` and newer features.

Due to the quick response time, in order to not break further downstream systems
that yet have to receive the update, the `3.5.0` release was deleted, so that
it does not appear in your system if you accidentally run `composer update`.

Thanks to @fabiang for promptly detecting the issue and reporting it
at https://github.com/laminas/laminas-servicemanager/issues/59

### 3.5.1

- Total issues resolved: **1**
- Total pull requests resolved: **0**
- Total contributors: **1**

#### BC Break,Bug

 - [59: `ContainerInterface` reference was changed in a minor release](https://github.com/laminas/laminas-servicemanager/issues/59) thanks to @fabiang

## 3.4.1 - 2020-05-11

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#28](https://github.com/laminas/laminas-servicemanager/pull/28) provides updates to the `ConfigAbstractFactory` to ensure it works correctly under PHP 7.4 when the `config` service is represented by an `ArrayObject` or `ArrayAccess` implementation.

- [#35](https://github.com/laminas/laminas-servicemanager/pull/35) updates two exception messagers from the `ConfigAbstractFactory` to be more clear about what caused them.

## 3.4.0 - 2018-12-22

### Added

- [zendframework/zend-servicemanager#275](https://github.com/zendframework/zend-servicemanager/pull/275) Enables
  plugin managers to accept as a creation context PSR Containers not
  implementing Interop interface

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#268](https://github.com/zendframework/zend-servicemanager/pull/268) Fixes
  ReflectionBasedAbstractFactory trying to instantiate classes with private
  constructors

## 3.3.2 - 2018-01-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#243](https://github.com/zendframework/zend-servicemanager/pull/243) provides
  a fix to the `ReflectionBasedAbstractFactory` to resolve type-hinted arguments
  with default values to their default values if no matching type is found in
  the container.

- [zendframework/zend-servicemanager#233](https://github.com/zendframework/zend-servicemanager/pull/233) fixes a
  number of parameter annotations to reflect the actual types used.

## 3.3.1 - 2017-11-27

### Added

- [zendframework/zend-servicemanager#201](https://github.com/zendframework/zend-servicemanager/pull/201) and
  [zendframework/zend-servicemanager#202](https://github.com/zendframework/zend-servicemanager/pull/202) add
  support for PHP versions 7.1 and 7.2.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#206](https://github.com/zendframework/zend-servicemanager/pull/206) fixes an
  issue where by callables in `Class::method` notation were not being honored
  under PHP 5.6.

## 3.3.0 - 2017-03-01

### Added

- [zendframework/zend-servicemanager#180](https://github.com/zendframework/zend-servicemanager/pull/180) adds
  explicit support for PSR-11 (ContainerInterface) by requiring
  container-interop at a minimum version of 1.2.0, and adding a requirement on
  psr/container 1.0. `Laminas\ServiceManager\ServiceLocatorInterface` now
  explicitly extends the `ContainerInterface` from both projects.

  Factory interfaces still typehint against the container-interop variant, as
  changing the typehint would break backwards compatibility. Users can
  duck-type most of these interfaces, however, by creating callables or
  invokables that typehint against psr/container instead.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.2.1 - 2017-02-15

### Added

- [zendframework/zend-servicemanager#176](https://github.com/zendframework/zend-servicemanager/pull/176) adds
  the options `-i` or `--ignore-unresolved` to the shipped
  `generate-deps-for-config-factory` command. This flag allows it to build
  configuration for classes resolved by the `ConfigAbstractFactory` that
  typehint on interfaces, which was previously unsupported.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#174](https://github.com/zendframework/zend-servicemanager/pull/174) updates
  the `ConfigAbstractFactory` to allow the `config` service to be either an
  `array` or an `ArrayObject`; previously, only `array` was supported.

## 3.2.0 - 2016-12-19

### Added

- [zendframework/zend-servicemanager#146](https://github.com/zendframework/zend-servicemanager/pull/146) adds
  `Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory`, which enables a
  configuration-based approach to providing class dependencies when all
  dependencies are services known to the `ServiceManager`. Please see
  [the documentation](docs/book/config-abstract-factory.md) for details.
- [zendframework/zend-servicemanager#154](https://github.com/zendframework/zend-servicemanager/pull/154) adds
  `Laminas\ServiceManager\Tool\ConfigDumper`, which will introspect a given class
  to determine dependencies, and then create configuration for
  `Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory`, merging it with
  the provided configuration file. It also adds a vendor binary,
  `generate-deps-for-config-factory`, for generating these from the command
  line.
- [zendframework/zend-servicemanager#154](https://github.com/zendframework/zend-servicemanager/pull/154) adds
  `Laminas\ServiceManager\Tool\FactoryCreator`, which will introspect a given class
  and generate a factory for it. It also adds a vendor binary,
  `generate-factory-for-class`, for generating these from the command line.
- [zendframework/zend-servicemanager#153](https://github.com/zendframework/zend-servicemanager/pull/153) adds
  `Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory`. This
  class may be used as either a mapped factory or an abstract factory, and will
  use reflection in order to determine which dependencies to use from the
  container when instantiating the requested service, with the following rules:
  - Scalar values are not allowed, unless they have default values associated.
  - Values named `$config` type-hinted against `array` will be injected with the
    `config` service, if present.
  - All other array values will be provided an empty array.
  - Class/interface typehints will be pulled from the container.
- [zendframework/zend-servicemanager#150](https://github.com/zendframework/zend-servicemanager/pull/150) adds
  a "cookbook" section to the documentation, with an initial document detailing
  the pros and cons of abstract factory usage.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#106](https://github.com/zendframework/zend-servicemanager/pull/106) adds
  detection of multiple attempts to register the same instance or named abstract
  factory, using a previous instance when detected. You may still use multiple
  discrete instances, however.

## 3.1.2 - 2016-12-19

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#167](https://github.com/zendframework/zend-servicemanager/pull/167) fixes
  how exception codes are provided to ServiceNotCreatedException. Previously,
  the code was provided as-is. However, some PHP internal exception classes,
  notably PDOException, can sometimes return other values (such as strings),
  which can lead to fatal errors when instantiating the new exception. The
  patch provided casts exception codes to integers to prevent these errors.

## 3.1.1 - 2016-07-15

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#136](https://github.com/zendframework/zend-servicemanager/pull/136) removes
  several imports to classes in subnamespaces within the `ServiceManager`
  classfile, removing potential name resolution conflicts that occurred in edge
  cases when testing.

## 3.1.0 - 2016-06-01

### Added

- [zendframework/zend-servicemanager#103](https://github.com/zendframework/zend-servicemanager/pull/103) Allowing
  installation of `ocramius/proxy-manager` `^2.0` together with
  `zendframework/zend-servicemanager`.
- [zendframework/zend-servicemanager#103](https://github.com/zendframework/zend-servicemanager/pull/103) Disallowing
  test failures when running tests against PHP `7.0.*`.
- [zendframework/zend-servicemanager#113](https://github.com/zendframework/zend-servicemanager/pull/113) Improved performance
  when dealing with registering aliases and factories via `ServiceManager#setFactory()` and
  `ServiceManager#setAlias()`
- [zendframework/zend-servicemanager#120](https://github.com/zendframework/zend-servicemanager/pull/120) The
  `laminas/laminas-servicemanager` component now provides a
  `container-interop/container-interop-implementation` implementation

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#97](https://github.com/zendframework/zend-servicemanager/pull/97) Typo corrections
  in the delegator factories documentation.
- [zendframework/zend-servicemanager#98](https://github.com/zendframework/zend-servicemanager/pull/98) Using coveralls ^1.0
  for tracking test code coverage changes.

## 3.0.3 - 2016-02-02

### Added

- [zendframework/zend-servicemanager#89](https://github.com/zendframework/zend-servicemanager/pull/89) adds
  cyclic alias detection to the `ServiceManager`; it now raises a
  `Laminas\ServiceManager\Exception\CyclicAliasException` when one is detected,
  detailing the cycle detected.
- [zendframework/zend-servicemanager#95](https://github.com/zendframework/zend-servicemanager/pull/95) adds
  GitHub Pages publication automation, and moves the documentation to
  https://docs.laminas.dev/laminas-servicemanager/
- [zendframework/zend-servicemanager#93](https://github.com/zendframework/zend-servicemanager/pull/93) adds
  `Laminas\ServiceManager\Test\CommonPluginManagerTrait`, which can be used to
  validate that a plugin manager instance is ready for version 3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#90](https://github.com/zendframework/zend-servicemanager/pull/90) fixes
  several examples in the configuration chapter of the documentation, ensuring
  that the signatures are correct.
- [zendframework/zend-servicemanager#92](https://github.com/zendframework/zend-servicemanager/pull/92) ensures
  that alias resolution is skipped during configuration if no aliases are
  present, and forward-ports the test from [zendframework/zend-servicemanager#81](https://github.com/zendframework/zend-servicemanager/pull/81)
  to validate v2/v3 compatibility for plugin managers.

## 3.0.2 - 2016-01-24

### Added

- [zendframework/zend-servicemanager#64](https://github.com/zendframework/zend-servicemanager/pull/64) performance optimizations
  when dealing with alias resolution during service manager instantiation

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#62](https://github.com/zendframework/zend-servicemanager/pull/62)
  [zendframework/zend-servicemanager#64](https://github.com/zendframework/zend-servicemanager/pull/64) corrected benchmark assets signature
- [zendframework/zend-servicemanager#72](https://github.com/zendframework/zend-servicemanager/pull/72) corrected link to the Proxy Pattern Wikipedia
  page in the documentation
- [zendframework/zend-servicemanager#78](https://github.com/zendframework/zend-servicemanager/issues/78)
  [zendframework/zend-servicemanager#79](https://github.com/zendframework/zend-servicemanager/pull/79) creation context was not being correctly passed
  to abstract factories when using plugin managers
- [zendframework/zend-servicemanager#82](https://github.com/zendframework/zend-servicemanager/pull/82) corrected migration guide in the DocBlock of
  the `InitializerInterface`

## 3.0.1 - 2016-01-19

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-servicemanager#68](https://github.com/zendframework/zend-servicemanager/pull/68) removes
  the dependency on laminas-stdlib by inlining the `ArrayUtils::merge()` routine
  as a private method of `Laminas\ServiceManager\Config`.

### Fixed

- Nothing.

## 3.0.0 - 2016-01-11

First stable release of version 3 of laminas-servicemanager.

Documentation is now available at http://laminas-servicemanager.rtfd.org

### Added

- You can now map multiple key names to the same factory. It was previously
  possible in Laminas but it was not enforced by the `FactoryInterface` interface.
  Now the interface receives the `$requestedName` as the *second* parameter
  (previously, it was the third).

  Example:

  ```php
  $sm = new \Laminas\ServiceManager\ServiceManager([
      'factories'  => [
          MyClassA::class => MyFactory::class,
          MyClassB::class => MyFactory::class,
          'MyClassC'      => 'MyFactory' // This is equivalent as using ::class
      ],
  ]);

  $sm->get(MyClassA::class); // MyFactory will receive MyClassA::class as second parameter
  ```

- Writing a plugin manager has been simplified. If you have simple needs, you no
  longer need to implement the complete `validate` method.

  In versions 2.x, if your plugin manager only allows creating instances that
  implement `Laminas\Validator\ValidatorInterface`, you needed to write the
  following code:

  ```php
  class MyPluginManager extends AbstractPluginManager
  {
    public function validate($instance)
    {
        if ($instance instanceof \Laminas\Validator\ValidatorInterface) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin manager "%s" expected an instance of type "%s", but "%s" was received',
             __CLASS__,
             \Laminas\Validator\ValidatorInterface::class,
             is_object($instance) ? get_class($instance) : gettype($instance)
        ));
    }
  }
  ```

  In version 3, this becomes:

  ```php
  use Laminas\ServiceManager\AbstractPluginManager;
  use Laminas\Validator\ValidatorInterface;

  class MyPluginManager extends AbstractPluginManager
  {
      protected $instanceOf = ValidatorInterface::class;
  }
  ```

  Of course, you can still override the `validate` method if your logic is more
  complex.

  To aid migration, `validate()` will check for a `validatePlugin()` method (which
  was required in v2), and proxy to it if found, after emitting an
  `E_USER_DEPRECATED` notice prompting you to rename the method.

- A new method, `configure()`, was added, allowing full configuration of the
  `ServiceManager` instance at once. Each of the various configuration methods —
  `setAlias()`, `setInvokableClass()`, etc. — now proxy to this method.

- A new method, `mapLazyService($name, $class = null)`, was added, to allow
  mapping a lazy service, and as an analog to the other various service
  definition methods.

### Deprecated

- Nothing

### Removed

- Peering has been removed. It was a complex and rarely used feature that was
  misunderstood most of the time.

- Integration with `Laminas\Di` has been removed. It may be re-integrated later.

- `MutableCreationOptionsInterface` has been removed, as options can now be
  passed directly through factories.

- `ServiceLocatorAwareInterface` and its associated trait has been removed. It
  was an anti-pattern, and you are encouraged to inject your dependencies in
  factories instead of injecting the whole service locator.

### Changed/Fixed

v3 of the ServiceManager component is a completely rewritten, more efficient
implementation of the service locator pattern. It includes a number of breaking
changes, outlined in this section.

- You no longer need a `Laminas\ServiceManager\Config` object to configure the
  service manager; you can pass the configuration array directly instead.

  In version 2.x:

  ```php
  $config = new \Laminas\ServiceManager\Config([
      'factories'  => [...]
  ]);

  $sm = new \Laminas\ServiceManager\ServiceManager($config);
  ```

  In Laminas 3.x:

  ```php
  $sm = new \Laminas\ServiceManager\ServiceManager([
      'factories'  => [...]
  ]);
  ```

  `Config` and `ConfigInterface` still exist, however, but primarily for the
  purposes of codifying and aggregating configuration to use.

- `ConfigInterface` has two important changes:
  - `configureServiceManager()` now **must** return the updated service manager
    instance.
  - A new method, `toArray()`, was added, to allow pulling the configuration in
    order to pass to a ServiceManager or plugin manager's constructor or
    `configure()` method.

- Interfaces for `FactoryInterface`, `DelegatorFactoryInterface` and
  `AbstractFactoryInterface` have changed. All are now directly invokable. This
  allows a number of performance optimization internally.

  Additionally, all signatures that accepted a "canonical name" argument now
  remove it.

  Most of the time, rewriting a factory to match the new interface implies
  replacing the method name by `__invoke`, and removing the canonical name
  argument if present.

  For instance, here is a simple version 2.x factory:

  ```php
  class MyFactory implements FactoryInterface
  {
      function createService(ServiceLocatorInterface $sl)
      {
          // ...
      }
  }
  ```

  The equivalent version 3 factory:

  ```php
  class MyFactory implements FactoryInterface
  {
      function __invoke(ServiceLocatorInterface $sl, $requestedName)
      {
          // ...
      }
  }
  ```

  Note another change in the above: factories also receive a second parameter,
  enforced through the interface, that allows you to easily map multiple service
  names to the same factory.

  To provide forwards compatibility, the original interfaces have been retained,
  but extend the new interfaces (which are under new namespaces). You can implement
  the new methods in your existing v2 factories in order to make them forwards
  compatible with v3.

- The for `AbstractFactoryInterface` interface renames the method `canCreateServiceWithName()`
  to `canCreate()`, and merges the `$name` and `$requestedName` arguments.

- Plugin managers will now receive the parent service locator instead of itself
  in factories. In version 2.x, you needed to call the method
  `getServiceLocator()` to retrieve the parent (application) service locator.
  This was confusing, and not IDE friendly as this method was not enforced
  through the interface.

  In version 2.x, if a factory was set to a service name defined in a plugin manager:

  ```php
  class MyFactory implements FactoryInterface
  {
      function createService(ServiceLocatorInterface $sl)
      {
          // $sl is actually a plugin manager

          $parentLocator = $sl->getServiceLocator();

          // ...
      }
  }
  ```

  In version 3:

  ```php
  class MyFactory implements FactoryInterface
  {
      function __invoke(ServiceLocatorInterface $sl, $requestedName)
      {
          // $sl is already the main, parent service locator. If you need to
          // retrieve the plugin manager again, you can retrieve it through the
          // servicelocator:
          $pluginManager = $sl->get(MyPluginManager::class);
          // ...
      }
  }
  ```

  In practice, this should reduce code, as dependencies often come from the main
  service locator, and not the plugin manager itself.

  To assist in migration, the method `getServiceLocator()` was added to `ServiceManager`
  to ensure that existing factories continue to work; the method emits an `E_USER_DEPRECATED`
  message to signal developers to update their factories.

- `PluginManager` now enforces the need for the main service locator in its
  constructor. In v2.x, people often forgot to set the parent locator, which led
  to bugs in factories trying to fetch dependencies from the parent locator.
  Additionally, plugin managers now pull dependencies from the parent locator by
  default; if you need to pull a peer plugin, your factories will now need to
  pull the corresponding plugin manager first.

  If you omit passing a service locator to the constructor, your plugin manager
  will continue to work, but will emit a deprecation notice indicatin you
  should update your initialization code.

- It's so fast now that your app will fly!

## 2.7.0 - 2016-01-11

### Added

- [zendframework/zend-servicemanager#60](https://github.com/zendframework/zend-servicemanager/pull/60) adds
  forward compatibility features for `AbstractPluingManager` and introduces
  `InvokableFactory` to help forward migration to version 3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#46](https://github.com/zendframework/zend-servicemanager/pull/46) updates
  the exception hierarchy to inherit from the container-interop exceptions.
  This ensures that all exceptions thrown by the component follow the
  recommendations of that project.
- [zendframework/zend-servicemanager#52](https://github.com/zendframework/zend-servicemanager/pull/52) fixes
  the exception message thrown by `ServiceManager::setFactory()` to remove
  references to abstract factories.

## 2.6.0 - 2015-07-23

### Added

- [zendframework/zend-servicemanager#4](https://github.com/zendframework/zend-servicemanager/pull/4) updates the
    `ServiceManager` to [implement the container-interop interface](https://github.com/container-interop/container-interop),
    allowing interoperability with applications that consume that interface.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-servicemanager#3](https://github.com/zendframework/zend-servicemanager/pull/3) properly updates the
  codebase to PHP 5.5, by taking advantage of the default closure binding
  (`$this` in a closure is the invoking object when created within a method). It
  also removes several `@requires PHP 5.4.0` annotations.
