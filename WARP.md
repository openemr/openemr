# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Development Environment Setup

### Requirements
- PHP 8.2 or newer
- Node.js 20.x
- Composer 2.x
- Docker and Docker Compose (for the provided dev environment)

### Initial Build Commands
```bash
composer install --no-dev
npm install
npm run build
composer dump-autoload -o
```

### Docker Development Environment
- Use `docker/development-easy` for standard x86 Linux
- Use `docker/development-easy-arm` or `docker/development-easy-arm64` for Raspberry Pi
- Start environment: `docker-compose up` from the docker directory
- Default credentials: username `admin`, password `pass`

**Access Points:**
- Web (HTTP): http://localhost:8300
- Web (HTTPS): https://localhost:9300
- phpMyAdmin: http://localhost:8310
- MySQL Direct: port 8320 (user: `openemr`, password: `openemr`)

### Local PHP Development Server (Non-Docker)
This workspace includes a `.env.dev` file defining `DEV_PORT=8081`

To serve locally:
```bash
php -S localhost:8081
```

**Always check `.env.dev` at the start of tasks to confirm the correct port.**

## Common Development Commands

### Build and Assets
```bash
# Install dependencies
composer install --no-dev
npm install

# Build assets
npm run build

# Development build (unminified)
npm run dev-build

# Watch for changes
npm run gulp-watch
```

### Theme Compilation
```bash
# Build themes in Docker
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools build-themes'
```

### Linting and Code Standards

**JavaScript:**
```bash
npm run lint:js
npm run lint:js-fix
```

**CSS/SCSS:**
```bash
npm run stylelint
npm run stylelint-fix
```

**PHP PSR-12 (in Docker):**
```bash
# Generate report
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools psr12-report'

# Auto-fix issues
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools psr12-fix'

# Check for PHP parsing errors
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools php-parserror'
```

### Testing

**Run specific test suites:**
```bash
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools unit-test'
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools api-test'
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools e2e-test'
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools services-test'
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools fixtures-test'
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools validators-test'
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools controllers-test'
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools common-test'
```

**Run all automated tests:**
```bash
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools clean-sweep-tests'
```

**Run full dev suite (PSR-12 fix + lint + all tests):**
```bash
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools clean-sweep'
```

**JavaScript tests:**
```bash
npm run test:js
npm run test:js-coverage
```

### Database Management

**Reset and reinstall:**
```bash
# Reset only
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools dev-reset'

# Reset and reinstall
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools dev-reset-install'

# Reset and reinstall with demo data
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools dev-reset-install-demodata'
```

**Snapshots (backup/restore):**
```bash
# Create backup
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools backup snapshot_name'

# Restore from backup
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools restore snapshot_name'

# List snapshots
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools list-snapshots'
```

**Local Database Access (Environment-Specific):**
```bash
mysql -u local_openemr -p
# Password: 5qy3xkMjP4A2US1u7Qv
```

### API Documentation
- Swagger UI available at: `/swagger`
- Rebuild API documentation:
```bash
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools build-api-docs'
```

## Architecture Overview

### Hybrid Legacy + Modern Architecture
OpenEMR is a large, mature codebase (10+ years) with a hybrid architecture:

- **No single monolithic framework** - uses best-of-breed components
- **Laminas Framework** - manages the module system
- **Symfony Components** - powers dispatch system and event-driven communication
- **Twig** - modern templating (migration in progress from legacy PHP templates)
- **PSR-4 Classes** - new code under `OpenEMR\` namespace in `/src`
- **Legacy Code** - procedural PHP in `/library` and `/interface`

### Directory Structure

```
/src                      # Modern PSR-4 classes (namespace OpenEMR\)
  /Common                 # Core components (Database, Session, Twig, etc.)
  /Services               # Business logic services
  /RestControllers        # REST API controllers
  /FHIR                   # FHIR API implementation
  /Events                 # Event system
  /Controllers            # MVC controllers
  /Validators             # Input validation

/library                  # Legacy helpers and procedural functions
  /classes                # Legacy class library
  /MedEx                  # Background service example

/interface                # UI and legacy interface code
  /globals.php            # Core bootstrap file
  /themes                 # SCSS source files for themes

/modules                  # Custom modules (Laminas-managed)

/templates                # Twig templates for modern frontend

/public                   # Compiled assets and public resources
  /themes                 # Compiled CSS
  /assets                 # Vendor libraries (Bootstrap, jQuery, etc.)

/apis                     # API endpoints
  /_rest_config.php       # API configuration
  /_rest_routes.inc.php   # API route definitions

/tests                    # PHPUnit test suites
  /Tests                  # Unit, API, E2E, Services tests

/sql                      # Database schema and migrations

/docker                   # Docker development environments
```

## Database Access Patterns

### Preferred Methods
- **`sqlQuery()`** - For single-row results
- **`sqlStatement()`** - For multiple-row results (use with `sqlFetchArray()` in a loop)

```php
// Single row
$result = sqlQuery("SELECT * FROM users WHERE id = ?", [$id]);

// Multiple rows
$stmt = sqlStatement("SELECT * FROM users WHERE active = ?", [1]);
while ($row = sqlFetchArray($stmt)) {
    // Process each row
}
```

### Avoid PDO
Only use PDO when `globals.php` is not accessible or namespace constraints prevent using helper functions.

### Database Connection Management
- Managed in `interface/globals.php` and `src/Common/Database/QueryUtils.php`
- Never create direct database connections in application code

### CLI/Non-Interactive Contexts

When running scripts from command line without browser access:

```php
// Set BEFORE including globals.php
$ignoreAuth = true;              // Skip authentication
$sessionAllowWrite = true;        // Enable session writes if needed

require_once(__DIR__ . '/interface/globals.php');

// Always set site ID
$_SESSION['site_id'] = 'default';
```

### Multisite and Webroot
**Always use these globals for site and path awareness:**
- `$_SESSION['site_id']` - Current site in multisite setup
- `$GLOBALS['webroot']` - Handles subfolder installations

## Twig Templates

### Key Rules
1. **Never use PHP code inside Twig templates**
2. Use Twig filters and functions exclusively

### Standard Header
```twig
{{ setupHeader() }}

{# With options #}
{{ setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']) }}
```

### Text Translation and Escaping
```twig
{# Escape and translate #}
{{ 'Text Here' | xlt }}

{# Translation functions available:
   - xl()  - translate
   - xlt() - translate (for Twig)
   - xla() - translate for attributes
#}
```

### Module Templates
- Always create a `base.twig` in your module's template directory
- Extend `base.twig` in all other templates for consistency

### Twig Integration Points
- **TwigContainer**: `src/Common/Twig/TwigContainer.php`
- **TwigExtension**: `src/Common/Twig/TwigExtension.php` (adds custom filters/functions)
- **Template Directory**: `$GLOBALS['webroot']/templates`

## Module Development

### Module System
- Modules managed by **Laminas Framework**
- Module configuration and autoloading handled by Laminas Module Manager

### Module Structure
```
/modules/your_module/
  /src/               # Module classes
  /templates/         # Twig templates
    base.twig         # Base template
  /scripts/           # Background service scripts
  Module.php          # Laminas module configuration
  composer.json       # Module dependencies (optional)
```

### Module Templates
- Ensure `base.twig` exists in module template directory
- All module templates should extend `base.twig`

### Background Services in Modules
Place background service scripts in `/scripts/` folder within module root.

## Background Services

Background services run automatically based on entries in the `background_services` table.

### Example Service Registration
```sql
INSERT INTO `background_services` 
  (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) 
VALUES 
  ('MedEx', 'MedEx Messaging Service', 0, 0, '2017-05-09 21:39:10', 0, 'start_MedEx', '/library/MedEx/MedEx_background.php', 100);
```

### Service Script Requirements
- Entry point function specified in `function` column
- Script path specified in `require_once` column (relative to OpenEMR root)
- For modules: place scripts in `modules/your_module/scripts/`

### Execution
Background services execute via:
- Cron jobs or
- Internal polling mechanism via `/library/ajax/execute_background_services.php`

## REST API Development

### API Types
1. **Standard REST API** - `/api/` endpoints (OEMR API)
2. **FHIR R4 API** - `/fhir/` endpoints (FHIR API, US Core 3.1 IG compliant)

### Multisite Support
API URLs include site identifier:
- Standard API: `https://localhost:9300/apis/default/api/patient`
- FHIR API: `https://localhost:9300/apis/default/fhir/Patient`
- Replace `default` with site name for multisite installations

### Authentication
- **OAuth 2.0** with **OIDC** (OpenID Connect) required
- SSL/TLS required
- Configure at: Administration → Config → Connectors
- Set "Site Address (required for OAuth2 and FHIR)"

### API Documentation
- **Swagger UI**: Navigate to `/swagger` in your OpenEMR installation
- **Rebuild docs**: Updates `swagger/openemr-api.yaml` from `_rest_routes.inc.php`
```bash
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools build-api-docs'
```

### Route Definition
Routes defined in: `_rest_routes.inc.php`

### Scopes
See detailed scope listing in `API_README.md`

Key scope prefixes:
- `api:oemr` - Standard REST API scopes
- `api:fhir` - FHIR API scopes
- `patient/`, `user/`, `system/` - Access level prefixes

## Testing Strategy

### Test Suites (PHPUnit)
- **unit** - Unit tests
- **api** - API endpoint tests
- **e2e** - End-to-end browser tests
- **services** - Service layer tests
- **fixtures** - Data fixture tests
- **validators** - Validator tests
- **controllers** - Controller tests
- **common** - Common component tests
- **ECQM** - Clinical Quality Measures tests

### Running Tests
```bash
# Individual suite
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools {suite}-test'

# All tests
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools clean-sweep-tests'

# Full sweep (PSR-12 + lint + tests)
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools clean-sweep'
```

### JavaScript Testing
```bash
npm run test:js
npm run test:js-coverage
```

### Test Configuration
PHPUnit configuration: `phpunit.xml`

## Coding Standards and Best Practices

### PSR-12 Standard
**All new PHP code must follow PSR-12 coding standard.**

Check compliance:
```bash
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools psr12-report'
```

Auto-fix issues:
```bash
docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools psr12-fix'
```

### SOLID Principles
- Code to interfaces when possible
- Follow SOLID principles for new code and refactoring
- Use dependency injection where appropriate

### Database Connections
- **OpenEMR projects**: Use `sqlQuery()` / `sqlStatement()` helpers
- **Greenfield projects**: Use singleton pattern for database connections

### Code Modification
**Do not modify existing code without explicit approval.** Preserving backward compatibility is critical.

### Documentation
- Table schemas documented at: `/Documentation/EHI_Export/docs/tables/` (one HTML file per table)
- API documentation in `API_README.md` and `FHIR_README.md`

## Important Conventions

1. **Always use `$_SESSION['site_id']`** for site awareness in multisite setups
2. **Always use `$GLOBALS['webroot']`** to handle subfolder installations
3. **Follow PSR-12** for all new PHP code
4. **Code to interfaces** and apply SOLID principles
5. **Do not change existing code** without explicit consent
6. **Use `sqlQuery()` and `sqlStatement()`** for database access
7. **Set `$ignoreAuth = true`** for CLI scripts before including `globals.php`
8. **Set `$_SESSION['site_id']`** in non-browser contexts
9. **Always extend `base.twig`** in module templates
10. **Never use PHP in Twig templates** - use Twig syntax exclusively

## Local Development Environment Notes

This workspace has specific local configuration:

### Local OpenEMR Instance
- **Path**: `/var/www/html/openemr703`
- **URL**: `localhost/openemr703`
- **Associated Database**: `openemr703`

### Local Database Access
```bash
mysql -u local_openemr -p
# Password: 5qy3xkMjP4A2US1u7Qv
```

### Development Port
Defined in `.env.dev`:
```
DEV_PORT=8081
```

Start local PHP server:
```bash
php -S localhost:8081
```

**Always check `.env.dev` at the start of each task to confirm port configuration.**

## Additional Resources

- **Contributing Guide**: `CONTRIBUTING.md`
- **API Documentation**: `API_README.md`
- **FHIR Documentation**: `FHIR_README.md`
- **Docker Instructions**: `DOCKER_README.md`
- **Main README**: `README.md`
- **Online Documentation**: https://open-emr.org/wiki/
- **Community Forums**: https://community.open-emr.org/
- **Development Demos**: https://www.open-emr.org/wiki/index.php/Development_Demo
