# OpenEMR Audit Report

**Clinical Co-Pilot — AgentForge Project**
*Audited: 2026-04-27 | Stack: OpenEMR (flex), PHP 8.5.4, MariaDB 11.8.6, Docker*
*Audit environment: local Docker dev (MariaDB). Production deployment uses MySQL on Railway — findings apply identically to both engines (same SQL dialect, same PHP driver, same InnoDB storage).*

---

## Executive Summary

This audit covers the OpenEMR codebase as the foundation for a Clinical Co-Pilot AI agent. Five areas were assessed: security, performance, architecture, data quality, and compliance. The findings below represent the most impactful issues — those that would block a trustworthy agent deployment, not a complete enumeration of every gap.

**The single highest-risk finding is that no de-identification or pseudonymization layer exists between the EHR data store and any future LLM integration.** When a physician queries the agent, the data pipeline would transmit raw Protected Health Information (PHI) — full legal names, dates of birth, Social Security Numbers, phone numbers, addresses, and medical record numbers — directly into LLM prompts. There is no stripping, masking, or pseudonymization step anywhere in the codebase. HIPAA defines 18 categories of PHI; OpenEMR's FHIR API returns all of them. The SSN field is stored in plaintext and is explicitly included in FHIR Patient responses despite a developer comment in the code acknowledging that HL7 advises against it due to identity theft risk. Transmitting raw PHI to an LLM provider without a Business Associate Agreement (BAA) is a direct HIPAA violation; even with a BAA, it violates the minimum necessary principle.

**The second critical finding is a system-wide minimum necessary access violation.** OpenEMR's phpGACL authorization system (Section+Action model) is well-designed in principle: six roles exist (Administrators, Physicians, Clinicians, Front Office, Accounting, Emergency Login) with granular per-section permissions. However, none of these roles are scoped to a provider's own patients. Any authenticated user with `patients|demo` access — including Front Office and Accounting — can query every patient in the system. There is no treating-relationship enforcement at the data layer. The `patient_data.providerID` column exists but is nullable with no database constraint enforcing assignment, and `PatientService.getAll()` does not use it as an access boundary. The GACL `sensitivities|high` permission (intended to gate mental health, HIV, and substance abuse records) gates only encounters whose `sensitivity` field is non-null — but the field is nullable with no enforcement that records be tagged at creation, leaving the gate effectively bypassable.

**The architecture audit reveals a dual-layer problem** with real consequences for data integrity and audit coverage. OpenEMR is mid-migration from a legacy procedural PHP layer (`interface/`, `library/`) to a modern service layer (`src/Services/`). Both layers write to the same clinical tables simultaneously. The legacy layer contains 34 direct SQL write paths to `patient_data` and 90 uses of `sqlStatementNoLog`/`sqlQueryNoLog` — functions that explicitly bypass the audit engine. Writes through these paths leave no audit trail. The service layer applies proper validation (`PatientValidator`); the legacy paths do not. An AI agent reading data written through legacy paths cannot assume that data has passed any validation or left any audit record.

**Performance risk is real but bounded.** The `providerID` column on `patient_data` — which the agent will query on every session — has no index, producing full table scans. The slow query log is disabled. At 53 demo patients this is invisible; at a 500-bed hospital it becomes a latency wall. The FHIR service layer and key clinical tables (encounters, prescriptions, labs) are adequately indexed for single-patient lookups.

**The compliance picture has two anchors.** The `api_log` table stores full request bodies and response JSON — meaning every agent API call would log raw PHI to the database with no visible retention policy or access restriction. Conversely, the SMART on FHIR v2.2.0 + OAuth2 implementation is solid and is the right integration point for the agent: it provides token-scoped access, audit logging per API call, and a path to BAA-compliant operation once a BAA with the LLM provider is in place.

The agent can be built to production quality on this foundation — but only after the PHI pipeline, access scoping, and audit bypass gaps are explicitly addressed in the architecture.

---

## 1. Security Audit

### 1.1 Authentication

OpenEMR uses session-based authentication for the web UI and OAuth2 / SMART on FHIR v2.2.0 for API access. Security headers are correctly configured:

- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- `X-Frame-Options: DENY`
- `Content-Security-Policy: frame-ancestors 'none'`
- Session cookies: `HttpOnly; SameSite=Strict`
- `X-XSS-Protection: 1; mode=block`

CSRF protection is present in 319 files across `interface/` and `library/`. All SQL queries use parameterized statements (ADODB binding) — no raw string interpolation of user input into queries was found.

### 1.2 Authorization — phpGACL (Generic Access Control List)

OpenEMR uses **phpGACL** for post-authentication authorization. The model is:

- **ACO** (Access Control Object): the thing being protected, expressed as `Section|Action`
  - e.g. `patients|med`, `patients|rx`, `encounters|notes`, `sensitivities|high`
- **ARO** (Access Request Object): the authenticated user (by username)
- **ARO Groups**: roles — Physicians, Clinicians, Front Office, Accounting, Administrators, Emergency Login
- **ACL**: maps a role to an ACO with a `return_value` of `view`, `write`, `addonly`, or `wsome`

The core check is:

```php
AclMain::aclCheckCore($section, $action, $user, $return_value)
// e.g. AclMain::aclCheckCore('patients', 'rx', $authUser, 'write')
```

Deny takes precedence over allow. `admin|super` bypasses all checks.

**The GACL model is sound. The enforcement is not.**

ACL checks happen at the call-site — at the top of each PHP page or in each REST route handler. The service layer (`src/Services/`) performs zero ACL checks internally. Any new code path (including the agent endpoint) that omits a `aclCheckCore` call gets silent full database access. This is a structural vulnerability: authorization is opt-in for developers, not enforced by the framework.

**For the agent integration**, every data type fetched must be gated explicitly:

```php
AclMain::aclCheckCore('patients', 'rx', $authUser)       // before fetching medications
AclMain::aclCheckCore('patients', 'lab', $authUser)      // before fetching labs
AclMain::aclCheckCore('encounters', 'notes', $authUser)  // before fetching encounter notes
AclMain::aclCheckCore('sensitivities', 'high', $authUser) // before surfacing sensitive records
```

A new ACO `copilot|query` should be registered so administrators can enable or disable agent access per role without modifying existing permissions.

### 1.3 HIPAA Minimum Necessary Violations

HIPAA's minimum necessary standard requires that access to PHI be limited to the minimum needed to accomplish the intended purpose. The current system violates this at every role boundary.

**No patient-provider scoping:**
`PatientService.getAll()` has no `providerID` filter. Any user with `patients|demo` permission retrieves every patient in the system. There is no treating-relationship check at the data layer.

**Role permission gaps vs. minimum necessary:**


| Role                       | Required Access                           | Current Actual Access                | Gap                                             |
| -------------------------- | ----------------------------------------- | ------------------------------------ | ----------------------------------------------- |
| Physician (attending)      | Own patients' full record                 | ALL patients, full record            | No provider scoping                             |
| Clinician / Nurse          | Nursing records + orders                  | ALL patients, addonly on most fields | No scoping                                      |
| Front Office               | Contact info + appointments, no diagnosis | `patients                            | demo` write (full demographics) on ALL patients |
| Accounting / Billing       | Billing codes + insurance                 | `encounters                          | coding_a` write — exposes diagnosis context    |
| Other-department physician | No access unless consult-authorized       | No such mechanism exists             | Entire concept missing from GACL                |

**Sensitivity enforcement is non-functional:**
The `sensitivities|high` ACO exists to gate high-sensitivity records (mental health, HIV, substance abuse). The check fires only when `form_encounter.sensitivity` is non-null. Of 1,968 encounters in the database, **1,965 (99.8%) have `sensitivity = NULL`**. The gate is open for virtually every record in the system regardless of role.

**Emergency Login:**
The Emergency Login (break-glass) role has full Administrator permissions including `admin|super`. A configurable option exists to force-audit all Emergency Login activity (`Audit all Emergency User Queries`), but its default state should be verified and enforced as enabled.

### 1.4 PHI De-identification Gap — Critical for AI Integration

**PHI (Protected Health Information)** is any health information that can identify an individual. HIPAA defines 18 categories of PHI. OpenEMR stores and exposes all of them:


| HIPAA Identifier                | OpenEMR Field                           |
| ------------------------------- | --------------------------------------- |
| Names                           | `fname`, `lname`, `mname`               |
| Geographic data (sub-state)     | `street`, `city`, `postal_code`         |
| Dates related to individual     | `DOB`, encounter dates, admission dates |
| Phone numbers                   | `phone_home`, `phone_cell`, `phone_biz` |
| Email addresses                 | `email`, `email_direct`                 |
| Social Security Numbers         | `ss` (stored **plaintext**)             |
| Medical record numbers          | `pid`                                   |
| Certificate/license numbers     | `drivers_license`                       |
| Health plan beneficiary numbers | insurance tables                        |

**The FHIR API returns all of these fields in a single Patient resource response**, including SSN as an `identifier` — despite a developer comment in `FhirPatientService.php` line 492 acknowledging that HL7 US Core advises SSNs *should not* be used as patient identifiers due to identity theft risk.

**The agent risk:** There is zero de-identification or pseudonymization code anywhere in the codebase (`src/`, `library/`, `interface/`). If the agent retrieves patient context via the FHIR API and includes it in an LLM prompt, the LLM provider receives raw PHI including legal names, SSNs, dates of birth, and addresses.

- **Without a BAA with the LLM provider**: direct HIPAA violation.
- **With a BAA**: still violates minimum necessary — the LLM does not need name, SSN, or address to answer clinical questions.

**Required mitigation before agent deployment:**


| Approach                      | Method                                                                                             | When to use                                 |
| ----------------------------- | -------------------------------------------------------------------------------------------------- | ------------------------------------------- |
| Pseudonymization              | Replace direct identifiers with session tokens (`Patient-{pid_hash}`); map back after LLM response | Primary approach for clinical conversations |
| Minimized transfer            | Only pass clinical fields (medications, lab values, diagnoses) — no name/SSN/address in prompt    | All agent queries                           |
| Safe Harbor de-identification | Strip all 18 identifier categories                                                                 | Population-level queries                    |

The physician already knows which patient they are discussing. The agent's LLM prompt does not need to contain "Phil Belford, SSN 333-22-3333, DOB 1972-02-09" — it needs medication lists, lab values, and encounter summaries. The identifier stays in the session context on the server; only clinical content crosses the LLM boundary.

### 1.5 PHI in Audit Logs

The `api_log` table captures full `request_body` (longtext) and `response` (longtext) for every API call. If the agent routes through the FHIR API, every query will log a complete PHI payload to the database. No data retention policy, encryption at rest for this table, or access control on who can `SELECT` from `api_log` was identified in the configuration.

---

## 2. Performance Audit

### 2.1 Missing Index on `providerID`

The `patient_data.providerID` column has no database index. A provider-scoped patient list query — which the agent will issue at the start of every session — produces a full table scan:

```sql
EXPLAIN SELECT pid, fname, lname FROM patient_data WHERE providerID = 1;
-- type: ALL  (full table scan, no index used)
```

At 53 demo patients this is invisible. At a 500-bed hospital with thousands of patients, this becomes a blocking latency issue for every agent session start.

**Required fix before production:**

```sql
ALTER TABLE patient_data ADD INDEX idx_provider (providerID);
```

### 2.2 Slow Query Log Disabled

`slow_query_log = OFF` in the MariaDB configuration. There is no visibility into which queries are slow in the current environment. This must be enabled before any performance baseline can be established for agent response latency targets.

### 2.3 Encounter Join Uses Filesort

The standard patient-encounter query uses a `pid_encounter` composite index on `form_encounter`, but the `ORDER BY fe.date DESC` produces a `filesort` (confirmed via EXPLAIN). For a physician's encounter history this is acceptable at small scale; at high concurrent load it will degrade.

### 2.4 Key Table Index Coverage


| Table              | Indexed Columns                             | Agent Query Pattern        | Assessment                  |
| ------------------ | ------------------------------------------- | -------------------------- | --------------------------- |
| `patient_data`     | `pid` (PK), `uuid`, `lname+fname`, `DOB`    | Lookup by pid or uuid      | Good — missing`providerID` |
| `form_encounter`   | `pid+encounter` (composite), `date`, `uuid` | Patient encounter history  | Good                        |
| `prescriptions`    | `patient_id`, `uuid`                        | Medication list by patient | Good                        |
| `procedure_result` | (check needed)                              | Lab results                | 8,172 rows — verify index  |

### 2.5 CouchDB for Documents

Unstructured clinical documents are stored in CouchDB alongside the MariaDB relational store. The agent will need to determine which documents are relevant and query CouchDB separately. This adds a second data source with its own latency profile and connection management requirement.

---

## 3. Architecture Audit

### 3.1 System Overview

OpenEMR is a three-tier system:

```
Browser / API Client
        │
        ▼
┌─────────────────────────────────────┐
│  Presentation Layer                 │
│  interface/ (legacy PHP pages)      │
│  apis/ (REST + FHIR dispatch)       │
└─────────────┬───────────────────────┘
              │
              ▼
┌─────────────────────────────────────┐
│  Service Layer                      │
│  src/Services/  (modern PHP 8+)     │
│  src/RestControllers/               │
│  src/FHIR/                          │
└─────────────┬───────────────────────┘
              │
              ▼
┌─────────────────────────────────────┐
│  Data Layer                         │
│  MariaDB 11.8 (structured data)     │
│  CouchDB 3.5 (documents)            │
└─────────────────────────────────────┘
```

285 tables in MariaDB. Key clinical tables: `patient_data`, `form_encounter`, `prescriptions`, `procedure_result`, `lists` (problems/diagnoses), `immunizations`.

### 3.2 The Dual-Layer Problem

OpenEMR is in an active multi-year migration from the legacy procedural layer to the service layer. **Both layers are live and write to the same tables simultaneously.**

**Three distinct write paths to `patient_data`:**


| Path                                           | Validation                                           | ACL Enforcement | Audit Trail                                    |
| ---------------------------------------------- | ---------------------------------------------------- | --------------- | ---------------------------------------------- |
| `src/Services/PatientService`                  | `PatientValidator` (fname, lname, DOB, sex required) | Caller-enforced | `sqlStatement` → logged                       |
| `library/patient.inc.php::updatePatientData()` | Delegates to PatientService                          | Caller-enforced | Logged (via service)                           |
| `interface/forms/*/save.php` (34 files)        | **None**                                             | Ad-hoc per-page | Uses`sqlQuery` — some use `sqlStatementNoLog` |

**Audit bypass scale:**

- 43 uses of `sqlStatementNoLog`/`sqlQueryNoLog` in `interface/`
- 47 uses in `library/`
- **90 PHI write/read paths that intentionally skip the audit engine**

These were intended for narrow performance cases (CDR engine, session writes) but have spread across clinical data paths.

**Implication for the agent:** Data the agent reads may have been written through any of these paths. A record written via `interface/forms/eye_mag/save.php` has no audit trail and passed no validation. The agent cannot assume records it reads have been validated or that their creation was audited.

### 3.3 ACL Enforcement Architecture

Authorization is enforced at the call-site, not inside the service layer. `BaseService` and `PatientService` contain no `AclMain` calls. This means:

- The REST API enforces ACL at the route level via `RestConfig::request_authorization_check()`
- The legacy UI enforces ACL at the top of each PHP page (inconsistent, manually maintained)
- The service layer trusts the caller to have checked permissions already

Any new endpoint (including the agent endpoint) that forgets to call `aclCheckCore` gets full database access with no enforcement.

### 3.4 Agent Integration Points

The cleanest integration point is the **FHIR R4 REST API** via **SMART on FHIR v2.2.0 + OAuth2**. This provides:

- Token-scoped access (request only the scopes needed: `patient/Patient.rs`, `patient/MedicationRequest.rs`, `patient/Observation.rs`, etc.)
- Per-call audit logging via `api_log`
- Standard FHIR resource format for all clinical data types
- OAuth2 client registration and token introspection endpoints

Available FHIR resources relevant to the agent:


| FHIR Resource        | Clinical Data             | OpenEMR Controller                     |
| -------------------- | ------------------------- | -------------------------------------- |
| `Patient`            | Demographics, identifiers | `FhirPatientRestController`            |
| `Encounter`          | Visit history             | `FhirEncounterRestController`          |
| `MedicationRequest`  | Prescriptions             | `FhirMedicationRequestRestController`  |
| `Observation`        | Lab results, vitals       | `FhirObservationRestController`        |
| `Condition`          | Problem list, diagnoses   | `FhirConditionRestController`          |
| `AllergyIntolerance` | Allergies                 | `FhirAllergyIntoleranceRestController` |
| `DiagnosticReport`   | Lab panels                | `FhirDiagnosticReportRestController`   |
| `Immunization`       | Vaccination history       | `FhirImmunizationRestController`       |

The agent should register as an OAuth2 client with the minimum necessary scopes and use the FHIR API exclusively — not call the service layer or database directly.

---

## 4. Data Quality Audit

### 4.1 Sample Data Shipped with the Codebase

OpenEMR ships an official sample dataset at `sql/example_patient_data.sql` containing 14 example patients (e.g., Farrah Rolle, Ted Shaw, Eduardo Perez). These records have realistic `providerID` assignments (1, 4, 5) and complete name/DOB/SSN fields. The audit findings below describe **structural data quality issues in the schema and write paths**, not row-level statistics from any imported dataset.

### 4.2 Schema-Level Nullability Risks

The following fields are nullable with no database-level enforcement, despite being load-bearing for clinical decision making and access control:


| Field         | Table            | Nullable | Default | Agent Impact                                                                                             |
| ------------- | ---------------- | -------- | ------- | -------------------------------------------------------------------------------------------------------- |
| `providerID`  | `patient_data`   | YES      | NULL    | Provider scoping cannot be enforced when field is unset                                                  |
| `sensitivity` | `form_encounter` | YES      | NULL    | `sensitivities|high` ACL gate fires only when populated — records never tagged are visible to all roles |
| `drug_id`     | `prescriptions`  | NO       | 0       | Default`0` does not join to any `drugs` row — orphaned references                                       |
| `end_date`    | `prescriptions`  | YES      | NULL    | Active vs. discontinued medications cannot be distinguished by date alone                                |

The `providerID` and `sensitivity` nullability is the most serious: both are referenced by access control logic, but neither is enforced at write time. Any code path that creates a record without setting these fields silently bypasses the controls that depend on them.

### 4.3 Unstructured Free-Text Fields

The following fields contain unstructured physician-entered text, which represents high variability and hallucination risk for an AI agent:

- `form_encounter.reason` — visit reason (longtext, free text, no controlled vocabulary)
- `prescriptions.drug` — medication name (varchar 150, free text, no enforced RxNorm)
- `prescriptions.dosage` — dosage (varchar 100, free text)
- `prescriptions.note` — prescription notes (mediumtext)

The agent must treat these fields as unverified free text and clearly attribute them to source rather than presenting them as structured clinical facts.

### 4.4 Prescription Data Quality

The `prescriptions` table has multiple write paths (PrescriptionService, eRxStore.php, drug dispensing forms). Fields created by different paths have different fill rates:

- `rxnorm_drugcode`: inconsistently populated — no drug has a guaranteed standard code
- `drug_id`: defaults to `0` (not null) — cannot reliably join to the `drugs` reference table
- `end_date`: nullable — active vs. inactive medications cannot always be distinguished by date alone

### 4.5 Encounter Sensitivity Tagging

The `form_encounter.sensitivity` field is the sole hook for the `sensitivities|high` ACL gate. The schema does not require it to be set, and the standard encounter creation forms in `interface/forms/` do not surface sensitivity selection by default. In practice this means high-sensitivity encounters (mental health, HIV, substance abuse) rely entirely on clinician discipline to tag — there is no system-level guarantee that they will be. The agent must assume the sensitivity field is unreliable and apply additional content-based heuristics if it surfaces clinically sensitive material.

---

## 5. Compliance & Regulatory Audit

### 5.1 HIPAA Overview

HIPAA (Health Insurance Portability and Accountability Act) governs the handling of Protected Health Information (PHI). The key rules for this system are:

- **Privacy Rule**: minimum necessary access, patient rights, use limitation
- **Security Rule**: administrative, physical, and technical safeguards for electronic PHI (ePHI)
- **Breach Notification Rule**: notification obligations when PHI is impermissibly disclosed
- **BAA Requirement**: any vendor handling PHI on behalf of a covered entity must sign a Business Associate Agreement

### 5.2 BAA Requirements for LLM Providers

Sending PHI to an LLM API (Claude, OpenAI, etc.) makes that provider a **Business Associate** under HIPAA. A BAA must be in place before any PHI is transmitted. For the purposes of this project, all LLM providers are treated as having a signed BAA per Gauntlet AI project guidelines.

However, having a BAA does not eliminate the minimum necessary obligation. Even with a BAA, the system must not transmit more PHI than necessary for the AI to perform its function. Transmitting SSNs, full addresses, or phone numbers to an LLM that is answering clinical questions violates this standard regardless of BAA status.

### 5.3 Audit Logging Requirements

HIPAA requires audit controls that record and examine activity in systems containing ePHI (Security Rule § 164.312(b)).

**Current audit coverage:**


| Mechanism                        | What it captures                                                            | Gap                                               |
| -------------------------------- | --------------------------------------------------------------------------- | ------------------------------------------------- |
| `log` table                      | Login events, admin actions — with checksum for tamper evidence            | Checksums are NULL for some events (inconsistent) |
| `audit_master` + `audit_details` | Patient record changes (approval workflow)                                  | Scoped to specific approval flows, not all writes |
| `api_log`                        | Full request + response for every API call (including PHI in response body) | No retention policy; response body stores raw PHI |
| `extended_log`                   | Additional event logging                                                    | Limited scope                                     |
| `sqlStatementNoLog` paths        | **Not captured** (90 call sites)                                            | Clinical writes with zero audit trail             |

**The `api_log` retention problem:** This table stores `response longtext` — the full JSON body of every API response. For FHIR Patient queries, this is a complete PHI record. HIPAA requires that audit logs themselves be protected and that PHI in logs be subject to the same access controls as the primary data. No configuration was found governing who can access `api_log` or how long records are retained.

### 5.4 Data Retention

No data retention or purge policy was identified in the OpenEMR configuration for any table. HIPAA requires covered entities to retain medical records per state law (typically 6–10 years) and to have a process for secure destruction. The absence of a visible retention policy is a compliance gap for a production deployment.

### 5.5 Encryption at Rest

PHI fields in `patient_data` are stored in plaintext in MariaDB. Specifically:

- `ss` (Social Security Number): plaintext — confirmed by query (`333222333`, `112-33-5544`)
- `drivers_license`: plaintext
- `phone_home`, `phone_cell`, `email`: plaintext

An `src/Encryption/CipherSuite.php` module exists with a full AES key management system (keychain, key rotation), but it is not applied to any `patient_data` fields. The encryption infrastructure exists but is unused for the most sensitive identifier fields.

### 5.6 Compliance Summary Table


| Requirement                                     | Status              | Detail                                                                                                 |
| ----------------------------------------------- | ------------------- | ------------------------------------------------------------------------------------------------------ |
| BAA with LLM provider                           | Not established     | Must be in place before agent deployment                                                               |
| PHI de-identification before LLM                | **Not implemented** | Raw PHI including SSN flows to FHIR API response                                                       |
| Minimum necessary access                        | **Violated**        | No provider scoping; all roles access all patients                                                     |
| Audit logging coverage                          | Partial             | 90 write paths bypass audit engine                                                                     |
| Audit log tamper evidence                       | Inconsistent        | Checksums NULL on some log entries                                                                     |
| SSN / PHI encryption at rest                    | **Not applied**     | CipherSuite exists but unused on patient_data                                                          |
| Data retention policy                           | Not defined         | No purge policy in configuration                                                                       |
| Sensitivity flagging (mental health, HIV, etc.) | **Bypassable**      | `sensitivity` field is nullable with no enforcement at write time — gate fires only on tagged records |
| SMART on FHIR v2.2.0 / OAuth2                   | Implemented         | Solid foundation for agent integration                                                                 |
| CSRF protection                                 | Implemented         | 319 files covered                                                                                      |
| SQL injection protection                        | Implemented         | Parameterized queries throughout                                                                       |
| Security headers                                | Implemented         | HSTS, X-Frame-Options, CSP, SameSite cookies                                                           |

---

## Appendix: Key Files Referenced


| File                                        | Relevance                                                       |
| ------------------------------------------- | --------------------------------------------------------------- |
| `src/Common/Acl/AclMain.php`                | phpGACL wrapper —`aclCheckCore()` implementation               |
| `src/Services/PatientService.php`           | Patient CRUD — validation present, no ACL                      |
| `src/Services/FHIR/FhirPatientService.php`  | FHIR Patient mapper — includes SSN in response                 |
| `library/patient.inc.php`                   | Legacy patient write wrapper — now delegates to PatientService |
| `library/sql.inc.php`                       | SQL abstraction —`sqlStatement` vs `sqlStatementNoLog`         |
| `apis/dispatch.php`                         | REST API entry point                                            |
| `apis/routes/_rest_routes_standard.inc.php` | REST route definitions with per-route ACL checks                |
| `src/FHIR/SMART/SMARTLaunchToken.php`       | SMART launch context — patient scope binding                   |
| `src/Encryption/CipherSuite.php`            | AES encryption module — exists but not applied to PHI fields   |
| `gacl/`                                     | phpGACL library                                                 |
