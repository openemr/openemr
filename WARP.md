# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Development Commands

### Primary Development Environment - Docker

The recommended development environment uses Docker. See [CONTRIBUTING.md](CONTRIBUTING.md) for the comprehensive setup guide.

**Quick Start:**
```bash
cd docker/development-easy
docker compose up
```

Access points after startup:
- OpenEMR: `http://localhost:8300/` or `https://localhost:9300/`
- Login: `admin` / `pass`
- phpMyAdmin: `http://localhost:8310/`
- MySQL direct: `localhost:8320` (user: `openemr`, password: `openemr`)

### Developer Tools (Inside Docker Container)

All developer tools are accessed via the docker container's `/root/devtools` script:

```bash
docker compose exec openemr /root/devtools <command>
```

**Common Commands:**

**Code Quality & Testing:**
```bash
# PSR12 code style
docker compose exec openemr /root/devtools psr12-report
docker compose exec openemr /root/devtools psr12-fix

# PHP parsing errors
docker compose exec openemr /root/devtools php-parserror

# Rector (PHP modernization)
docker compose exec openemr /root/devtools rector-dry-run
docker compose exec openemr /root/devtools rector-process

# Run all tests
docker compose exec openemr /root/devtools clean-sweep-tests

# Individual test suites
docker compose exec openemr /root/devtools unit-test
docker compose exec openemr /root/devtools api-test
docker compose exec openemr /root/devtools e2e-test
docker compose exec openemr /root/devtools services-test
docker compose exec openemr /root/devtools fixtures-test
docker compose exec openemr /root/devtools validators-test
docker compose exec openemr /root/devtools controllers-test
docker compose exec openemr /root/devtools common-test
```

**Theme Development:**
```bash
# Rebuild themes after changes to interface/themes/
docker compose exec openemr /root/devtools build-themes

# Theme linting
docker compose exec openemr /root/devtools lint-themes-report
docker compose exec openemr /root/devtools lint-themes-fix
```

**Database & Environment:**
```bash
# Reset OpenEMR (then reinstall via setup.php)
docker compose exec openemr /root/devtools dev-reset

# Reset and reinstall with demo data
docker compose exec openemr /root/devtools dev-reset-install-demodata

# Snapshots (backup/restore)
docker compose exec openemr /root/devtools backup <snapshot-name>
docker compose exec openemr /root/devtools restore <snapshot-name>
docker compose exec openemr /root/devtools list-snapshots
```

**API Development:**
```bash
# Update API documentation from _rest_routes.inc.php
docker compose exec openemr /root/devtools build-api-docs

# Register OAuth2 client for testing
docker compose exec openemr /root/devtools register-oauth2-client
```

**Debugging:**
```bash
# View PHP error logs
docker compose exec openemr /root/devtools php-log

# View Xdebug log
docker compose exec openemr /root/devtools xdebug-log
```

### Running Single Tests

OpenEMR uses PHPUnit. Test configuration is in `phpunit.xml`.

```bash
# Run a specific test file
docker compose exec openemr vendor/bin/phpunit tests/Tests/Unit/Path/To/TestFile.php

# Run a specific test method
docker compose exec openemr vendor/bin/phpunit --filter testMethodName tests/Tests/Unit/Path/To/TestFile.php

# Run tests in a specific directory
docker compose exec openemr vendor/bin/phpunit tests/Tests/Services/

# View available test suites
docker compose exec openemr vendor/bin/phpunit --list-suites
```

### Building From Source (Without Docker)

Requires Node.js 22.x:

```bash
composer install --no-dev
npm install
npm run build
composer dump-autoload -o
```

**JavaScript Testing:**
```bash
npm run test:js
npm run test:js-coverage
```

**Linting:**
```bash
npm run lint:js
npm run lint:js-fix
npm run stylelint
npm run stylelint-fix
```

### Theme Development

Themes are built using Gulp from SCSS sources in `interface/themes/`.

```bash
# Development build (with sourcemaps)
npm run gulp-build

# Production build
npm run build

# Watch for changes
npm run dev
```

## Architecture Overview

### Directory Structure

- **`/src`** - Modern PHP classes following PSR-4 autoloading (`OpenEMR\` namespace)
  - `Common/` - Core framework components (Twig, Database, Auth, Logging, etc.)
  - `Services/` - Business logic layer
  - `RestControllers/` - API endpoints
  - `FHIR/` - FHIR API implementation
  - `Events/` - Event system for extensibility
  - `Core/` - Kernel, Header, Module system
  - `Billing/`, `Cqm/`, `ClinicalDecisionRules/`, etc. - Domain-specific modules

- **`/library`** - Legacy PHP includes and classes
  - Database functions (`sql.inc.php`, `sqlconf.php`)
  - Authentication (`auth.inc.php`)
  - Global functions (`globals.inc.php`, `options.inc.php`)
  - Legacy classes in `/classes` subdirectory

- **`/interface`** - User interface files (forms, main screens, patient file, etc.)
  - `forms/` - Clinical forms
  - `main/` - Main navigation and dashboard
  - `patient_file/` - Patient-specific screens
  - `themes/` - SCSS source files for themes
  - `modules/` - Module system
  - `globals.php` - Application bootstrapping

- **`/templates`** - Twig template files organized by feature
  - `core/`, `reports/`, `portal/`, `patient/`, `super/`, etc.

- **`/public`** - Compiled public assets
  - `themes/` - Built CSS from interface/themes/
  - `assets/` - JavaScript libraries and vendor assets

- **`/apis`** - API routing (`dispatch.php` for standard API, `fhir` for FHIR)

- **`/Documentation`** - Project documentation
  - `EHI_Export/docs/tables/` - HTML documentation for each database table

- **`/sql`** - Database schemas and upgrade scripts
  - `database.sql` - Complete database schema
  - Upgrade scripts for version migrations

- **`/docker`** - Docker development environments
- **`/tests`** - PHPUnit and E2E tests
- **`/sites`** - Multi-site data directories (not in repo, created at runtime)

### Key Architectural Patterns

**Database Layer:**
- Primary functions: `sqlStatement()` for recordsets, `sqlQuery()` for single rows
- Located in `library/sql.inc.php`
- Uses ADODB with MySQLi driver
- Binding support: `sqlStatement($query, [$param1, $param2])`
- New code in `/src` may use `QueryUtils` class

**Twig Integration:**
- Container: `src/Common/Twig/TwigContainer.php`
- Extensions: `src/Common/Twig/TwigExtension.php`
- Templates in `/templates` directory hierarchy
- Base templates use `{{ setupHeader() }}` for HTML head elements
- Always escape and translate: `{{ 'Text' | xlt }}`
- Header class manages assets: `src/Core/Header.php`

**Global Configuration:**
- `$GLOBALS['webroot']` - OpenEMR installation path (can be subfolder)
- `$GLOBALS['srcdir']` - Usually points to `/library`
- `$GLOBALS['fileroot']` - Absolute path to OpenEMR root
- `$GLOBALS['OE_SITE_DIR']` or `$GLOBALS['OE_SITES_DIR']` - Site-specific data paths
- Site ID: `$_SESSION['site_id']` (usually 'default')

**Authentication & Sessions:**
- `$ignoreAuth = true` - Bypass authentication (use in CLI scripts, cron jobs)
- `$sessionAllowWrite = true` - Enable session writes
- Example pattern in `interface/login/login.php`

**Module System:**
- Managed by Laminas framework components
- Located in `interface/modules/` and `interface/modules/custom_modules/`
- Module registration via Laminas Module Manager
- Background services table: insert records to schedule background tasks

**Security:**
- **NEVER send patient-identifiable data to external AI services**
- Always use `htmlspecialchars()` or `text()` helpers for output
- Use SQL binding for parameterized queries
- Validate user inputs with functions from `library/validation/`

## Development Best Practices

### Code Standards
- Follow PSR-12 for new PHP code
- Use modern PHP syntax (convert `array()` to `[]` when modifying old code)
- Run `psr12-fix` and `rector-process` before committing
- Code must meet PHP 8.2+ requirements
- Mark AI-generated code per `.github/copilot-instructions.md`

### Twig Templates
- Cannot use PHP in Twig files
- Use `{{ setupHeader() }}` or `{{ setupHeader(['asset-name']) }}` for HTML head
- Reference existing templates in `/templates` as examples
- Always extend a `base.twig` when creating module templates
- Translation syntax: `{{ 'Text Here' | xlt }}`
- For datepicker: class order matters - `datepicker` before `form-control`

### Database
- Check table schemas in `Documentation/EHI_Export/docs/tables/*.html` or `sql/database.sql`
- When testing on local DB, verify column existence before writing queries
- For multisite: always use `$_SESSION['site_id']` and `$GLOBALS['OE_SITE_DIR']` or `$GLOBALS['OE_SITES_DIR']`

### Background Services
- Avoid cron jobs; use OpenEMR background services instead
- Insert into `background_services` table with script path
- Place scripts in module's `/scripts` folder
- Example: MedEx service in `library/MedEx/MedEx_background.php`

### API Development
- Standard REST API docs: [API_README.md](API_README.md)
- FHIR API docs: [FHIR_README.md](FHIR_README.md)
- Swagger UI: `https://localhost:9300/swagger`
- OAuth2 scopes documented in API_README.md

### Testing & Debugging
- Xdebug is available on port 9003
- E2E tests are viewable in real-time: `http://localhost:7900` (password: `openemr123`)
- Always test in both development and production modes

## Additional Resources

- [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution guidelines and detailed Docker setup
- [DOCKER_README.md](DOCKER_README.md) - Docker environment documentation
- [API_README.md](API_README.md) - REST API documentation
- [FHIR_README.md](FHIR_README.md) - FHIR API documentation
- [Development Policies](https://open-emr.org/wiki/index.php/Development_Policies)
- [OpenEMR Wiki](https://www.open-emr.org/wiki/)
- [Community Forums](https://community.open-emr.org/)

## Important Notes

1. **Data Privacy:** This is a healthcare application. Never include patient demographics or identifiable information in AI prompts, logs, or commits.

2. **Autoloading:** Modern code (PHP 7.4+) should use PSR-4 autoloading in `/src`. Legacy code may still use require/include statements.

3. **Hybrid Codebase:** OpenEMR contains both legacy code (10+ years old) and modern code. When modifying existing files, maintain consistency with surrounding code style unless doing a comprehensive refactor.

4. **Multisite:** Always consider multisite scenarios - use site-aware globals and session variables.

5. **No Framework Lock-in:** OpenEMR does not use a single framework. It uses:
   - Laminas components for modules and some services
   - Twig for templating (newer views)
   - Smarty for some legacy templates
   - Symfony components for HTTP and event dispatching
   - Custom frameworks in `/src/Common`
