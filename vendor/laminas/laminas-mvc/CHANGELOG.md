# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.2.0 - 2020-12-14

### Added

- [#69](https://github.com/laminas/laminas-mvc/pull/69) Adds PHP 8.0 support
- [zendframework/zend-mvc#282](https://github.com/zendframework/zend-mvc/pull/282) Adds a full
  controller namespace as additional event manager identifier for
  implementations of AbstractController

### Deprecated

- [#51](https://github.com/laminas/laminas-mvc/pull/51) Deprecates MiddlewareListener. Optional support for dispatching middleware, middleware pipes and handlers is moved to laminas/laminas-mvc-middleware package

### Removed

- [#69](https://github.com/laminas/laminas-mvc/pull/69) Removes PHP support prior 7.3.0


-----

### Release Notes for [3.2.0](https://github.com/laminas/laminas-mvc/milestone/1)



### 3.2.0

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Enhancement

 - [69: PHP 8.0 support](https://github.com/laminas/laminas-mvc/pull/69) thanks to @snapshotpl

 - [51: Deprecate middleware listener](https://github.com/laminas/laminas-mvc/pull/51) thanks to @Xerkus

## 3.1.1 - 2017-11-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#247](https://github.com/zendframework/zend-mvc/pull/247) fixes bug in
  controller plugin Forward, introduced in 3.1.0, where problem listeners were
  not detached for forwarded controller dispatch

## 3.1.0 - 2017-05-01

### Added

- [zendframework/zend-mvc#217](https://github.com/zendframework/zend-mvc/pull/217) adds support for
  middleware _pipelines_ when using the `MiddlewareListener`. You can now
  specify an _array` of middleware for the `middleware` attached to a route, and
  it will be marshaled into a `Laminas\Stratigility\MiddlewarePipe` instance, using
  the same rules as if you specified a single middleware.

- [zendframework/zend-mvc#236](https://github.com/zendframework/zend-mvc/pull/236) adds the ability to
  attach dispatch listeners to middleware when using the `MiddlewareListener`.
  Attach shared events to the class identifier `Laminas\Mvc\Controller\MiddlewareController`.
  This feature helps ensure that listeners that should run for every controller
  (e.g., authentication or authorization listeners) will run even for
  middleware.

- [zendframework/zend-mvc#231](https://github.com/zendframework/zend-mvc/pull/231) adds a
  `composer.json` suggestion for zendframework/zend-paginator.

- [zendframework/zend-mvc#232](https://github.com/zendframework/zend-mvc/pull/232) adds a
  `composer.json` suggestion for zendframework/zend-log.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-mvc#211](https://github.com/zendframework/zend-mvc/pull/211) Removed unused
  laminas-servicemanager v2 and laminas-eventmanager v2 compatibility code since
  laminas-mvc requires v3 of those components.

### Fixed

- [zendframework/zend-mvc#237](https://github.com/zendframework/zend-mvc/pull/237) fixes the return
  annotations for `HttpDefaultRenderingStrategyFactory::createService` and
  `injectLayoutTemplate()` to be `HttpDefaultRenderingStrategy` and not
  `HttpDefaultRendererStrategy`.

## 3.0.4 - 2016-12-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#210](https://github.com/zendframework/zend-mvc/pull/210) copies the 
  `RouteMatch` and its parameters to the PSR-7 `ServerRequest` object so that
  they are available to middleware.

## 3.0.3 - 2016-08-29

### Added

- [zendframework/zend-mvc#198](https://github.com/zendframework/zend-mvc/pull/198) adds a factory for
  the `SendResponseListener`, to ensure that it is injected with an event
  manager instance from the outset; this fixes issues with delegator factories
  that registered listeners with it in previous versions.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#184](https://github.com/zendframework/zend-mvc/pull/184) provides a
  performance optimization for `DELETE` requests to `AbstractRestfulController`
  instances.
- [zendframework/zend-mvc#187](https://github.com/zendframework/zend-mvc/pull/187) removes a typehint
  for `Exception` from an argument in
  `DispatchListener::marshalControllerNotFoundEvent()`, allowing it to be used
  with PHP 7 `Throwable` instances.

## 3.0.2 - 2016-06-30

### Added

- [zendframework/zend-mvc#163](https://github.com/zendframework/zend-mvc/pull/163) adds support to the
  `AcceptableViewModelSelector` plugin for controller maps in the `view_manager`
  configuration in the format:

  ```php
  [
      'ControllerClassName' => 'view/name',
  ]
  ```

  This fixes an issue observed when running with Laminas API Tools.

- [zendframework/zend-mvc#163](https://github.com/zendframework/zend-mvc/pull/163) adds support to the
  `InjectTemplateListener` for specifying whether or not to prefer the
  controller matched during routing via routing configuration:

  ```php
  'route-name' => [
      /* ... */
      'options' => [
          /* ... */
          'defaults' => [
              /* ... */
              'prefer_route_match_controller' => true,
          ],
      ],
  ],
  ```

  This allows actions that might otherwise skip injection of the template
  to force the injection.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#161](https://github.com/zendframework/zend-mvc/pull/161) fixes the
  `DispatchListener::marshalBadControllerEvent()` method to allow either
  `Throwable` or `Exception` types for the `$exception` argument.

## 3.0.1 - 2016-06-23

### Added

- [zendframework/zend-mvc#165](https://github.com/zendframework/zend-mvc/pull/165) adds a new
  controller factory, `LazyControllerAbstractFactory`, that provides a
  Reflection-based approach to instantiating controllers. You may register it
  either as an abstract factory or as a named factory in your controller
  configuration:

  ```php
  'controllers' => [
      'abstract_factories' => [
          'Laminas\Mvc\Controller\LazyControllerAbstractFactory`,
      ],
      'factories' => [
          'MyModule\Controller\FooController' => 'Laminas\Mvc\Controller\LazyControllerAbstractFactory`,
      ],
  ],
  ```

  The factory uses the typehints to lookup services in the container, using
  aliases for well-known services such as the `FilterManager`,
  `ValidatorManager`, etc. If an `array` typehint is used with a `$config`
  parameter, the `config` service is injected; otherwise, an empty array is
  provided. For all other types, a null value is injected.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.0 - 2016-05-31

New major version! Please see:

- [doc/book/migration/to-v3-0.md](doc/book/migration/to-v3-0.md)

for full details on how to migrate your v2 application.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-mvc#99](https://github.com/zendframework/zend-mvc/pull/99) removes all router
  functionality (everything in the `Laminas\Mvc\Router` namespace. This
  functionality is now provided by the [laminas-router](https://docs.laminas.dev/laminas-router/)
  component, which becomes a requirement of laminas-mvc. The removal also includes
  all service factories related to routing, as they are provided by laminas-router.
- [zendframework/zend-mvc#99](https://github.com/zendframework/zend-mvc/pull/99) removes all
  console-related functionality, including the `AbstractConsoleController`, the
  `CreateConsoleNotFoundModel` controller plugin, the `ConsoleResponseSender`,
  and all classes under the `Laminas\Mvc\View\Console` namespace; these are now
  provided by the [laminas-mvc-console](https://docs.laminas.dev/laminas-mvc-console/)
  component. (That component also includes console-specific routes, which were
  removed from laminas-router.) All service factories related to console
  functionality are also now provided by laminas-mvc-console.
- [zendframework/zend-mvc#104](https://github.com/zendframework/zend-mvc/pull/104) removes the `prg()`
  plugin. It can now be installed separately via the
  zendframework/zend-mvc-plugin-prg package.
- [zendframework/zend-mvc#108](https://github.com/zendframework/zend-mvc/pull/108) removes the
  `fileprg()`, `flashMessenger()`, and `identity()` plugins. These can be
  installed via, respectively, the laminas/laminas-mvc-plugin-fileprg,
  laminas/laminas-mvc-plugin-flashmessenger, and
  zendframework/zend-mvc-plugin-identity packages.
- [zendframework/zend-mvc#110](https://github.com/zendframework/zend-mvc/pull/110) removes the
  internationalization functionality from the component, including factories for
  the translator and translator loader manager. This functionality is
  now provided by the [laminas-i18n](https://docs.laminas.dev/laminas-i18n/)
  and [laminas-mvc-i18n](https://docs.laminas.dev/laminas-mvc-i18n/) packages;
  installing `zendframework/zend-mvc-i18n` will restore i18n functionality in
  your application.
- [zendframework/zend-mvc#115](https://github.com/zendframework/zend-mvc/pull/115) removes the
  requirement for laminas-filter in the `InjectTemplateListener` by inlining the
  logic from `Laminas\Filter\Word\CamelCaseToDash`.
- [zendframework/zend-mvc#116](https://github.com/zendframework/zend-mvc/pull/116) removes the
  functionality related to integrating laminas-servicemanager and laminas-di. If you
  used this functionality previously, it is now available via a separate
  package, [laminas-servicemanager-di](https://docs.laminas.dev/laminas-servicemanager-di/]).
- [zendframework/zend-mvc#117](https://github.com/zendframework/zend-mvc/pull/117) removes the
  functionality related to exposing and configuring the laminas-filter
  `FilterPluginManager`. That functionality is now exposed directly by the
  laminas-filter component.
- [zendframework/zend-mvc#118](https://github.com/zendframework/zend-mvc/pull/118) removes the
  functionality related to exposing and configuring the laminas-validator
  `ValidatorPluginManager`. That functionality is now exposed directly by the
  laminas-validator component.
- [zendframework/zend-mvc#119](https://github.com/zendframework/zend-mvc/pull/119) removes the
  functionality related to exposing and configuring the laminas-serializer
  `SerializerAdapterManager`. That functionality is now exposed directly by the
  laminas-serializer component.
- [zendframework/zend-mvc#120](https://github.com/zendframework/zend-mvc/pull/120) removes the
  functionality related to exposing and configuring the laminas-hydrator
  `HydratorManager`. That functionality is now exposed directly by the
  laminas-hydrator component.
- [zendframework/zend-mvc#54](https://github.com/zendframework/zend-mvc/pull/54) removes the
  `$configuration` argument (first required argument) from the
  `Laminas\Mvc\Application` constructor. If you were directly instantiating an
  `Application` instance previously (whether in your bootstrap, a factory, or
  tests), you will need to update how you instantiate the instance. (The
  argument was removed as the value was never used.)
- [zendframework/zend-mvc#121](https://github.com/zendframework/zend-mvc/pull/121) removes the
  functionality related to exposing and configuring the laminas-log
  `ProcessorPluginManager` and `WriterPluginManager`. That functionality is now
  exposed directly by the laminas-log component (with the addition of exposing the
  `FilterPluginManager` and `FormatterPluginManager` as well).
- [zendframework/zend-mvc#123](https://github.com/zendframework/zend-mvc/pull/123) removes the
  functionality related to exposing and configuring the laminas-inputfilter
  `InputFilterManager`. That functionality is now exposed directly by the
  laminas-inputfilter component.
- [zendframework/zend-mvc#124](https://github.com/zendframework/zend-mvc/pull/124) removes the
  functionality related to exposing and configuring laminas-form, including the
  `FormElementManager`, `FormAnnotationBuilder`, and the
  `FormAbstractServiceFactory`. The functionality is now exposed directly by the
  laminas-form component.
- [zendframework/zend-mvc#125](https://github.com/zendframework/zend-mvc/pull/125) removes the
  functionality from the `ViewHelperManager` factory for fetching configuration
  classes from other components and using them to configure the instance. In all
  cases, this is now done by the components themselves.
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) removes the
  `ControllerLoaderFactory`, and the `ControllerLoader` service alias; use
  `ControllerManagerFactory` and `ControllerManager`, respectively, instead.
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) removes
  `Laminas\Mvc\View\SendResponseListener`; use `Laminas\Mvc\SendResponseListener`
  instead.
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) removes
  `Application::send()`, which has been a no-op since 2.2.
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) removes
  `DispatchListener::marshallControllerNotFoundEvent()`, which has proxied to
  `marshalControllerNotFoundEvent()` since 2.2.
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) removes
  the `ServiceLocatorAwareInterface` implementation
  (`setServiceLocator()`/`getServiceLocator()` methods) from
  `AbstractController`. You will need to inject your dependencies specifically
  going forward.
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) removes
  the `ServiceLocatorAwareInterface` initializers defined in
  `Laminas\Mvc\Service\ServiceManagerConfig` and
  `Laminas\Mvc\Controller\ControllerManager`. You will need to inject your
  dependencies specifically going forward.
- [zendframework/zend-mvc#139](https://github.com/zendframework/zend-mvc/pull/139) removes support for
  pseudo-module template resolution using the `__NAMESPACE__` routing
  configuration option, as it often led to conflicts when multiple modules
  shared a common top-level namespace. Auto-resolution now always takes into
  account the full namespace (minus the `Controller` segment).

### Fixed

- [zendframework/zend-mvc#113](https://github.com/zendframework/zend-mvc/pull/113) updates
  `AbstractRestfulController` to make usage of laminas-json for deserializing JSON
  requests optional. `json_decode()` is now used by default, falling back to
  `Laminas\Json\Json::decode()` if it is available. If neither are available, an
  exception is now thrown.
- [zendframework/zend-mvc#115](https://github.com/zendframework/zend-mvc/pull/115) and
  [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) update the
  dependency list, per https://github.com/laminas/maintainers/wiki/laminas-mvc-v3-refactor:-reduce-components#required-components,
  to do the following:
  - Makes the following components required:
    - laminas-http
    - laminas-modulemanager
    - laminas-router
    - laminas-view
  - Makes the following components optional:
    - laminas-json
    - laminas-psr7bridge
  - And pares the suggestion list down to:
    - laminas-mvc-console
    - laminas-mvc-i18n
    - laminas-mvc-plugin-fileprg
    - laminas-mvc-plugin-flashmessenger
    - laminas-mvc-plugin-identity
    - laminas-mvc-plugin-prg
    - laminas-servicemanager-di
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) bumps the minimum
  supported version of laminas-eventmanager, laminas-servicemanager, and laminas-stdlib
  to their v3 releases.
- [zendframework/zend-mvc#128](https://github.com/zendframework/zend-mvc/pull/128) bumps the minimum
  supported PHP version to 5.6.

## 2.7.8 - 2016-05-31

### Added

- [zendframework/zend-mvc#138](https://github.com/zendframework/zend-mvc/pull/138) adds support for
  PHP 7 `Throwable`s within each of:
  - `DispatchListener`
  - `MiddlewareListener`
  - The console `RouteNotFoundStrategy` and `ExceptionStrategy`
  - The HTTP `DefaultRenderingStrategy` and `RouteNotFoundStrategy`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.7 - 2016-04-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#122](https://github.com/zendframework/zend-mvc/pull/122) fixes the
  `FormAnnotationBuilderFactory` to use the container's `get()` method instead
  of `build()` to retrieve the event manager instance.

## 2.7.6 - 2016-04-06

### Added

- [zendframework/zend-mvc#94](https://github.com/zendframework/zend-mvc/pull/94) adds a documentation
  recipe for using middleware withing MVC event listeners.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#107](https://github.com/zendframework/zend-mvc/pull/107) fixes an incorrect
  import statement in the `DiStrictAbstractServiceFactoryFactory` that prevented
  it from working.
- [zendframework/zend-mvc#112](https://github.com/zendframework/zend-mvc/pull/112) fixes how the
  `Forward` plugin detects and detaches event listeners to ensure it works
  against either v2 or v3 releases of laminas-eventmanager.

## 2.7.5 - 2016-04-06

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#111](https://github.com/zendframework/zend-mvc/pull/111) fixes a bug in how
  the `ConsoleExceptionStrategyFactory` whereby it was overwriting the default
  exception message template with an empty string when no configuration for it
  was provided.

## 2.7.4 - 2016-04-03

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#114](https://github.com/zendframework/zend-mvc/pull/114) fixes an issue in
  the `ServiceLocatorAware` initializer whereby plugin manager instances were
  falsely identified as the container instance when under laminas-servicemanager v2.

## 2.7.3 - 2016-03-08

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#97](https://github.com/zendframework/zend-mvc/pull/97) re-introduces the
  `ServiceManager` factory definition inside `ServiceManagerConfig`, to ensure
  backwards compatibility.

## 2.7.2 - 2016-03-08

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#95](https://github.com/zendframework/zend-mvc/pull/95) re-introduces the
  various laminas-di aliases and factories in `Laminas\Mvc\Service\ServiceListenerFactory`,
  which were accidently removed in the 2.7.0 release.
- [zendframework/zend-mvc#96](https://github.com/zendframework/zend-mvc/pull/96) fixes shared event
  detachment/attachment within the `Forward` plugin to work with both v2 and v3
  of laminas-eventmanager.
- [zendframework/zend-mvc#93](https://github.com/zendframework/zend-mvc/pull/93) ensures that the
  Console `Catchall` route factory will not fail when the `defaults` `$options`
  array key is missing.
- [zendframework/zend-mvc#43](https://github.com/zendframework/zend-mvc/pull/43) updates the
  `AbstractRestfulController` to ensure it can accept textual (e.g., XML, YAML)
  data.
- [zendframework/zend-mvc#79](https://github.com/zendframework/zend-mvc/pull/79) updates the
  continuous integration configuration to ensure we test against lowest and
  highest accepted dependencies, and those in the current lockfile.

## 2.7.1 - 2016-03-02

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#88](https://github.com/zendframework/zend-mvc/pull/88) addresses backwards
  compatibility concerns raised by users due to the new deprecation notices
  emitted by `ServiceLocatorAware` initializers; in particular, all
  `AbstractController` implementations were raising a deprecation wen first
  pulled from the `ControllerManager`.
  
  At this time, notices are now only raised in the following conditions:

  - When a non-controller, non-plugin manager, `ServiceLocatorAware` instance
    is detected.
  - When a plugin manager instance is detected that is `ServiceLocatorAware` and
    does not have a composed service locator. In this situation, the deprecation
    notice indicates that the factory for the plugin manager should be updated
    to inject the service locator via the constructor.
  - For controllers that do not extend `AbstractController` but do implement
    `ServiceLocatorAware`.
  - When calling `getServiceLocator()` from within an `AbstractController`
    extension; this properly calls out the practice that should be avoided and
    which requires updates to the controller.

## 2.7.0 - 2016-03-01

### Added

- [zendframework/zend-mvc#31](https://github.com/zendframework/zend-mvc/pull/31) adds three new
  optional arguments to the `Laminas\Mvc\Application` constructor: an EventManager
  instance, a Request instance, and a Response instance.
- [zendframework/zend-mvc#36](https://github.com/zendframework/zend-mvc/pull/36) adds more than a
  dozen service factories, primarily to separate conditional factories into
  discrete factories.
- [zendframework/zend-mvc#32](https://github.com/zendframework/zend-mvc/pull/32) adds
  `Laminas\Mvc\MiddlewareListener`, which allows dispatching PSR-7-based middleware
  implementing the signature `function (ServerRequestInterface $request,
  ResponseInterface $response)`. To dispatch such middleware, point the
  `middleware` "default" for a given route to a service name or callable that
  will resolve to the middleware:

  ```php
  [ 'router' => 'routes' => [
      'path' => [
          'type' => 'Literal',
          'options' => [
              'route' => '/path',
              'defaults' => [
                  'middleware' => 'ServiceNameForPathMiddleware',
              ],
          ],
      ],
  ]
  ```

  This new listener listens at the same priority as the `DispatchListener`, but,
  due to being registered earlier, will invoke first; if the route match does
  not resolve to middleware, it will fall through to the original
  `DispatchListener`, allowing normal Laminas-style controller dispatch.
- [zendframework/zend-mvc#84](https://github.com/zendframework/zend-mvc/pull/84) publishes the
  documentation to https://docs.laminas.dev/laminas-mvc/

### Deprecated

- Two initializers registered by `Laminas\Mvc\Service\ServiceManagerConfig` are now
  deprecated, and will be removed starting in version 3.0:
  - `ServiceManagerAwareInitializer`, which injects classes implementing
    `Laminas\ServiceManager\ServiceManagerAwareInterface` with the service manager
    instance. Users should create factories for such classes that directly
    inject their dependencies instead.
  - `ServiceLocatorAwareInitializer`, which injects classes implementing
    `Laminas\ServiceManager\ServiceLocatorAwareInterface` with the service manager
    instance. Users should create factories for such classes that directly
    inject their dependencies instead.

### Removed

- `Laminas\Mvc\Controller\AbstractController` no longer directly implements
  `Laminas\ServiceManager\ServiceLocatorAwareInterface`, but still implements the
  methods defined in that interface. This was done to provide
  forwards-compatibility, as laminas-servicemanager v3 no longer defines the
  interface. All initializers that do `ServiceLocatorInterface` injection were
  updated to also inject when just the methods are present.

### Fixed

- [zendframework/zend-mvc#31](https://github.com/zendframework/zend-mvc/pull/31) and
  [zendframework/zend-mvc#76](https://github.com/zendframework/zend-mvc/pull/76) update the component
  to be forwards-compatible with laminas-eventmanager v3.
- [zendframework/zend-mvc#36](https://github.com/zendframework/zend-mvc/pull/36),
  [zendframework/zend-mvc#76](https://github.com/zendframework/zend-mvc/pull/76),
  [zendframework/zend-mvc#80](https://github.com/zendframework/zend-mvc/pull/80),
  [zendframework/zend-mvc#81](https://github.com/zendframework/zend-mvc/pull/81), and
  [zendframework/zend-mvc#82](https://github.com/zendframework/zend-mvc/pull/82) update the component
  to be forwards-compatible with laminas-servicemanager v3. Several changes were
  introduced to support this effort:
  - Added a `RouteInvokableFactory`, which can act as either a
    `FactoryInterface` or `AbstractFactoryInterface` for loading invokable route
    classes, including by fully qualified class name. This is registered as an
    abstract factory by default with the `RoutePluginManager`.
  - The `DispatchListener` now receives the controller manager instance at
    instantiation.
  - The `ViewManager` implementations were updated, and most functionality
    within separated into discrete factories.

## 2.6.3 - 2016-02-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#74](https://github.com/zendframework/zend-mvc/pull/74) fixes the
  `FormAnnotationBuilderFactory`'s usage of the
  `FormElementManager::injectFactory()` method to ensure it works correctly on
  all versions.

## 2.6.2 - 2016-02-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#71](https://github.com/zendframework/zend-mvc/pull/71) fixes the
  `ViewHelperManagerFactory` to be backwards-compatible with v2 by ensuring that
  the factories for each of the `url`, `basepath`, and `doctype` view helpers
  are registered using the fully qualified class names present in
  `Laminas\View\HelperPluginManager`; these changes ensure requests for these
  helpers resolve to these override factories, instead of the
  `InvokableFactory`.

## 2.6.1 - 2016-02-16

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#69](https://github.com/zendframework/zend-mvc/pull/69) largely reverts
  [zendframework/zend-mvc#30](https://github.com/zendframework/zend-mvc/pull/30), having the component
  utilize the `HydratorPluginManager` from laminas-stdlib 2.7.5. This was done to
  provide backwards compatibility; while laminas-stdlib Hydrator types can be used
  in place of laminas-hydrator types, the reverse is not true.

  You can make your code forwards-compatible with version 3, where the
  `HydratorPluginManager` will be pulled from laminas-hydrator, by updating your
  typehints to use the laminas-hydrator classes instead of those from laminas-stdlib;
  the instances returned from the laminas-stdlib `HydratorPluginManager`, because
  they extend those from laminas-hydrator, remain compatible. 

## 2.6.0 - 2015-09-22

### Added

- [zendframework/zend-mvc#30](https://github.com/zendframework/zend-mvc/pull/30) updates the component
  to use laminas-hydrator for hydrator functionality; this provides forward
  compatibility with laminas-hydrator, and backwards compatibility with
  hydrators from older versions of laminas-stdlib.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.3 - 2015-09-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#29](https://github.com/zendframework/zend-mvc/pull/29) updates the
  laminas-stdlib dependency to reference `>=2.5.0,<2.7.0` to ensure hydrators
  will work as expected following extraction of hydrators to the laminas-hydrator
  repository.

## 2.5.2 - 2015-09-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mvc#27](https://github.com/zendframework/zend-mvc/pull/27) fixes a condition
  where non-view model results from controllers could cause errors to be
  raisedin the `DefaultRenderingStrategy`.
