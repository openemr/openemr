# OpenEMR

## Continuous Integration

This directory houses the configuration for automated tests across multiple version and setup configurations of OpenEMR. It is used by the GitHub Actions workflow in `.github/workflows/test.yml` to run various tests.

### Layout and Configurations

The subdirectories that contain `docker-compose.yml` files contain different configurations. The name of the directory lets you know the webserver, PHP version, and expected database versions. The naming convention follows this pattern:

```
{webserver}_{phpversion}[_{dbversion}]
```

For example, `apache_83_116` means:
- Web server: Apache
- PHP version: 8.3
- MariaDB/MySQL version: 11.6

While the directory names indicate the configuration, the actual `docker-compose.yml` files determine what's run. If a directory ends with `_no-e2e`, then the end-to-end (E2E) tests will be skipped for that configuration.

All of the tests use the same source script: `ciLibrary.source`. That script sets up the environment, optionally including code coverage setup, and then provides shell functions that can be used to run each test suite.

### Test Matrix System

The GitHub Actions workflow dynamically builds a test matrix based on the directories in this folder. For each directory containing a `docker-compose.yml` file:

1. The workflow parses the directory name to extract the webserver, PHP version, and database version.
2. It reads the `docker-compose.yml` to determine the exact database image.
3. It creates a configuration for GitHub Actions to run tests against that specific setup.

This allows testing OpenEMR across multiple environments automatically without having to manually update the GitHub workflow file.

### Test Types

The CI runs several different test suites sequentially:

1. **Unit Tests**: Basic unit tests for individual functions and classes
2. **E2E Tests**: End-to-end browser tests using Panther/ChromeDriver
3. **API Tests**: Tests for REST API endpoints
4. **Fixtures Tests**: Tests for database fixtures
5. **Services Tests**: Tests for service classes
6. **Validators Tests**: Tests for data validation classes
7. **Controllers Tests**: Tests for controller classes
8. **Common Tests**: Tests for common utilities and functions

### Code Coverage

Code coverage reporting is enabled only for the `apache_83_116` configuration. When enabled, it:
- Configures Xdebug for coverage collection
- Merges coverage data from all test suites
- Generates Clover XML and HTML coverage reports
- Uploads the reports as GitHub Actions artifacts

### Adding New Test Configurations

To add a new test configuration:

1. Create a new directory following the naming convention `webserver_phpversion_dbversion`.
2. Add a `docker-compose.yml` file with the appropriate MySQL/MariaDB and OpenEMR services.
3. Ensure the database service is named `mysql` for compatibility with the test scripts.
4. If you want to skip E2E tests for this configuration, add `_no-e2e` suffix to the directory name.

### CI Environment Variables

The CI process uses several important environment variables:

- `DOCKER_DIR`: The directory containing the Docker Compose configuration
- `ENABLE_COVERAGE`: Whether to enable code coverage reporting (true/false)
- `OPENEMR_DIR`: The directory containing OpenEMR inside the Docker container
- `CHROMIUM_INSTALL`: Commands to install ChromeDriver for E2E tests

### Troubleshooting CI

If tests are failing in CI but passing locally, check:

1. PHP version compatibility issues
2. Database version specific features
3. Web server configuration differences
4. Path differences between container environments

To debug a specific configuration, you can run the same Docker Compose setup locally using:

```bash
docker compose --project-directory "ci/apache_83_116" up
```

Replace `apache_83_116` with the configuration directory you want to test.
