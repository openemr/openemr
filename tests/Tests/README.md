## OpenEMR Testing

### Overview

OpenEMR integration and unit tests are implemented using [phpunit](https://phpunit.de/). Browser based test cases are implemented using [Symfony's Panther framework](https://github.com/symfony/panther).

### Test Case Directory Structure

| Directory | Test Case Type |
| --------- | -------------- |
| Api       | API Controller Tests |
| Common    | Tests OpenEMR "common"/reusable components |
| E2e       | Browser Based Tests (End to End) |
| Fixture   | Manages test case fixtures |
| Isolated  | Tests that run without a database or Docker (Twig templates, etc.) |
| Service   | Service/Data Access Tests |
| Unit      | Tests components which don't require database integration |

### Test Case Fixtures

The [Fixture Namespace](./Fixture) is used to manage test case fixtures, or sample records, used in test cases. The [Fixture Manager](./Fixture/FixtureManager.php) is used to install sample records into the database, or return sample records to it's caller. The FixtureManager sources data from `JSON` datafiles within the fixture namespace.

The FixtureManager currently supports the following record types:
- Patient Data
- FHIR Patient Resources

To support additional record types within FixtureManager:
- Add a supporting json file to the Fixture Namespace which maps to an OpenEMR database table.
- Add public methods to the class to get, install, and remove fixture records.

### Isolated Tests

Isolated tests live in `Isolated/` and run without a database or Docker using a
separate PHPUnit config:

```sh
composer phpunit-isolated
```

Currently includes:
- **Twig compilation tests** — verify all `.twig` templates parse and reference
  valid filters/functions/tests.
- **Twig render tests** — render specific templates with known parameters and
  compare full HTML output to expected fixture files. Update fixtures with
  `composer update-twig-fixtures`. See
  `Isolated/Common/Twig/fixtures/render/README.md` for details.
