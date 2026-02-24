# OpenEMR Pre-Search: Codebase Orientation & Key File Reference

This document provides a fast orientation for developers and AI agents working
on OpenEMR. It maps the key files, patterns, and integration points so you can
navigate the codebase without redundant exploration.

---

## Quick Stats

| Metric | Count |
|--------|-------|
| PHP files in `/src/` | 1,867 |
| FHIR resource files | 918 |
| Service classes | 286 (60+ primary, rest are FHIR/sub-services) |
| Event class files | 81 across 24 categories |
| REST controllers | 22 standard + 35 FHIR |
| Test files | 270 across 6 suites |
| CI workflows | 27 GitHub Actions |
| Custom modules | 8 production modules |
| PHPStan custom rules | 12 enforced rules |
| Composer packages | 100+ |
| npm packages | 60+ |

---

## Core Architecture Files

### Kernel & Bootstrap

| File | Purpose |
|------|---------|
| `src/Core/Kernel.php` | Service container + EventDispatcher; `getEventDispatcher()` at line 93 |
| `src/Core/OEGlobalsBag.php` | Global service registry; replaces `$GLOBALS` access |
| `src/Core/OEHttpKernel.php` | HTTP request handling |
| `src/RestControllers/ApiApplication.php` | REST API bootstrap and dispatch |
| `src/RestControllers/Config/RestConfig.php` | REST configuration, route maps |

### Service Layer

| File | Purpose |
|------|---------|
| `src/Services/BaseService.php` | Base class for all services; provides search(), getEventDispatcher(), selectHelper(), buildInsertColumns(), buildUpdateColumns(), getFields() |
| `src/Services/BaseServiceInterface.php` | Service contract interface |
| `src/Services/Traits/ServiceEventTrait.php` | Adds event dispatch to save/delete operations (save.pre, save.post, delete.pre, delete.post) |
| `src/Validators/ProcessingResult.php` | Standard return wrapper: data + validationMessages + internalErrors + pagination |
| `src/Validators/BaseValidator.php` | Base for all input validators |

### Key Services (by domain)

| Domain | Service | File |
|--------|---------|------|
| Patients | PatientService | `src/Services/PatientService.php` (~983 lines) |
| Encounters | EncounterService | `src/Services/EncounterService.php` (~781 lines) |
| Appointments | AppointmentService | `src/Services/AppointmentService.php` (~696 lines) |
| Clinical Notes | ClinicalNotesService | `src/Services/ClinicalNotesService.php` (~611 lines) |
| Insurance | InsuranceService | `src/Services/InsuranceService.php` (~587 lines) |
| Prescriptions | PrescriptionService | `src/Services/PrescriptionService.php` (~388 lines) |
| Allergies | AllergyIntoleranceService | `src/Services/AllergyIntoleranceService.php` (~369 lines) |
| Conditions | ConditionService | `src/Services/ConditionService.php` (~293 lines) |
| Clinical Rules | DecisionSupportInterventionService | `src/Services/DecisionSupportInterventionService.php` (~269 lines) |
| Vitals | VitalsService | `src/Services/VitalsService.php` |
| Lab Results | ObservationLabService | `src/Services/FHIR/FhirObservationService.php` |
| Documents | DocumentService | `src/Services/DocumentService.php` |
| Users | UserService | `src/Services/UserService.php` |
| Facilities | FacilityService | `src/Services/FacilityService.php` |
| Messages | MessageService | `src/Services/MessageService.php` |

### Database Layer

| File | Purpose |
|------|---------|
| `src/Common/Database/QueryUtils.php` | Static query helpers: fetchRecords(), listTableFields(), escapeTableName() |
| `src/Common/Database/DatabaseQueryTrait.php` | Trait for DB access in non-service classes |
| `src/Common/Database/QueryPagination.php` | Pagination support |
| `src/Common/Uuid/UuidRegistry.php` | UUID management for FHIR interoperability |
| `sql/database.sql` | Complete base schema |

---

## Event System Reference

### Access Pattern

```php
// Get the dispatcher (the ONLY correct way - PHPStan enforced)
$dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();

// Dispatch an event
$dispatcher->dispatch($event, EventClass::EVENT_HANDLE);

// Listen for an event
$dispatcher->addListener(PatientCreatedEvent::EVENT_HANDLE, function($event) {
    // handle
});
```

### Event Directory Map

| Directory | Key Events | Use For |
|-----------|-----------|---------|
| `src/Events/Patient/` | BeforePatientCreatedEvent, PatientCreatedEvent, BeforePatientUpdatedEvent, PatientUpdatedEvent, PatientUpdatedEventAux, PatientBeforeCreatedAuxEvent | Patient lifecycle hooks |
| `src/Events/Encounter/` | EncounterFormsListRenderEvent, EncounterMenuEvent, EncounterButtonEvent, EncounterLoadFormFilterEvent | Encounter workflow |
| `src/Events/Appointments/` | AppointmentSetEvent, AppointmentRenderEvent, AppointmentDialogCloseEvent, AppointmentCustomFilterEvent, CalendarCustomFilterEvent | Scheduling |
| `src/Events/Services/` | ServiceSaveEvent (save.pre/save.post), ServiceDeleteEvent (delete.pre/delete.post) | Generic service hooks |
| `src/Events/RestApiExtend/` | RestApiCreateEvent, RestApiResourceServiceEvent, RestApiScopeEvent, RestApiSecurityCheckEvent | API extension |
| `src/Events/PatientDocuments/` | PatientDocumentCreateCCDAEvent, PatientDocumentStoreEvent, PatientDocumentRetrieveEvent | Document management |
| `src/Events/Messaging/` | SendNotificationEvent, SendSmsEvent | Communication |
| `src/Events/Core/` | ModuleLoadEvents, SQLUpgradeEvent, TwigEnvironmentEvent, TemplatePageEvent | System lifecycle |
| `src/Events/Patient/Summary/Card/` | RenderEvent | Patient summary card widgets |
| `src/Events/Main/Tabs/` | RenderEvent | Main navigation tabs |
| `src/Events/UserInterface/` | PageHeadingRenderEvent, ActionButtonEvent | UI customization |
| `src/Events/Facility/` | FacilityCreatedEvent, FacilityUpdatedEvent | Facility management |
| `src/Events/PatientFinder/` | PatientFinderFilterEvent | Patient search modification |
| `src/Events/Billing/Payments/` | Payment events | Financial workflow |
| `src/Events/Globals/` | GlobalsInitializedEvent | Configuration |

### Filter Events (BoundFilter Pattern)

```php
// Modify database queries by adding WHERE clauses
$dispatcher->addListener('patientFinder.customFilter', function($event) {
    $boundFilter = $event->getBoundFilter();
    $boundFilter->setFilterClause("AND patient_data.status = ?");
    $boundFilter->addBoundValue("active");
    $event->setBoundFilter($boundFilter);
});
```

Base classes: `src/Events/AbstractBoundFilterEvent.php`, `src/Events/BoundFilter.php`

---

## REST API Reference

### Route Files

| File | Endpoint Base | Resources |
|------|---------------|-----------|
| `apis/routes/_rest_routes_standard.inc.php` (258 KB) | `/apis/{site}/api/` | 20+ OpenEMR-native endpoints |
| `apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php` (293 KB) | `/apis/{site}/fhir/` | 30+ FHIR R4 resources |
| `apis/routes/_rest_routes_portal.inc.php` (6 KB) | `/apis/{site}/portal/` | Patient portal (experimental) |

### Standard REST Controllers

| Controller | Endpoint Pattern |
|-----------|-----------------|
| PatientRestController | `/api/patient` |
| EncounterRestController | `/api/encounter` |
| AppointmentRestController | `/api/appointment` |
| ConditionRestController | `/api/condition` |
| AllergyIntoleranceRestController | `/api/allergy` |
| PrescriptionRestController | `/api/prescription` |
| DocumentRestController | `/api/document` |
| FacilityRestController | `/api/facility` |
| InsuranceRestController | `/api/insurance` |
| PractitionerRestController | `/api/practitioner` |
| MessageRestController | `/api/message` |

### FHIR Controllers (`src/RestControllers/FHIR/`)

35 FHIR resource controllers including: FhirPatientRestController,
FhirConditionRestController, FhirObservationRestController,
FhirMedicationRequestRestController, FhirAllergyIntoleranceRestController,
FhirDocumentReferenceRestController, FhirAppointmentRestController,
FhirDiagnosticReportRestController, FhirGenericRestController, plus metadata.

### Authorization Architecture

| File | Purpose |
|------|---------|
| `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php` | OAuth2 bearer token validation |
| `src/RestControllers/Authorization/LocalApiAuthorizationController.php` | Internal API calls |
| `src/RestControllers/Authorization/SkipAuthorizationStrategy.php` | Public endpoints (metadata) |
| `src/RestControllers/Subscriber/AuthorizationListener.php` | Auth enforcement subscriber |
| `src/RestControllers/Subscriber/OAuth2AuthorizationListener.php` | OAuth2 specific |
| `src/RestControllers/Subscriber/CORSListener.php` | CORS headers |
| `src/Common/Acl/AclMain.php` | ACL-based access control |

**Grant types:** Authorization Code (PKCE), Client Credentials (JWKS), Password, Refresh Token, Device Code

**Scope format:** `<context>/<Resource>.<permissions>[?<query>]`
- `patient/Patient.rs` - Read + search patient data
- `user/Observation.cruds` - Full CRUD + search
- `system/*.$export` - Bulk data export

---

## FHIR Implementation

### Service Layer

| File | Purpose |
|------|---------|
| `src/Services/FHIR/FhirServiceBase.php` | Base for all FHIR services |
| `src/Services/FHIR/FhirPatientService.php` | Patient FHIR wrapper |
| `src/Services/FHIR/FhirConditionService.php` | Condition with sub-services (encounters, problems, health concerns) |
| `src/Services/FHIR/FhirObservationService.php` | Observation wrapper |
| `src/Services/Search/FhirSearchWhereClauseBuilder.php` | FHIR search -> SQL WHERE |
| `src/Services/Search/SearchFieldStatementResolver.php` | Individual search field resolution |

### FHIR Service Interfaces (composition-based)

- `IResourceReadableService` - GET operations
- `IResourceSearchableService` - SEARCH operations
- `IResourceCreatableService` - POST operations
- `IResourceUpdateableService` - PUT operations
- `IFhirExportableResourceService` - Bulk export support
- `IPatientCompartmentResourceService` - Patient context filtering
- `IResourceUSCIGProfileService` - US Core compliance

### Search Field Types

`TokenSearchField`, `StringSearchField`, `DateSearchField`,
`ReferenceSearchField`, `CompositeSearchField` in `src/Services/Search/`

---

## Custom Module System

### Production Modules (in `/interface/modules/custom_modules/`)

| Module | Purpose |
|--------|---------|
| `oe-module-claimrev-connect` | Claims processing integration |
| `oe-module-comlink-telehealth` | Telehealth integration |
| `oe-module-dashboard-context` | Dashboard context |
| `oe-module-dorn` | DORN lab integration |
| `oe-module-ehi-exporter` | EHI export functionality |
| `oe-module-faxsms` | Fax/SMS integration |
| `oe-module-prior-authorizations` | Prior authorization management |
| `oe-module-weno` | Weno prescribing integration |

### Module Bootstrap Template

```php
// openemr.bootstrap.php (required entry point)
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\MyModule\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);
$bootstrap = new \OpenEMR\Modules\MyModule\Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
```

### Module Lifecycle

- Modules are loaded via `ModulesApplication::bootstrapCustomModules()`
- Database `modules` table controls active state (`mod_active = 1`)
- Implement `ModuleManagerListener` (extends `AbstractModuleActionListener`) for install/enable/disable/uninstall hooks
- **Safety:** Modules that fail 3 times are auto-disabled

### Example Event Subscribers (in tests)

| Directory | Demonstrates |
|-----------|-------------|
| `tests/eventdispatcher/RestApiEventHookExample/` | Extending REST API routes |
| `tests/eventdispatcher/oe-patient-create-update-hooks-example/` | Patient event listeners |
| `tests/eventdispatcher/oe-modify-patient-menu-example/` | Menu customization |

---

## Agent-Adjacent Infrastructure

These existing systems are directly relevant for building AI/agent features:

| System | Location | Capability |
|--------|----------|------------|
| DecisionSupportInterventionService | `src/Services/DecisionSupportInterventionService.php` | Existing CDS framework; supports Evidence-based and Predictive DSI types; integrates with SMART clients |
| BackgroundTaskManager | `src/Telemetry/BackgroundTaskManager.php` | Manages recurring background tasks via `background_services` table; create, modify, enable, disable tasks |
| ClinicalDecisionRules | `src/ClinicalDecisionRules/` | Rule-based clinical decision support with AMC (Automated Measure Calculation) |
| SMS/Email Reminders | `modules/sms_email_reminder/` | Reference implementation for cron-based patient communication |

---

## CI/CD Pipeline

### Key Workflows (27 total in `.github/workflows/`)

| Category | Workflow | Purpose |
|----------|----------|---------|
| Testing | `test-all.yml` | PHPUnit across PHP 8.2-8.6, multiple DB versions |
| Testing | `isolated-tests.yml` | No-Docker tests (fast) |
| Quality | `phpstan.yml` | Static analysis (Level 10) |
| Quality | `styling.yml` | PSR-12 code style (phpcs) |
| Quality | `rector.yml` | Code modernization checks |
| Security | `semgrep.yml` | SAST scanning with OpenEMR-specific rules |
| FHIR | `inferno-test.yml` | FHIR certification (3-hour timeout) |
| JS | `js-test.yml` | Jest unit tests |
| Deps | `composer.yml` | Dependency validation |
| Convention | `conventional-commits.yml` | PR title validation |
| Database | `database.yml` | Doctrine migration validation |

### CI Hints

- Use `Skip-Slow-Tests: true` commit trailer during development for faster CI
- Rector runs with `--dry-run` in CI (Code Quality 5, Dead Code 5, Type Coverage 5, 12 parallel jobs)
- PHPStan uses baseline approach - only new violations fail CI

---

## Configuration Files

| File | Purpose |
|------|---------|
| `composer.json` | PHP deps, scripts (code-quality, phpstan, phpcs, phpunit-isolated, etc.) |
| `package.json` | JS deps, build scripts (build, dev, lint:js, stylelint) |
| `phpunit.xml` | Docker test config |
| `phpunit.isolated.xml` | Host-only test config |
| `phpstan.neon` | Static analysis config (Level 10, custom rules) |
| `.phpcs.xml` | Code style config (PSR-12) |
| `rector.php` | Code modernization rules |
| `docker/development-easy/docker-compose.yml` | Dev environment |
| `.pre-commit-config.yaml` | Pre-commit hooks |

---

## Patterns to Follow

### Creating a New Service

```php
namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Validators\ProcessingResult;

class MyFeatureService extends BaseService
{
    public const TABLE_NAME = "my_feature";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getById($id): ProcessingResult
    {
        $result = new ProcessingResult();
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE id = ?";
        $records = QueryUtils::fetchRecords($sql, [$id]);
        foreach ($records as $record) {
            $result->addData($record);
        }
        return $result;
    }
}
```

### Listening to Events in a Module

```php
namespace OpenEMR\Modules\MyModule;

use OpenEMR\Events\Patient\PatientCreatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function subscribeToEvents(): void
    {
        $this->dispatcher->addListener(
            PatientCreatedEvent::EVENT_HANDLE,
            [$this, 'onPatientCreated']
        );
    }

    public function onPatientCreated(PatientCreatedEvent $event): void
    {
        $patientData = $event->getPatientData();
        // Process the new patient
    }
}
```

### Extending REST API Routes

```php
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;

$dispatcher->addListener(RestApiCreateEvent::EVENT_HANDLE, function(RestApiCreateEvent $event) {
    $event->addToRouteMap([
        '/api/my-feature/:id' => [
            'GET' => [MyRestController::class, 'getOne'],
            'PUT' => [MyRestController::class, 'put'],
        ],
    ]);
});
```

---

## Agent Proposal Summary

Six agent concepts have been analyzed (full details in `AGENT_PLANNING_REPORT.md`):

| # | Agent | Phase | Complexity | Key Integration |
|---|-------|-------|------------|-----------------|
| 1 | Clinical Documentation Assistant | 2 | High | Encounter events, ClinicalNotesService, LLM API |
| 2 | Intelligent Appointment Scheduler | 2 | Medium | AppointmentService, appointment events, ML model |
| 3 | Medication Safety Agent | 1 | High | PrescriptionService, save.pre events, allergy cross-ref |
| 4 | Lab Result Alert Agent | 1 | Medium | ObservationLabService, save events, trend analysis |
| 5 | Prior Authorization Agent | 3 | Very High | InsuranceService, external payor APIs, workflow engine |
| 6 | Population Health Agent | 3 | High | FHIR Bulk Export, quality measures, cron batch |

**Recommended approach:** Hybrid architecture - OpenEMR custom module for event
hooks and UI integration, external microservice for AI/ML inference.

Additional agents proposed by parallel research:
- **Code Quality & Migration Agent** - Progressive legacy-to-modern migration
- **FHIR Interoperability Agent** - Cross-system data exchange
- **Patient Communication Agent** - Automated personalized outreach
- **Automated Testing Agent** - Coverage gap identification and test generation
- **Smart Documentation Agent** - Living documentation from code analysis
