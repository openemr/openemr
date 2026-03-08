# OpenEMR Tests

OpenEMR uses [PHPUnit](https://phpunit.de/) for PHP tests and [Jest](https://jestjs.io/) for JavaScript tests. Browser-based tests use [Symfony Panther](https://github.com/symfony/panther).

## Directory Structure

| Directory | Description |
|-----------|-------------|
| `Tests/` | PHPUnit test suites (unit, integration, API, E2E, services, etc.) |
| `api/` | Legacy API test helpers |
| `certification/` | Meaningful Use certification test mappings |
| `eventdispatcher/` | Event dispatcher test utilities |
| `js/` | Jest JavaScript tests |

See [`Tests/README.md`](Tests/README.md) for detailed PHPUnit test documentation.

## Running Tests

### Docker (recommended)

Install [openemr-cmd](https://github.com/openemr/openemr-devops/tree/master/utilities/openemr-cmd) for shorthand commands that work from any directory:

```bash
openemr-cmd ut    # Unit tests
openemr-cmd at    # API tests
openemr-cmd et    # E2E browser tests
openemr-cmd st    # Services tests
openemr-cmd cst   # All tests (clean-sweep-tests)
```

Use `openemr-cmd -h` to list all available commands.

### Isolated tests (no Docker required)

Some tests run on the host without a database:

```bash
composer phpunit-isolated
```

This includes **Twig template validation** which verifies that all `.twig` files:
- Parse without syntax errors
- Reference only valid filters, functions, and tests

Some templates also have **render tests** that compare output against expected fixtures. If you modify a template with render coverage, regenerate the fixtures:

```bash
composer update-twig-fixtures
```

Review the diff before committing. See [`Tests/README.md`](Tests/README.md) for details.

### JavaScript tests

```bash
npm run test:js
npm run test:js-coverage  # With coverage
```

## Certification Reference

The [`certification/tests.md`](certification/tests.md) document maps OpenEMR features to Meaningful Use certification requirements. It links to official ONC test procedures and test data for manual QA verification—these are not automated tests.
