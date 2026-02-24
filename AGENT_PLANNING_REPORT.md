# OpenEMR Agent Planning Report

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Codebase Architecture Overview](#codebase-architecture-overview)
3. [Extension Points & Integration Surfaces](#extension-points--integration-surfaces)
4. [Agent Ideas & Proposals](#agent-ideas--proposals)
5. [Architectural Decisions & Trade-offs](#architectural-decisions--trade-offs)
6. [Implementation Roadmap](#implementation-roadmap)
7. [Testing Strategy](#testing-strategy)

---

## Executive Summary

OpenEMR is a mature, open-source electronic health records (EHR) system built on PHP 8.2+, using Laminas MVC, Symfony components, and a rich event-driven architecture. The codebase provides **three primary integration surfaces**: a REST/FHIR API with OAuth2 authentication, a Symfony EventDispatcher-based event system with 60+ event types, and a custom module system supporting PSR-4 namespaced plugins.

This report analyzes the full architecture and proposes **six distinct agent concepts** that could be built as OpenEMR modules or external integrations, along with the architectural decisions each would require.

---

## Codebase Architecture Overview

### Directory Structure

| Directory | Purpose |
|-----------|---------|
| `/src/` | Modern PSR-4 code (`OpenEMR\` namespace) - services, events, FHIR, controllers |
| `/library/` | Legacy procedural PHP code |
| `/interface/` | Web UI controllers, templates, and module host |
| `/templates/` | Twig 3.x (modern) and Smarty 4.5 (legacy) templates |
| `/tests/` | PHPUnit 11 test suite (unit, API, e2e, services, isolated) |
| `/sql/` | Database schema and migration scripts |
| `/modules/` | Third-party module directory |
| `/docker/` | Docker development environment |

### Technology Stack

- **Backend:** PHP 8.2+, Laminas MVC, Symfony EventDispatcher, ADODB
- **Frontend:** AngularJS 1.8, jQuery 3.7, Bootstrap 4.6
- **Templates:** Twig 3.x (new), Smarty 4.5 (legacy)
- **API:** REST + FHIR R4, OAuth2/OpenID Connect, SMART on FHIR v2.2.0
- **Database:** MySQL via ADODB wrapper, QueryUtils abstraction
- **Testing:** PHPUnit 11, Jest 29, Symfony Panther (E2E)
- **Build:** Gulp 4, SASS
- **Standards:** US Core 8.0, HIPAA compliant, ONC Cures Update

### Request Flow (API)

```
HTTP Request
  -> dispatch.php
    -> ApiApplication
      -> OAuth2 Authorization
        -> Route Matching (_rest_routes.inc.php)
          -> Controller (RestController pattern)
            -> Service (extends BaseService)
              -> Database (QueryUtils / ADODB)
                -> ProcessingResult response
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

    // Methods return ProcessingResult objects
    // Built-in event dispatcher access via $this->getEventDispatcher()
    // Auto-discovers table fields on construction
}
```

Key services and their line counts (indicating complexity):
- `PatientService.php` (~983 lines) - Patient demographics CRUD
- `EncounterService.php` (~781 lines) - Clinical encounter management
- `AppointmentService.php` (~696 lines) - Scheduling and appointments
- `ClinicalNotesService.php` (~611 lines) - Clinical documentation
- `InsuranceService.php` (~587 lines) - Insurance and billing
- `PrescriptionService.php` (~388 lines) - Medication management
- `AllergyIntoleranceService.php` (~369 lines) - Allergy tracking
- `ConditionService.php` (~293 lines) - Diagnoses and conditions
- `DecisionSupportInterventionService.php` (~269 lines) - Clinical rules

---

## Extension Points & Integration Surfaces

### 1. Event System (60+ Events)

OpenEMR uses Symfony EventDispatcher with a comprehensive event catalog:

#### Patient Lifecycle Events
- `patient.created` / `patient.before-created` - Before/after patient creation
- `patient.updated` / `patient.before-updated` - Before/after patient update
- `patient.updated.aux` / `patient.before-created-aux` - Auxiliary data hooks

#### Encounter & Clinical Events
- `forms.encounter.list.render.pre/post` - Encounter form rendering
- `encounter.load_form_filter` - Form loading filter
- Encounter menu and button rendering events

#### Appointment Events
- `appointment.set` - After appointment creation
- `appointments.customFilter` / `calendar.customFilter` - Query filters
- Appointment dialog and rendering events

#### REST API Extension Events
- `restConfig.route_map.create` - Add custom REST routes
- `restapi.service.get` - Provide custom service implementations
- `api.scope.get-supported-scopes` - Register OAuth scopes
- `api.route.security.check` - Custom security checks

#### UI/Template Events
- `core.twig.environment.create` - Twig environment customization
- `html.head.script.filter` / `html.head.style.filter` - Asset injection
- `oemrui.page.header.render` - Page heading customization
- Main tabs rendering (nav/pre/post)

#### Service Lifecycle Events
- `save.pre` / `save.post` - Generic service save hooks
- `delete.pre` / `delete.post` - Generic service delete hooks

#### Document & Messaging Events
- Patient document CCDA creation/viewing
- External document storage/retrieval
- SMS sending and notification events

#### Filter Events (BoundFilter Pattern)
```php
// Modify database queries via event listeners
$eventDispatcher->addListener('patientFinder.customFilter', function($event) {
    $boundFilter = $event->getBoundFilter();
    $boundFilter->setFilterClause("AND patient_data.status = ?");
    $boundFilter->addBoundValue("active");
    $event->setBoundFilter($boundFilter);
});
```

### 2. Custom Module System

Modules live in `/interface/modules/custom_modules/` and require:

**Minimal structure:**
```
oe-module-my-agent/
  openemr.bootstrap.php    # Required - entry point
  src/
    Bootstrap.php           # Class-based bootstrap (recommended)
    AgentService.php        # Business logic
  templates/                # Twig templates
  public/                   # Static assets, config pages
  sql/                      # Database migrations
```

**Bootstrap pattern:**
```php
// openemr.bootstrap.php
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\MyAgent\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);
$bootstrap = new \OpenEMR\Modules\MyAgent\Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
```

**Module lifecycle management:**
Modules can implement `ModuleManagerListener` (extending `AbstractModuleActionListener`) for install/enable/disable/uninstall hooks.

### 3. REST & FHIR API

Three API surfaces available:
- **Standard REST:** `https://host/apis/{site}/api/` - OpenEMR-native endpoints
- **FHIR R4:** `https://host/apis/{site}/fhir/` - 30+ FHIR resources
- **Patient Portal:** `https://host/apis/{site}/portal/` - Patient-facing (experimental)

**Authentication options:**
- Authorization Code Grant (recommended for user-facing apps)
- Client Credentials Grant (system-to-system, requires JWKS)
- SMART on FHIR launch (EHR-integrated apps)
- Token introspection and refresh token rotation

**Scope model:**
```
<context>/<Resource>.<permissions>[?<query>]
# Examples:
patient/Patient.rs                    # Read + search patient data
user/Observation.cruds                # Full CRUD + search
system/*.$export                      # Bulk data export
patient/Observation.rs?category=vital-signs  # Granular scope
```

---

## Agent Ideas & Proposals

### Agent 1: Clinical Documentation Assistant

**Purpose:** Help clinicians generate, improve, and code clinical notes during encounters.

**How it works:**
- Hooks into encounter form rendering events to add an "AI Assist" panel
- Listens to `encounter.load_form_filter` and SOAP note events
- Uses patient context (conditions, medications, allergies, vitals) to pre-populate note templates
- Suggests ICD-10 and CPT codes based on narrative text
- Flags documentation gaps (missing HPI elements, incomplete assessments)

**Integration points:**
- `EncounterService` - Retrieve encounter context
- `ClinicalNotesService` - Read/write clinical notes
- `ConditionService` - Active problem list for context
- `VitalsService` - Recent vitals for auto-population
- Encounter form rendering events for UI injection

**Architecture:**
- Custom module with Twig template for the AI panel
- Backend service calling an LLM API for note generation/coding suggestions
- Event listeners for encounter lifecycle
- REST API endpoint for async AI processing

**Complexity:** High
**Clinical value:** Very High

---

### Agent 2: Intelligent Appointment Scheduler

**Purpose:** Optimize scheduling by predicting no-shows, suggesting optimal times, and balancing provider workloads.

**How it works:**
- Analyzes historical appointment data (completion rates, patient patterns)
- Predicts no-show probability for each scheduled appointment
- Suggests overbooking strategies for high-risk slots
- Recommends appointment durations based on visit type and patient complexity
- Sends smart reminders (timing and channel optimized per patient)

**Integration points:**
- `AppointmentService` - CRUD operations, status tracking
- `appointment.set` event - React to new appointments
- `appointments.customFilter` - Augment appointment displays with risk scores
- Calendar rendering events for visual indicators
- Patient demographics for contact preferences

**Architecture:**
- Custom module with dashboard widget via card rendering events
- ML model trained on historical appointment data
- Cron-based batch predictions (stored in module table)
- Real-time scoring on appointment creation events
- REST endpoint for the scheduler UI

**Complexity:** Medium
**Clinical value:** High

---

### Agent 3: Medication Safety & Reconciliation Agent

**Purpose:** Real-time drug interaction checking, allergy cross-referencing, and medication reconciliation across care transitions.

**How it works:**
- Monitors medication events (prescriptions, orders, updates)
- Cross-references new medications against existing prescriptions, allergies, and conditions
- Checks drug-drug, drug-allergy, and drug-condition interactions
- During encounters, compares documented medications with pharmacy/patient-reported lists
- Generates reconciliation reports highlighting discrepancies

**Integration points:**
- `PrescriptionService` - Active medication list
- `AllergyIntoleranceService` - Allergy data for cross-checking
- `ConditionService` - Contraindication checking
- Service save events (`save.pre`) - Intercept before medication is saved
- REST API extension event - Add `/api/medication-check` endpoint
- Patient demographics rendering - Display warnings in summary

**Architecture:**
- Custom module with pre-save event interceptors
- Drug interaction database (RxNorm/NLM integration)
- Alert presentation via patient summary card events
- Background reconciliation job comparing multiple medication sources
- FHIR MedicationRequest integration for external data

**Complexity:** High
**Clinical value:** Critical (patient safety)

---

### Agent 4: Lab Result Interpretation & Alert Agent

**Purpose:** Automatically interpret lab results, flag critical values, identify trends, and suggest follow-up actions.

**How it works:**
- Monitors new lab result arrivals
- Compares values against reference ranges with age/sex-specific thresholds
- Identifies longitudinal trends (rising creatinine, declining eGFR, etc.)
- Generates plain-language interpretations for both clinicians and patients
- Creates smart alerts with escalation logic (critical vs. abnormal vs. notable)
- Suggests follow-up tests based on results (reflex testing logic)

**Integration points:**
- `ObservationLabService` - Lab result data access
- Service save events - Trigger on new results
- Patient summary card events - Display trend widgets
- Portal rendering events - Patient-facing interpretations
- Messaging service - Clinician notifications
- REST API extension - Trend data endpoint

**Architecture:**
- Custom module with event-driven processing
- Trend analysis engine with configurable rules
- Patient-facing Twig templates for portal integration
- Clinician dashboard with abnormal result queue
- Background batch processing for longitudinal analysis

**Complexity:** Medium
**Clinical value:** High

---

### Agent 5: Prior Authorization & Insurance Navigation Agent

**Purpose:** Automate prior authorization workflows, verify insurance eligibility, and predict claim outcomes.

**How it works:**
- Identifies procedures/medications requiring prior authorization
- Auto-generates authorization request forms with supporting clinical data
- Tracks authorization status and deadlines
- Verifies insurance eligibility before appointments
- Predicts claim denial risk and suggests documentation improvements
- Manages appeals workflow for denied claims

**Integration points:**
- `InsuranceService` - Coverage verification
- `AppointmentService` - Pre-visit eligibility checks
- `PrescriptionService` / procedure services - Authorization triggers
- `ConditionService` / `ClinicalNotesService` - Supporting documentation
- Service save events - Trigger authorization checks on orders
- REST API extension - External payor API integration
- Calendar events - Pre-visit insurance verification

**Architecture:**
- Custom module with external payor API connectors
- Workflow engine for authorization lifecycle management
- Task queue for async verification requests
- Dashboard for authorization status tracking
- Integration with X12 EDI standards for electronic submission

**Complexity:** Very High
**Clinical value:** High (revenue cycle impact)

---

### Agent 6: Population Health & Care Gap Agent

**Purpose:** Identify patients with care gaps, generate outreach lists, and track quality measures.

**How it works:**
- Runs population-level queries against patient data (conditions, medications, labs, appointments)
- Identifies patients overdue for preventive care (screenings, immunizations, annual visits)
- Calculates quality measure performance (e.g., HEDIS, MIPS measures)
- Generates prioritized outreach lists with contact information
- Tracks care gap closure over time
- Provides clinic-level dashboards with quality metrics

**Integration points:**
- Bulk FHIR API (`system/*.$export`) - Population-level data access
- `PatientService` - Demographics and contact info
- `ConditionService` / `ObservationLabService` - Clinical data
- `AppointmentService` - Scheduling gaps
- `DecisionSupportInterventionService` - Clinical rules integration
- Patient finder filter events - Augment patient lists with gap indicators
- Main tabs rendering - Dashboard widget

**Architecture:**
- Custom module with background batch processing
- Rule engine for configurable quality measures
- Patient outreach queue with communication preferences
- FHIR Bulk Export for data extraction
- Analytics dashboard with Twig templates
- Exportable reports for quality reporting programs

**Complexity:** High
**Clinical value:** High (quality + revenue)

---

## Architectural Decisions & Trade-offs

### Decision 1: Module vs. External Service

| Approach | Pros | Cons |
|----------|------|------|
| **Custom Module** | Deep event integration, access to all services, single deployment, no network latency | PHP-only, limited compute for ML, shares server resources |
| **External Service + API** | Language flexibility (Python for ML), independent scaling, isolation | Network latency, OAuth complexity, limited event hooks |
| **Hybrid** (Module + External) | Best of both: event hooks + external compute | Two deployments, more complex architecture |

**Recommendation:** Hybrid approach for agents requiring AI/ML (1, 2, 4). Pure module for workflow-focused agents (3, 5, 6). The module handles OpenEMR integration (events, UI), while an external microservice handles AI inference.

### Decision 2: Synchronous vs. Asynchronous Processing

| Pattern | Use When | Implementation |
|---------|----------|----------------|
| **Synchronous** | Pre-save validation, real-time alerts, UI rendering | Event listeners returning immediately |
| **Async (Queue)** | AI inference, batch analysis, external API calls | Database job queue + cron worker |
| **Background (Cron)** | Population analytics, trend analysis, bulk operations | Scheduled PHP scripts via cron |

**Recommendation:** Use synchronous for safety-critical checks (medication interactions), async queues for AI-assisted features (note generation), and cron for batch analytics (population health).

### Decision 3: Data Storage Strategy

| Option | Use Case |
|--------|----------|
| **Existing tables** | Augment standard OpenEMR data (e.g., add flags to existing records) |
| **Module-specific tables** | Agent state, predictions, cached results, audit logs |
| **External database** | Large-scale analytics, ML feature stores |

**Recommendation:** Module-specific tables for agent state/predictions, with references to existing OpenEMR records via UUIDs. Use the module's `sql/` directory for schema migrations.

### Decision 4: AI/LLM Integration Pattern

```
Option A: Direct API Call (simplest)
  Module -> HTTP Client -> LLM API (Claude/OpenAI) -> Response

Option B: Local Model (privacy-focused)
  Module -> Local Inference Server (Ollama/vLLM) -> Response

Option C: Agent Framework (most capable)
  Module -> Agent SDK -> Tool Calls -> OpenEMR Services -> Response
```

**Recommendation:** Option A for initial development with Claude API for clinical documentation tasks. Option B for deployments with strict data residency requirements. Option C for complex multi-step clinical reasoning.

### Decision 5: Authentication & Authorization

| Scenario | Auth Method |
|----------|-------------|
| Module accessing services directly | Inherits user session (no extra auth) |
| External service calling OpenEMR API | Client Credentials Grant with JWKS |
| SMART app integrated in EHR | Authorization Code + EHR Launch |
| Background jobs | Service account with system/* scopes |

**Recommendation:** Modules use direct service access (no API overhead). External AI services use Client Credentials Grant. User-facing features use SMART on FHIR for proper context.

### Decision 6: UI Integration Strategy

| Approach | Events Used | Best For |
|----------|-------------|----------|
| **Patient Summary Cards** | `patientSummaryCard.render` | Alerts, scores, quick actions |
| **Encounter Panel** | Encounter form/menu events | Clinical workflow tools |
| **Dashboard Widget** | Main tabs rendering | Analytics, queues, dashboards |
| **Portal Integration** | Portal rendering events | Patient-facing features |
| **Standalone Page** | Module routing | Complex UIs, configuration |

**Recommendation:** Use patient summary cards for clinical alerts (Agents 3, 4). Encounter panels for documentation tools (Agent 1). Dashboard widgets for operational views (Agents 2, 5, 6).

---

## Implementation Roadmap

### Phase 1: Foundation & Quick Wins

1. **Create module skeleton** - Standard bootstrap, namespace, event registration
2. **Implement Agent 3 (Medication Safety)** - Highest patient safety impact
   - Start with allergy-drug cross-checking (synchronous pre-save)
   - Add drug-drug interaction checking
   - Display alerts via patient summary cards
3. **Implement Agent 4 (Lab Alerts)** - Clear rules-based logic
   - Critical value flagging (no AI needed initially)
   - Trend detection with configurable thresholds

### Phase 2: AI-Powered Features

4. **Implement Agent 1 (Documentation Assistant)** - Requires LLM integration
   - SOAP note generation from structured data
   - ICD-10 coding suggestions
   - Documentation gap analysis
5. **Implement Agent 2 (Smart Scheduling)** - Requires ML model
   - No-show prediction model
   - Scheduling optimization

### Phase 3: Complex Workflows

6. **Implement Agent 5 (Prior Auth)** - Requires external API integration
   - Eligibility verification
   - Authorization workflow engine
7. **Implement Agent 6 (Population Health)** - Requires batch infrastructure
   - Quality measure calculations
   - Outreach list generation

---

## Testing Strategy

### Test Types Available

| Test Type | Command | Use For |
|-----------|---------|---------|
| **Isolated** | `composer phpunit-isolated` | Pure logic, no DB (host) |
| **Unit** | `docker exec ... /root/devtools unit-test` | Service-level with DB |
| **API** | `docker exec ... /root/devtools api-test` | REST/FHIR endpoints |
| **E2E** | `docker exec ... /root/devtools e2e-test` | Browser workflows |
| **Services** | `docker exec ... /root/devtools services-test` | Service integration |

### Code Quality

```bash
composer code-quality     # All PHP checks (phpcs, phpstan, rector)
composer phpstan          # Static analysis
composer phpcs            # Code style
npm run lint:js           # JavaScript linting
```

### Agent-Specific Testing

- **Unit tests** for business logic (interaction checking, trend detection)
- **Service tests** for data access layer integration
- **API tests** for custom REST endpoints
- **Isolated tests** for template rendering
- **Mock LLM responses** for AI-dependent features
- **Fixture-based tests** using `FixtureManager` for patient/encounter data

### CI Pipeline

PR submissions trigger:
- Conventional commit validation
- PHP syntax, phpcs, phpstan, rector checks
- Full test matrix (unit, API, E2E, services) across PHP versions
- Use `Skip-Slow-Tests: true` trailer during development for faster feedback

---

## Key Takeaways

1. **OpenEMR's event system is the primary extension mechanism** - 60+ events covering patient lifecycle, encounters, appointments, API routes, and UI rendering.

2. **The custom module system is production-ready** - Multiple real-world modules (fax/SMS, Weno pharmacy, dashboard context) demonstrate the pattern works at scale.

3. **Three API surfaces** (Standard REST, FHIR R4, Portal) provide comprehensive external integration capabilities with OAuth2/SMART authentication.

4. **The service layer is well-structured** - BaseService provides consistent data access, ProcessingResult for responses, and built-in event dispatcher access.

5. **AI agents should use the hybrid approach** - Module for OpenEMR integration (events, UI, services) + external service for AI/ML inference to keep compute separate.

6. **Patient safety agents (medication, lab alerts) should be Phase 1** - Rules-based, high impact, and don't require AI initially.

7. **Testing infrastructure is comprehensive** - Isolated tests for fast iteration, Docker-based tests for integration, and CI validation for quality.
