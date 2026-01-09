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
2. **E2E Tests**: End-to-end browser tests using Panther/ChromeDriver (via Selenium Grid)
3. **API Tests**: Tests for REST API endpoints
4. **Fixtures Tests**: Tests for database fixtures
5. **Services Tests**: Tests for service classes
6. **Validators Tests**: Tests for data validation classes
7. **Controllers Tests**: Tests for controller classes
8. **Common Tests**: Tests for common utilities and functions

### Code Coverage

Code coverage reporting is enabled only for the `apache_84_114` configuration. When enabled, it:
- Configures Xdebug for coverage collection
- Merges coverage data from all test suites
- Generates Clover XML and HTML coverage reports
- Uploads the reports as GitHub Actions artifacts

### Adding New Test Configurations

To add a new test configuration:

1. Create a new directory following the naming convention `webserver_phpversion_dbversion`.
2. Add a `docker-compose.yml` file with the appropriate MySQL/MariaDB and OpenEMR services (see below `Adding a New Configuration` section).
3. Ensure the database service is named `mysql` for compatibility with the test scripts.
4. If you want to skip E2E tests for this configuration, add `_no-e2e` suffix to the directory name.

### CI Environment Variables

The CI process uses several important environment variables:

- `DOCKER_DIR`: The directory containing the Docker Compose configuration
- `ENABLE_COVERAGE`: Whether to enable code coverage reporting (true/false)
- `OPENEMR_DIR`: The directory containing OpenEMR inside the Docker container
- `COMPOSE_FILE`: The Docker Compose COMPOSE_FILE environment variable is set to store the templates for the multi-file composition. The first file is the template for the web server configuration (Apache or Nginx). The second file is the template for the database configuration (MariaDB or MySQL). The third file is the template for the PHP version and MariaDB/MySQL version.

### Docker Compose Extension System

The CI system uses Docker Compose's multi-file composition (otherwise known as compose file merging) to maintain DRY (Don't Repeat Yourself) configuration across multiple test environments. This is implemented through shared base configurations that individual test environments use.

#### How It Works

1. **Shared Configuration Files**:
   - `compose-shared-selenium/docker-compose.yml`: Contains the Selenium Grid configuration for running E2E tests. It is always included.
   - `compose-shared-apache.yml`: Contains the base configuration for Apache-based setups with database specific items not included.
   - `compose-shared-nginx.yml`: Contains the base configuration for Nginx-based setups with database specific items not included.
   - `compose-shared-mariadb.yml`: Contains MariaDB specific items.
   - `compose-shared-mysql.yml`: Contains MySQL specific items.

2. **Individual Test Environment Setup**:
   Each test directory (e.g., `apache_83_116`) has its own `docker-compose.yml` that:
   - Shows which 2 base configurations (a webserver and a database) to use
   - Select the specific database version and PHP versions

3. **docker-compose.yml Pattern**:
   ```yaml
   # Note these x-includes are not actually seen or used by Docker Compose and are instead utilized by scripting to build the
   #  multi-file composition command line commands.
   x-includes:
     selenium-template: "compose-shared-selenium/docker-compose.yml"  # Show the Selenium Grid template
     webserver-template: "compose-shared-apache.yml"  # Show the web server template (Apache or Nginx)
     database-template: "compose-shared-mariadb.yml"  # Show the database template (MariaDB or MySQL)

   services:
     mysql:
       image: mariadb:11.4                            # Specify MariaDB/MySQL version
     openemr:
       image: openemr/openemr:flex-3.21               # Specify PHP version
   ```

#### Adding a New Configuration

To add a new test configuration:

1. Decide if your new environment needs Apache or Nginx:
   - For Apache-based environments, will use `compose-shared-apache.yml`
   - For Nginx-based environments, will use `compose-shared-nginx.yml`

2. Decide if your new environment needs MariaDB or MySQL:
   - For MariaDB, will use `compose-shared-mariadb.yml`
   - For MySQL, will use `compose-shared-mysql.yml`

2. Create a new directory following the naming convention:
   ```
   {webserver}_{phpversion}[_{dbversion}][_no-e2e]
   ```

3. Create a `docker-compose.yml` file in the new directory:

   **For Apache environments with MariaDB**:
   ```yaml
   # Note these x-includes are not actually seen or used by Docker Compose and are instead utilized by scripting to build the
   #  multi-file composition command line commands.
   x-includes:
     selenium-template: "compose-shared-selenium/docker-compose.yml"  # Show the Selenium Grid template
     webserver-template: "compose-shared-apache.yml"  # Show the Apache web server template
     database-template: "compose-shared-mariadb.yml"  # Show the MariaDB database template

   services:
     mysql:
       image: mariadb:<version>                       # Specify MariaDB version
     openemr:
       image: openemr/openemr:<tag>                   # Specify PHP version
   ```

   **For Apache environments with MySQL**:
   ```yaml
   # Note these x-includes are not actually seen or used by Docker Compose and are instead utilized by scripting to build the
   #  multi-file composition command line commands.
   x-includes:
     selenium-template: "compose-shared-selenium/docker-compose.yml"  # Show the Selenium Grid template
     webserver-template: "compose-shared-apache.yml"  # Show the Apache web server template
     database-template: "compose-shared-mysql.yml"    # Show the MySQL database template

   services:
     mysql:
       image: mysql:<version>                         # Specify MySQL version
     openemr:
       image: openemr/openemr:<tag>                   # Specify PHP version
   ```

   **For Nginx environments with MariaDB**:
   ```yaml
   # Note these x-includes are not actually seen or used by Docker Compose and are instead utilized by scripting to build the
   #  multi-file composition command line commands.
   x-includes:
     selenium-template: "compose-shared-selenium/docker-compose.yml"  # Show the Selenium Grid template
     webserver-template: "compose-shared-nginx.yml"   # Show the Nginx web server template
     database-template: "compose-shared-mariadb.yml"  # Show the MariaDB database template

   services:
     mysql:
       image: mariadb:<version>                         # Specify MariaDB version
     openemr:
       image: openemr/dev-php-fpm:<php-version>       # Specify PHP version
   ```

   **For Nginx environments with MySQL**:
   ```yaml
   # Note these x-includes are not actually seen or used by Docker Compose and are instead utilized by scripting to build the
   #  multi-file composition command line commands.
   x-includes:
     selenium-template: "compose-shared-selenium/docker-compose.yml"  # Show the Selenium Grid template
     webserver-template: "compose-shared-nginx.yml"   # Show the Nginx web server template
     database-template: "compose-shared-mysql.yml"    # Show the MySQL database template

   services:
     mysql:
       image: mysql:<version>                         # Specify MySQL version
     openemr:
       image: openemr/dev-php-fpm:<php-version>       # Specify PHP version
   ```

4. Customize any additional settings specific to your configuration as needed.

#### Modifying Shared Configurations

When updating the shared configuration files:
- Changes to `compose-shared-selenium/docker-compose.yml`, ```compose-shared-apache.yml`, `compose-shared-nginx.yml`, `compose-shared-mariadb.yml`, and  `compose-shared-mysql.yml` will affect all test environments that use them
- Make sure your changes are backward compatible or update the individual environment files as needed
- Test the changes across multiple environments to ensure they work correctly

### Skipping Slow Tests During Development

When iterating on a PR, you may want to skip the slow `Test All Configurations` workflow to get faster feedback from linting and static analysis. To do this, add the `Skip-Slow-Tests: true` trailer to your commit:

```sh
git commit --trailer "Skip-Slow-Tests: true" -m "fix: correct date parsing"
```

When this trailer is present, the workflow will fail immediately. This failed status prevents accidental merging without running the full test suite.

Before merging, push a commit without the trailer to trigger the full tests.

### Troubleshooting CI

If tests are failing in CI but passing locally, check:

1. PHP version compatibility issues
2. Database version specific features
3. Web server configuration differences
4. Path differences between container environments

### Debugging Configurations

-For below commands:
  - Replace `apache_84_114` with the configuration directory you want to test.
  - Replace `compose-shared-apache.yml` and `compose-shared-mariadb.yml` with the `x-includes` values that are included in the main docker-compose.yml file.
  - Run the below commands from the base openemr directory.
  - Note that for future: the first entry (-f) in the command needs to be in ci/ (if it has a subdirectory then it breaks things)

You can view the fully merged configuration file with the following `config` command:
```bash
docker compose -f "ci/compose-shared-apache.yml" -f "ci/compose-shared-mariadb.yml" -f "ci/compose-shared-selenium/docker-compose.yml" -f "ci/apache_84_114/docker-compose.yml" config
```

You can also run the same Docker Compose setup locally:
```bash
docker compose -f "ci/compose-shared-apache.yml" -f "ci/compose-shared-mariadb.yml" -f "ci/compose-shared-selenium/docker-compose.yml" -f "ci/apache_84_114/docker-compose.yml" up -d
```

You can go directly into the OpenEMR testing container:
```bash
docker compose -f "ci/compose-shared-apache.yml" -f "ci/compose-shared-mariadb.yml" -f "ci/compose-shared-selenium/docker-compose.yml" -f "ci/apache_84_114/docker-compose.yml" exec -it openemr sh
```

You can shut down the Docker Compose setup:
```bash
docker compose -f "ci/compose-shared-apache.yml" -f "ci/compose-shared-mariadb.yml" -f "ci/compose-shared-selenium/docker-compose.yml" -f "ci/apache_84_114/docker-compose.yml" down -v
```
