# OpenEMR Development Guide

## Project Structure

```
/src/              - Modern PSR-4 code (OpenEMR\ namespace)
/library/          - Legacy procedural PHP code
/interface/        - Web UI controllers and templates
/templates/        - Smarty/Twig templates
/tests/            - Test suite (unit, e2e, api, services)
/sql/              - Database schema and migrations
/public/           - Static assets
/docker/           - Docker configurations
/modules/          - Custom and third-party modules
```

## Technology Stack

- **PHP:** 8.2+ required
- **Backend:** Laminas MVC, Symfony components
- **Templates:** Twig 3.x (modern), Smarty 4.5 (legacy)
- **Frontend:** Angular 1.8, jQuery 3.7, Bootstrap 4.6
- **Build:** Gulp 4, SASS
- **Database:** MySQL via Doctrine DBAL 4.x (ADODB surface API for legacy code)
- **Testing:** PHPUnit 11, Jest 29
- **Static Analysis:** PHPStan level 10, Rector, custom rules in `tests/PHPStan/Rules/`

## Local Development

See `CONTRIBUTING.md` for full setup instructions. Quick start:

```bash
cd docker/development-easy
docker compose up --detach --wait
```

- **App URL:** http://localhost:8300/ or https://localhost:9300/
- **Login:** `admin` / `pass`
- **phpMyAdmin:** http://localhost:8310/

## Testing

Tests run inside Docker via devtools. Run from `docker/development-easy/`:

```bash
# Run all tests
docker compose exec openemr /root/devtools clean-sweep-tests

# Individual test suites
docker compose exec openemr /root/devtools unit-test
docker compose exec openemr /root/devtools api-test
docker compose exec openemr /root/devtools e2e-test
docker compose exec openemr /root/devtools services-test

# View PHP error log
docker compose exec openemr /root/devtools php-log
```

**Tip:** Install [openemr-cmd](https://github.com/openemr/openemr-devops/tree/master/utilities/openemr-cmd)
for shorter commands (e.g., `openemr-cmd ut` for unit tests) from any directory.

### Isolated tests (no Docker required)

Isolated tests run on the host without a database or Docker:

```bash
composer phpunit-isolated        # Run all isolated tests
```

### Data providers: mark as `@codeCoverageIgnore`

PHPUnit data provider methods execute *before* coverage instrumentation
starts, so their lines never register as "hit" even though they run on every
test. Without an explicit ignore they show up as uncovered in Codecov
patch-coverage reports and drag the number down for no real reason (a
10-case data provider = 10 spurious "missing" lines on every new test).

Annotate every data provider with the standard comment used elsewhere in
this repo (see `tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php`):

```php
/**
 * @return array<string, array{string, int}>
 *
 * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
 */
public static function exampleProvider(): array
{
    return [
        'case one' => ['input-1', 1],
        'case two' => ['input-2', 2],
    ];
}
```

Use this exact wording so a repo-wide grep finds every provider in one pass.

### Twig template tests

Twig templates have two layers of testing (both isolated):

- **Compilation tests** verify every `.twig` file parses and references valid
  filters/functions/tests. These run automatically over all templates.
- **Render tests** render specific templates with known parameters and compare
  the full HTML output to expected fixture files in
  `tests/Tests/Isolated/Common/Twig/fixtures/render/`.

When modifying a Twig template that has render test coverage, update the
fixture files:

```bash
composer update-twig-fixtures    # Regenerate fixture files
```

Review the diff before committing. See the
[fixtures README](tests/Tests/Isolated/Common/Twig/fixtures/render/README.md)
for details on adding new test cases.

## Code Quality

These run on the host (requires local PHP/Node):

```bash
# Run all PHP quality checks (phpcs, phpstan, rector, etc.)
composer code-quality

# Individual checks (composer scripts handle memory limits)
composer phpstan              # Static analysis (level 10)
composer phpstan-baseline     # Regenerate PHPStan baseline
composer phpcs                # PHP code style check
composer phpcbf               # PHP code style auto-fix
composer rector-check         # Code modernization (dry-run)
composer rector-fix           # Code modernization (apply changes)
composer require-checker      # Detect undeclared dependencies
composer checks               # Validate composer.json and normalize
composer codespell            # Spell-check the codebase
composer conventional-commits:check  # Validate commit messages
composer php-syntax-check     # Run php -l on all PHP files

# JavaScript/CSS
npm run lint:js           # ESLint check
npm run lint:js-fix       # ESLint auto-fix
npm run stylelint         # CSS/SCSS lint
```

## Build Commands

```bash
npm run build        # Production build
npm run dev          # Development with file watching
npm run gulp-build   # Build only (no watch)
```

## Coding Standards

### Legacy Code Is Not the Standard

OpenEMR's codebase predates modern PHP and contains many antipatterns: global
state, stringly-typed parameters, `$_SESSION` and `$GLOBALS` as a service
locator, untyped arrays passed through multiple layers, and pervasive use of
`empty()` and loose comparisons. These patterns exist for historical reasons,
not because they are correct. Never use existing legacy patterns as
justification for writing new code the same way — follow the standards
documented here instead.

### Formatting and Structure

- **Indentation:** 4 spaces
- **Line endings:** LF (Unix)
- **Namespaces:** PSR-4 with `OpenEMR\` prefix for `/src/`
- New code goes in `/src/`, legacy helpers in `/library/`

### PSR Standards

| Standard | Purpose |
|----------|---------|
| [PSR-1](https://www.php-fig.org/psr/psr-1/) | Basic coding standard (class naming, file structure) |
| [PSR-4](https://www.php-fig.org/psr/psr-4/) | Autoloading |
| [PSR-3](https://www.php-fig.org/psr/psr-3/) | Logger interface (`Psr\Log\LoggerInterface`) |
| [PSR-11](https://www.php-fig.org/psr/psr-11/) | Container interface (`Psr\Container\ContainerInterface`) |
| [PER-CS 3.0](https://www.php-fig.org/per/coding-style/) | Coding style (supersedes PSR-12; adds enums, match, union types) |

Adopt where applicable: [PSR-7](https://www.php-fig.org/psr/psr-7/) (HTTP
messages), [PSR-15](https://www.php-fig.org/psr/psr-15/) (middleware),
[PSR-17](https://www.php-fig.org/psr/psr-17/) (HTTP factories),
[PSR-18](https://www.php-fig.org/psr/psr-18/) (HTTP client),
[PSR-20](https://www.php-fig.org/psr/psr-20/) (clock).

### Database and Global Settings

- **Database:** Use `QueryUtils` for queries. New schema changes use Doctrine
  Migrations. Do not instantiate database connections directly — use the
  centralized `DatabaseConnectionFactory`.
- **Global settings:** Use `OEGlobalsBag` (extends Symfony `ParameterBag`) instead
  of `$GLOBALS`. Prefer typed getters over `get()` + cast:
  - `getString($key)` instead of `(string) get($key)`
  - `getInt($key)` instead of `(int) get($key)`
  - `getBoolean($key)` instead of `(bool) get($key)`
  - `getKernel()` for the Kernel instance
  - Check the parent class for more: `getAlpha()`, `getAlnum()`, `getDigits()`, `getEnum()`

### Strict Typing

Every new PHP file starts with `declare(strict_types=1)`. Without strict types,
PHP silently coerces `"123abc"` to `123` when passed to an `int` parameter,
hiding bugs that surface later as data corruption.

Every property, parameter, and return type should have a native type
declaration. Reserve PHPDoc types for information native types cannot express
(generics, array shapes, type narrowing).

### Type System

- **Nullable types:** Use `?Type` for nullable. Use `Type|null` only in unions
  with three or more members.
- **Avoid `mixed`:** Enumerate types explicitly. Reserve `mixed` for genuinely
  polymorphic code and narrow it immediately via type checks.
- **Enums over constants:** Use enums for any value drawn from a closed set.
  Prefer unit enums (no backing type) for purely runtime state. Use backed enums
  only when the value is persisted to a database, serialized to JSON, or
  exchanged with an external system.
- **Return types:** Use `void` for side-effect-only methods, `never` for methods
  that always throw or exit, `self` for factories on `final` classes, `static`
  for factories on non-final classes.

### Immutability

- Use `readonly` classes or `readonly` properties for value objects, DTOs, and
  configuration. Mutable state should be the exception.
- `final` on value objects to prevent mutable subclasses.
- `DateTimeImmutable` over `DateTime` — always.
- Wither methods (return a new instance) over setters on value objects.

### Domain Primitives

Wrap primitive values in typed classes when the primitive could be confused with
another primitive of the same PHP type. This prevents argument transposition
bugs that are invisible to PHP's type system:

```php
final readonly class PatientId
{
    public function __construct(public int $value)
    {
        if ($value <= 0) {
            throw new \DomainException('Patient ID must be positive');
        }
    }
}
```

Use for: IDs that could be confused (`PatientId` vs `EncounterId`), strings with
semantic meaning (`Email`, `Npi`), numbers with constraints or units (`Money`).

### Parse, Don't Validate

At system boundaries (controllers, CLI handlers, message consumers), parse raw
input into typed objects immediately. After parsing, the rest of the code works
with types that guarantee their own validity — no re-validation downstream.

### Exhaustive Matching

Use `match` on enums without a `default` branch. PHPStan verifies that every
case is handled. Adding a `default` silently absorbs new cases and suppresses
the exhaustiveness check.

### Error Handling and Logging

**PSR-3 logging context:** Never concatenate or interpolate variables into log
messages. Use PSR-3 context arrays:

```php
// Bad
$this->logger->error("Failed for {$phone}: " . $e->getMessage());

// Good
$this->logger->error('Failed to send message', [
    'phone' => $phone,
    'exception' => $e,
]);
```

**Catch `\Throwable`, not `\Exception`.** `\Exception` misses `\TypeError`,
`\ParseError`, and other `\Error` subclasses.

**Let exceptions propagate.** Only catch when the caller can meaningfully
recover. Do not catch-log-continue — it hides failures from callers.

**Never expose `$e->getMessage()` in user-facing output.** Exception messages
may contain internal details (SQL, file paths). Log the exception and return a
generic message to the user.

**Exception chaining:** When wrapping an exception, use a generic message
describing the failed operation. The original exception is accessible via
`->getPrevious()` — do not embed its message in the wrapper.

### Dependency Injection

Inject all dependencies through the constructor. Never use `new` for
service-layer objects inside business logic, never call static service locators,
and never reach into global state (`$GLOBALS`, `$_SESSION`, `$_GET`, etc.) for
dependencies.

- **Interface-based dependencies** for cross-boundary code. Use concrete types
  for internal collaborators where a single-implementation interface adds no
  value.
- **PSR-11 containers:** Wire in configuration, not in business logic. Business
  logic classes should never know the container exists.
- **Clock injection (PSR-20):** Inject `ClockInterface` instead of calling
  `new \DateTimeImmutable()` or `time()` directly. This makes time-dependent
  code deterministically testable.
- **No direct superglobal access** in application code. Use PSR-7 request
  objects, framework session abstractions, and container-provided configuration.
  In legacy code where this is unavoidable, confine superglobal reads to the
  outermost entry point and parse into typed objects immediately.

### Null Safety

- **Early returns:** Flatten null checks with early returns rather than nesting.
- **Null coalescing:** `??` for defaults, `??=` for lazy initialization.
- **Null-safe operator:** `?->` for optional chaining, but no more than two
  levels deep.
- **Never suppress nullable warnings.** If PHPStan says a value might be null,
  handle the null case explicitly. Do not add `@var` casts or `@phpstan-ignore`
  comments to silence it.

### Static Analysis (PHPStan)

PHPStan runs at level 10 (`max`). Key principles:

- **Fix at the source, not the sink.** When PHPStan reports a type error, trace
  it back to where the wrong type was introduced. Do not suppress at the point
  where it manifests.
- **Narrow, don't cast.** When a value is `mixed` or a union type, narrow with
  `is_string()`, `instanceof`, etc. — do not cast with `(string)`, `(int)`.
  Casts silently coerce invalid data.
- **Avoid inline `@var` casts.** Each one should prompt the question: why does
  the type not match, and can the source be fixed?
- **Avoid baselines.** Never add new baseline entries — fix the underlying type
  error. When modifying a file, fix any existing baseline entries for that file.
- **Array typing progression** (worst to best): bare `array` → `array<K, V>` →
  `list<T>` → `non-empty-list<T>` → array shape → `@phpstan-type` alias → DTO.
  Convert shapes to DTOs when they exceed 3-4 keys or appear in multiple places.
- **Always run on the full codebase** and filter output for changed files. Never
  run on a subset — PHPStan's type inference depends on full-codebase context.

### Authorization Modeling

When an operation requires authorization, type the principal — do not pass
authorization context as strings or bare integers:

```php
// Bad
function approveOrder(int $userId, int $orderId): void {}

// Good
function approveOrder(ClinicalUser $approver, OrderId $orderId): void {}
```

When an operation is scoped to a facility or tenant, encode that scope in the
type (e.g., a scoped repository) rather than relying on runtime checks scattered
through the codebase.

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>
```

**Types:** feat, fix, docs, style, refactor, perf, test, build, ci, chore, revert

**Examples:**
- `feat(api): add PATCH support for patient resource`
- `fix(calendar): correct date parsing for recurring events`
- `chore(deps): bump monolog/monolog to 3.10.0`

### AI Assistance Trailer

If an AI assistant helped write a commit, add an `Assisted-by` trailer to that
commit:

```bash
git commit --trailer "Assisted-by: Claude Code" -m "fix(calendar): correct date parsing"
```

Use the name of the tool as the trailer value (e.g. `Claude Code`,
`GitHub Copilot`, `ChatGPT`). When the AI agent creates commits automatically,
this trailer is typically added for you.

## Service Layer Pattern

New services should extend `BaseService`:

```php
namespace OpenEMR\Services;

class ExampleService extends BaseService
{
    public const TABLE_NAME = "table_name";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }
}
```

## File Headers

When modifying PHP files, ensure proper docblock:

```php
/**
 * Brief description
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) YEAR Your Name or Organization
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
```

Preserve existing authors/copyrights when editing files.

## Common Gotchas

- Multiple template engines: check extension (.twig, .html, .php)
- Event system uses Symfony EventDispatcher
- **Pre-commit hooks:** Install with `prek install` (or `pre-commit install` if
  prek is unavailable). Run `prek run --all-files` before committing to catch
  issues early — the hooks run phpstan, rector, phpcs, codespell, and more.
- Custom PHPStan rules in `tests/PHPStan/Rules/` enforce project conventions
  (forbidden globals, forbidden direct instantiations, namespace rules, etc.)
- Commit messages are validated against Conventional Commits format in CI

## Key Documentation

- `CONTRIBUTING.md` - Contributing guidelines
- `API_README.md` - REST API docs
- `FHIR_README.md` - FHIR implementation
- `tests/Tests/README.md` - Testing guide
