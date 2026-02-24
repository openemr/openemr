# OpenEMR Development Guide

## Project Structure

```
/src/              - Modern PSR-4 code (OpenEMR\ namespace) - 1,867 PHP files
  /Services/       - 60+ services (BaseService pattern), 35+ FHIR services
  /RestControllers/- REST API controllers (standard + FHIR + auth)
  /Events/         - 81 event classes in 24 categories (Symfony EventDispatcher)
  /Validators/     - Input validation (ProcessingResult pattern)
  /FHIR/           - FHIR R4 resource definitions (918 files, auto-generated)
  /Common/         - Shared utilities (Database, Auth, Logging, Twig, UUID)
  /Core/           - Kernel, HTTP kernel, OEGlobalsBag
  /ClinicalDecisionRules/ - Existing CDS rule engine
/library/          - Legacy procedural PHP code (do not extend, only maintain)
/interface/        - Web UI controllers and templates
  /modules/custom_modules/ - Custom module directory (8 production modules)
/templates/        - Twig 3.x (modern) and Smarty 4.5 (legacy) templates
/tests/            - Test suite (unit, e2e, api, services, isolated) - 270 files
/sql/              - Database schema and migrations
/apis/routes/      - REST route definitions (standard, FHIR R4, portal)
/public/           - Static assets
/docker/           - Docker configurations
/modules/          - Legacy module directory
```

## Technology Stack

- **PHP:** 8.2+ required (tested through 8.6)
- **Backend:** Laminas MVC, Symfony EventDispatcher, ADODB
- **Templates:** Twig 3.x (modern), Smarty 4.5 (legacy)
- **Frontend:** Angular 1.8, jQuery 3.7, Bootstrap 4.6
- **Build:** Gulp 4, SASS, Node 22+
- **Database:** MySQL/MariaDB via ADODB wrapper + QueryUtils
- **Testing:** PHPUnit 11, Jest 29, Symfony Panther (E2E)
- **API:** REST + FHIR R4 (US Core 8.0), OAuth2/SMART on FHIR v2.2.0
- **CI:** 27 GitHub Actions workflows

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
# Run all PHP quality checks (phpcs, phpstan, rector)
composer code-quality

# Individual checks (composer scripts handle memory limits)
composer phpstan          # Static analysis (Level 10, strictest)
composer phpcs            # PHP code style check (PSR-12)
composer phpcbf           # PHP code style auto-fix
composer rector-check     # Code modernization (dry-run)

# JavaScript/CSS
npm run lint:js           # ESLint check
npm run lint:js-fix       # ESLint auto-fix
npm run stylelint         # CSS/SCSS lint
```

### PHPStan Custom Rules (Must Follow)

PHPStan runs at **Level 10** with 12 custom rules. Code that violates these
rules will fail CI:

| Forbidden | Use Instead |
|-----------|-------------|
| `$GLOBALS['key']` | `OEGlobalsBag::getInstance()` |
| Legacy `sql.inc.php` functions | `QueryUtils`, `DatabaseQueryTrait` |
| `curl_*` functions | `GuzzleHttp\Client` or `oeHttp` |
| `error_log()` | `SystemLogger` |
| `call_user_func()` | Modern PHP syntax `$fn()` |
| `laminas-db` classes | ADODB or Doctrine |
| `global` keyword | Dependency injection |
| `empty()` construct | `=== null`, `=== ''`, `count() === 0` |
| `@covers` in tests | Remove annotation |

## Build Commands

```bash
npm run build        # Production build
npm run dev          # Development with file watching
npm run gulp-build   # Build only (no watch)
```

## Coding Standards

- **Indentation:** 4 spaces
- **Line endings:** LF (Unix)
- **No strict_types:** Project doesn't use `declare(strict_types=1)` - do not add it
- **Namespaces:** PSR-4 with `OpenEMR\` prefix for `/src/`
- New code goes in `/src/`, legacy helpers in `/library/`

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

## Architecture

### Request Flow (API)

```
HTTP Request -> dispatch.php -> ApiApplication
  -> OAuth2 Authorization (Bearer/SMART/Client Credentials)
    -> Route Matching (apis/routes/_rest_routes*.inc.php)
      -> RestController -> Service (extends BaseService)
        -> Database (QueryUtils/ADODB) -> ProcessingResult response
```

### Service Layer Pattern

All data access flows through services extending `BaseService`:

```php
namespace OpenEMR\Services;

class ExampleService extends BaseService
{
    public const TABLE_NAME = "table_name";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    // Key inherited capabilities:
    // - search() returns ProcessingResult with FHIR search support
    // - getEventDispatcher() for event publishing
    // - selectHelper() for SQL select queries
    // - buildInsertColumns() / buildUpdateColumns() for writes
    // - getFields() / getSelectFields() for column discovery
    // - UUID management for FHIR interoperability
}
```

**Key services:** PatientService, EncounterService, AppointmentService,
ConditionService, PrescriptionService, AllergyIntoleranceService,
InsuranceService, ClinicalNotesService, ObservationLabService,
DecisionSupportInterventionService (60+ total)

### ProcessingResult Pattern

All service methods return `ProcessingResult`:

```php
$result = new ProcessingResult();
$result->addData($record);              // Success data
$result->setValidationMessages($errors); // Validation errors
$result->addInternalError($message);     // System errors
// Check: $result->isValid(), $result->hasData(), $result->hasErrors()
```

### Event System (81 Events, 24 Categories)

Events are dispatched via Symfony EventDispatcher accessed through:

```php
$dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
$dispatcher->dispatch($event, EventClass::EVENT_HANDLE);
```

**Key event categories:**
- **Patient:** BeforeCreated, Created, BeforeUpdated, Updated, UpdatedAux
- **Encounter:** FormsListRender, LoadFormFilter, Button, Menu events
- **Appointments:** Set, Render, DialogClose, Filter, CalendarFilter
- **Services:** save.pre, save.post, delete.pre, delete.post (via ServiceEventTrait)
- **REST API:** restConfig.route_map.create, restapi.service.get, api.scope.get-supported-scopes
- **Documents:** Create, Store, Retrieve, CCDA events
- **UI:** html.head.script.filter, oemrui.page.header.render, main tabs rendering
- **Messaging:** SendNotification, SendSms
- **Filters:** AbstractBoundFilterEvent for modifying queries

### Custom Module System

Modules live in `/interface/modules/custom_modules/` with this structure:

```
oe-module-my-feature/
  openemr.bootstrap.php     # Required entry point
  moduleConfig.php          # Pre-install config
  src/Bootstrap.php         # Class-based bootstrap (recommended)
  src/                      # PSR-4 source under OpenEMR\Modules\MyFeature\
  templates/                # Twig templates
  public/                   # Static assets
  table.sql                 # Schema creation
  cleanup.sql               # Uninstall cleanup
  info.txt                  # Module metadata
```

**Bootstrap pattern:**
```php
// openemr.bootstrap.php
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\MyFeature\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);
$bootstrap = new \OpenEMR\Modules\MyFeature\Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
```

**8 production modules exist:** claimrev-connect, comlink-telehealth,
dashboard-context, dorn, ehi-exporter, faxsms, prior-authorizations, weno.

Modules that fail 3 times are auto-disabled (safety mechanism).

### REST & FHIR API

Three API surfaces:
- **Standard REST:** `/apis/{site}/api/` - OpenEMR-native endpoints (20+)
- **FHIR R4:** `/apis/{site}/fhir/` - 30+ FHIR resources (US Core 8.0)
- **Patient Portal:** `/apis/{site}/portal/` - Patient-facing (experimental)

**Authentication:** OAuth 2.0 with PKCE, SMART on FHIR v2.2.0, Client Credentials (JWKS)

**Scope model:** `<context>/<Resource>.<permissions>[?<query>]`

**Route extension via events:**
```php
$eventDispatcher->addListener(RestApiCreateEvent::EVENT_HANDLE, function($event) {
    $event->addToRouteMap(['/ai/insight/:id' => ['GET' => ...]]);
});
```

### Database Layer

- **QueryUtils** - Static helpers: `fetchRecords()`, `listTableFields()`, `escapeTableName()`
- **DatabaseQueryTrait** - Trait for classes needing DB access
- **UuidRegistry** - Maps internal IDs to UUIDs for FHIR interoperability
- **Migrations** - SQL-based version upgrades in `/sql/`

### Existing Agent-Adjacent Infrastructure

- **DecisionSupportInterventionService** - Existing CDS framework supporting Evidence-based and Predictive DSI types
- **BackgroundTaskManager** (`src/Telemetry/`) - Manages recurring background tasks via `background_services` table
- **ClinicalDecisionRules** (`src/ClinicalDecisionRules/`) - Rule-based clinical decision support with AMC integration

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
- Event system uses Symfony EventDispatcher (access via `OEGlobalsBag`, never `$GLOBALS`)
- Pre-commit hooks available via `.pre-commit-config.yaml`
- PHPStan Level 10 with custom rules - `empty()`, `$GLOBALS`, `curl_*` are all forbidden
- No `declare(strict_types=1)` - this is a project-wide decision, do not add it
- Legacy code in `/library/` is maintained but not extended - new code goes in `/src/`
- Modules auto-disable after 3 failures

## Key Documentation

- `CONTRIBUTING.md` - Contributing guidelines
- `API_README.md` - REST API docs
- `FHIR_README.md` - FHIR implementation
- `tests/Tests/README.md` - Testing guide
- `pre-search.md` - Codebase orientation and key file reference
- `AGENT_PLANNING_REPORT.md` - Agent architecture analysis and proposals
