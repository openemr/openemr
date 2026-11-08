# Service Configuration

This directory contains PSR-11 container configuration files for dependency injection.

## Background

Dependency injection makes it easier to write robust and testable code by decoupling specific implementations.

For more information:

- [Inversion of Control Containers and the Dependency Injection pattern](https://martinfowler.com/articles/injection.html) - Martin Fowler's canonical explanation
- [PHP The Right Way: Dependency Injection](https://phptherightway.com/#dependency_injection) - quick practical intro
- [PSR-11: Container Interface](https://www.php-fig.org/psr/psr-11/) - the container interface spec used here

## Usage

The DI container is **not yet fully integrated** into OpenEMR.
Once it is, here's how things will work:

- Application entrypoints (e.g. API endpoints and CLI tools) will be managed by the container
- The container will inject their dependencies when fetched
  - Dependencies may have their own dependencies, etc. This can go as deep as needed, as long as it doesn't go in a circle
  - Dependencies can include configuration if needed
- The dispatching system (router, kernel, CLI Application, etc) will `get()` the dependency from the container and feed it the request to process

Only the application entrypoints should be directly interacting with the container.
The container **will not** be passed around to various services.

## Requirements

Services added here **must** support constructor-based dependency injection. This means:

- All dependencies are declared as constructor parameters
- No use of `$GLOBALS`, `OEGlobalsBag`, service locators, or static singletons to obtain dependencies
- No side effects during construction
- The class itself **must not** expose a singleton

### On Singletons

By default, the container itself will reuse services as they are requested.
Like (just about) all PHP code, there is still a fresh instance per request.

In the rare case that this behavior is inappropriate for the service, the definition can be wrapped in a `factory()` call.
Generally when this is true, it's a sign that the class has too much internal state.

## Adding Services

See the [container library documentation](https://github.com/Firehed/container#readme) for syntax and usage examples.

While there's no technical requirement around organization, config files should be organized into logical groups:

- `env.php` - Environment/configuration values
- `database.php` - Database-related services
- `psr.php` - PSR interface-to-implementation mappings
- `services.php` - General application services

If code is not a good fit, other files can be added.
Services will probably get large, and it can be split apart if and when needed.

## Migrating Legacy Code

Many existing services in OpenEMR do not yet follow DI principles. Before wiring a legacy service into the container:

1. Refactor to accept dependencies via constructor
2. Remove direct usage of `$GLOBALS`, `OEGlobalsBag`, or various singletons for service access
3. Ensure the service can be instantiated without side effects

Do not add services that still rely on global state or service locators. Migrate them first.
