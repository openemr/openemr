# AUDIT.md
## OpenEMR Pre-Build Audit for the Clinical Co-Pilot

> **Audited build:** the working copy at `/Users/scottlydon/Desktop/Clutter/iOS/openemr` (master branch, OpenEMR 7.x line, PHP 8.2, Laminas + Symfony, MySQL via Doctrine DBAL, FHIR R4 with US Core 3.1.0).
> **Audit owner:** Scott
> **Audit date:** 2026-04-28

---

## Summary (one page)

OpenEMR already exposes the data and integration surfaces a clinical co-pilot needs. It has Fast Healthcare Interoperability Resources (FHIR) R4 with US Core 3.1.0, an OAuth2 bearer-token authorization strategy at `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php`, an Access Control List (ACL) layer in `src/Common/Acl/AclMain.php`, and a centralized `EventAuditLogger` with an optional Audit Trail and Node Authentication (ATNA) sink. The agent should consume those existing surfaces rather than reach into the database. Below are the five most consequential findings, ordered by impact on the build.

First, the codebase is heterogeneous. `/src/` is modern Laminas plus Symfony with PSR-4, strict typing, and Doctrine DBAL via `QueryUtils`. `/library/` and `/interface/` remain procedural with hand-escaped Structured Query Language (SQL) and historical injection patterns. Any agent integration must ride the modern service layer (FHIR controllers, `BaseService` subclasses, `QueryUtils`) so LLM-driven traffic does not amplify legacy risk.

Second, authorization is enforceable but incomplete in shape for AI use. `AclMain` provides role and section permissions, and the OAuth2 flow supports scope-based checks. There is no built-in concept of an "AI acting on behalf of a clinician" principal, and no per-patient consent flag for downstream LLM transmission. Both must be added at the agent gateway.

Third, audit logging is real but optional. `EventAuditLogger` writes to the `log` and `audit_master` tables (with an ATNA TCP sink available), but flags such as `enable_auditlog`, `audit_events_query`, and `audit_events_patient-record` are configuration-driven and frequently off. The agent cannot rely on EMR hooks for AI-specific events. It needs its own immutable audit stream covering every prompt, retrieval, tool call, and response.

Fourth, data quality is the single largest predictor of agent accuracy. The `lists` table is polymorphic (problems, allergies, medications, surgeries) keyed on a free-text `type` column plus `diagnosis` and `title` strings. International Classification of Diseases, 10th Revision (ICD-10) and SNOMED codes coexist with free-text labels, and `verification`, `severity_al`, and `enddate` are nullable. The diagnostic and chart-error use cases both depend on resolving these into typed, coded facts before any LLM call. Reconciliation belongs in deterministic preprocessing, not in the prompt.

Fifth, Protected Health Information (PHI) and HIPAA constrain the model layer. OpenAI offers a Business Associate Agreement (BAA) on the Enterprise tier and a Zero Data Retention (ZDR) endpoint. Azure OpenAI is HIPAA-eligible by default through Microsoft's BAA. Either is acceptable. I recommend OpenAI Enterprise as the primary, given my empirical preference for the underlying models on medical reasoning. Local de-identification of free-text notes before transmission is recommended even with a BAA in place, and AI-side audit logging of every prompt and response is non-negotiable.

Performance: `/interface/` patient summary pages issue dozens of synchronous queries per render. The agent should prefetch a structured "patient snapshot" via FHIR `$everything` (or a dedicated read API) rather than reuse interface code paths. The latency budget for pre-visit prep is roughly 60 to 90 seconds per patient; the bottleneck will be parallel LLM calls, not the database.

The remainder of this document expands each audit dimension.

---

## 1. Security Audit

### 1.1 Authentication

**Where it lives.** `src/Common/Auth/AuthUtils.php` (login flow), `src/Common/Auth/OAuth2KeyConfig.php` (OAuth2 keys), and the OAuth2 server bootstrap under `oauth2/`. The bearer-token strategy used by REST and FHIR is in `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php`.

**Findings.**

1. Session-based login with cookies is the default for the human user interface (UI). Multi-Factor Authentication (MFA) exists in OpenEMR (Time-based One-Time Password, U2F) but is opt-in per user, not enforced globally.
2. Password policy is configurable via globals (`password_strength`, `password_history`, `password_grace_time`) and several installations leave defaults weak.
3. OAuth2 is implemented via `league/oauth2-server` and supports authorization code, client credentials, password, and refresh-token grants. SMART-on-FHIR scopes are honored on the FHIR routes (`_rest_routes_fhir_r4_us_core_3_1_0.inc.php`).
4. There is no first-class "service principal" abstraction for an AI gateway. The agent will need its own OAuth2 client registered with narrow scopes, plus a per-request "delegated user" claim.

**Risks for the agent.**

A long-lived service-account token would let the agent read any patient's chart, defeating per-clinician scoping. Refresh tokens are stored in `oauth_refresh_tokens` (database). If exposed, they grant durable PHI access. Session fixation, Cross-Site Request Forgery (CSRF), and Cross-Site Scripting (XSS) in the UI can be repurposed against the agent if the agent is embedded as an iframe in `/interface/`.

**Mitigations the build will adopt.**

The build uses the **authorization code with PKCE (Proof Key for Code Exchange)** grant from a separate Backend-for-Frontend (BFF) so the LLM never sees a refresh token. MFA is required for any clinician whose session can launch the agent (enforced at the BFF, not in OpenEMR). The BFF mints **short-lived (5 minute) downscoped tokens** per agent task, scoped to a specific `Patient/{id}` compartment using SMART-on-FHIR scopes such as `patient/Condition.r patient/Observation.r patient/MedicationRequest.r`.

### 1.2 Authorization

**Where it lives.** `src/Common/Acl/AclMain.php` (PHP-Generic Access Control Lists, gacl), `src/Common/Acl/AclExtended.php`, `src/Common/Acl/AccessDeniedHelper.php`. FHIR routes layer scope checks in `src/RestControllers/Subscriber/AuthorizationListener.php`.

**Findings.**

1. ACL sections are coarse (e.g. `patients`, `encounters`, `med`, `admin`). There is no row-level security in MySQL; access is enforced in PHP.
2. Multi-facility installations rely on `users_facility` linkage and `gacl_protect` flags. Facility scoping is not uniformly enforced across all controllers, especially in legacy `/interface/` paths.
3. No "break-the-glass" affordance is exposed to API clients today (the EMR has `gbl_force_log_breakglass` but it is UI-driven).

**Risks for the agent.**

The agent could request a chart for a patient outside the clinician's panel, and the FHIR layer alone may not block it (depends on resource and scope). A misconfigured ACL section can silently grant the AI client more than intended.

**Mitigations the build will adopt.**

The agent gateway performs a **second authorization check** independent of OpenEMR: it confirms the (user, patient, purpose) triple against a local policy store before any LLM call. AI access is blocked for patients flagged with `is_sensitive` (HIV, mental health, substance use) unless an explicit consent record exists.

### 1.3 Data Exposure Vectors

| Vector | Notes |
|---|---|
| Direct database read | The agent will not have direct database credentials; all reads go through FHIR or a thin internal Read API. |
| Log files | OpenEMR writes verbose logs in `sites/default/documents/logs_and_misc/`. Logs sometimes echo PHI in error traces. The agent must not aggregate OpenEMR logs into AI observability. |
| LLM prompts | Largest new exposure surface. Mitigation: de-identification before transmission, BAA + ZDR endpoint, immutable AI audit log. |
| Vector store | If embeddings are stored for unstructured notes, the embedded text must remain inside the BAA boundary. Use a self-hosted Postgres + pgvector instance, not a third-party Software-as-a-Service (SaaS). |
| Browser session | If the agent UI is embedded in `/interface/`, XSS in OpenEMR can read the agent's session. Use a separate origin and `postMessage` with a strict allow-list. |

### 1.4 Known Historical Vulnerability Patterns

OpenEMR has a long history of disclosed Common Vulnerabilities and Exposures (CVE)s, primarily SQL injection and reflected XSS in legacy `/interface/` files, plus occasional path traversal in document handlers. The modern `/src/` code is largely clean. The agent must not introduce new code paths under `/interface/`. New code goes in `/src/` and is reachable through a dedicated controller under `src/RestControllers/`.

---

## 2. Performance Audit

### 2.1 Where the system is slow

1. **Patient summary rendering.** `/interface/patient_file/summary/demographics.php` and adjacent views can issue 30 to 60 SELECT statements per render, many over the polymorphic `lists` table without ideal indexes.
2. **`lists` table.** Indexed on `pid` and `type`. Common analytic queries that filter by `diagnosis` (a free-text VARCHAR(255)) end up scanning. ICD-10 code lookups are not always normalized.
3. **`form_*` tables.** Each form type has its own table. Joins for "everything about this encounter" are wide and slow. The FHIR layer is faster because it batches.
4. **Audit writes.** `EventAuditLogger` writes synchronously. With `audit_events_query` enabled, the audit log is written on every SELECT, doubling I/O.
5. **Smarty + Twig template duplication.** Mostly a code-organization issue, but it adds startup cost on cold paths.

### 2.2 Constraints on agent latency

| Workload | Budget | Rationale |
|---|---|---|
| Pre-visit prep (per patient) | 60 to 90 seconds | Runs in batch overnight or during the 8:30 to 8:50 prep window. Latency tolerated. |
| Mid-visit follow-up question | 4 to 8 seconds | Clinician is on a 15 minute slot; anything longer breaks the workflow. |
| Chart-error scan (background) | Hours | Runs as a scheduled job. Throughput, not latency. |

### 2.3 Recommendations

1. Build a **patient snapshot service** that issues a parallel fan-out across the per-resource FHIR endpoints (`Condition`, `MedicationRequest`, `AllergyIntolerance`, `Observation`, `Encounter`, `DocumentReference`, `Procedure`) and caches a denormalized JavaScript Object Notation (JSON) document keyed by `(pid, snapshot_version)`. OpenEMR does not implement `Patient/{id}/$everything`; the closest single-call equivalents it ships are `POST /fhir/DocumentReference/$docref` (per-patient C-CDA generation) and `GET /fhir/Patient/$export` or `GET /fhir/Group/{id}/$export` (asynchronous Bulk Data export). Use `$docref` for cold-start ingest and `$export` for overnight cohort prep; use the per-resource fan-out for the per-visit hot path. The agent reads the snapshot and never the raw tables.
2. Embed unstructured notes only on demand, not eagerly. Per-patient pgvector namespaces.
3. Run pairwise LLM comparisons in **parallel batches** capped at 20 concurrent calls, with structured outputs rather than free-text.
4. Cache LLM judgments keyed on a hash of `(symptom_text, candidate_diagnosis, model_version, prompt_version)`. The same pair will recur across visits.

---

## 3. Architecture Audit

### 3.1 Layout (verified against the working copy)

```
/src/                       PSR-4 modern code (OpenEMR\ namespace)
  ├── Common/Acl/           AclMain, AclExtended (RBAC)
  ├── Common/Auth/          AuthUtils, OAuth2KeyConfig
  ├── Common/Logging/       EventAuditLogger (+ Audit/* sinks: LogTablesSink, AtnaSink)
  ├── Common/Uuid/          UuidRegistry (FHIR resource UUIDs)
  ├── FHIR/R4/              FHIR R4 domain resources (Condition, Observation, AllergyIntolerance, …)
  ├── RestControllers/      REST + FHIR controllers, AuthorizationListener, BearerTokenAuthorizationStrategy
  └── Services/             BaseService subclasses (the right place for new domain services)
/library/                   Legacy procedural helpers
/interface/                 Web UI controllers and templates (Smarty + Twig)
/apis/routes/               Route maps:
                              _rest_routes_standard.inc.php
                              _rest_routes_fhir_r4_us_core_3_1_0.inc.php
                              _rest_routes_portal.inc.php
/sql/database.sql           Schema (lists, form_encounter, patient_data, audit_master, log, …)
/_rest_routes.inc.php       Route bootstrap
```

### 3.2 Where data lives

| Domain | Table(s) | Notes |
|---|---|---|
| Demographics | `patient_data` | One row per patient, `pid` is primary key. |
| Problems, allergies, medications, surgeries | `lists` | Polymorphic on `type`, free-text `diagnosis`/`title`, optional ICD/SNOMED via `list_options`. |
| Encounters | `form_encounter` plus per-form tables | Joins are wide. |
| Vitals | `form_vitals` | Latest-by-encounter pattern. |
| Lab results | `procedure_order`, `procedure_report`, `procedure_result` | Lab Loinc-coded values live here. |
| Medications (prescribed) | `prescriptions` | Distinct from `lists` medication entries; may disagree. |
| Audit | `log`, `audit_master`, `audit_master_query` | Configurable. |
| OAuth2 | `oauth_*` tables | Tokens, clients, codes. |
| Patient consent | `patient_data.allow_*` flags + `lbf_data` for custom forms | Sparse, inconsistent across installs. |

### 3.3 Integration points the agent will use

1. **FHIR R4 per-resource search**, in parallel: `GET /fhir/Condition?patient={uuid}&category=problem-list-item&clinical-status=active`, `GET /fhir/MedicationRequest?patient={uuid}`, `GET /fhir/AllergyIntolerance?patient={uuid}`, `GET /fhir/Observation?patient={uuid}&category=vital-signs`, `GET /fhir/Observation?patient={uuid}&category=laboratory`, `GET /fhir/Encounter?patient={uuid}`, `GET /fhir/DocumentReference?patient={uuid}&category=clinical-note`, `GET /fhir/Procedure?patient={uuid}`. All read-only.
2. **`POST /fhir/DocumentReference/$docref`** for an on-demand Continuity of Care Document Architecture (C-CDA) summary when a single-payload chart pull is needed (cold-start ingest, fallback when the per-resource fan-out is incomplete). See [the OpenEMR `$docref` guide](https://github.com/openemr/openemr/blob/master/Documentation/api/FHIR_API.md#documentreference-docref-operation).
3. **`GET /fhir/Group/{groupId}/$export`** for asynchronous Bulk FHIR export across the clinician's panel (overnight pre-visit prep batch, Use Case B chart-error scan). Returns Newline-Delimited JSON (NDJSON), one file per resource type. See [the OpenEMR Bulk FHIR guide](https://github.com/openemr/openemr/blob/master/Documentation/api/FHIR_API.md#bulk-fhir-exports).
4. **A new internal Read API** under `src/RestControllers/Agent/SnapshotController.php` that returns the denormalized snapshot in one call, signed by the BFF. This wraps the FHIR fan-out with the reconciliation pass.
5. **OAuth2 token exchange** for the AI client (separate `oauth_clients` row, narrow scopes).
6. **Outbound webhook** from OpenEMR after chart edits, so the snapshot cache can invalidate (use the existing Symfony `EventDispatcher` hook surface).

Note: OpenEMR does **not** implement the FHIR `Patient/{id}/$everything` operation. References to it in earlier drafts of this document have been removed. The per-resource fan-out plus `$docref` plus `$export` covers the same ground with better latency on the hot path and structured FHIR resources rather than C-CDA Extensible Markup Language (XML).

### 3.4 What the architecture does **not** offer today

- No row-level security in MySQL.
- No native event stream (Kafka, Pub/Sub) for chart changes.
- No service mesh; calls are direct Hypertext Transfer Protocol (HTTP).
- No native vector search.
- No first-class "purpose of use" claim on tokens.

---

## 4. Data Quality Audit

### 4.1 Findings

1. **Free-text everywhere.** The `lists.diagnosis` column is `VARCHAR(255)` free-text. ICD-10 codes are present in some installations and absent in others. The `title` column duplicates information.
2. **Inconsistent verification.** `lists.verification` references `list_options` but is often empty. "Provisional" vs "confirmed" cannot be distinguished reliably.
3. **Two sources of truth for medications.** `lists` (problem-list-style) and `prescriptions` (e-prescribed) overlap and disagree. The agent must reconcile.
4. **Missing end dates.** Resolved problems often have a null `enddate`, so a patient who recovered from an infection in 2012 still appears "active." This is a direct trigger for false-positive AI inferences in the diagnostic case.
5. **Vitals and labs are sparse.** Outside hospital integrations, vitals come from manual entry. Trend-based reasoning must tolerate gaps.
6. **Duplicate patients.** No probabilistic record linkage. Some sites have duplicate `pid` rows for the same human.
7. **Free-text encounter notes.** Stored in `form_encounter` and various `form_*` tables. Quality varies wildly. Note bloat from copy-forward is common.
8. **Stale problem list.** The Stage 0 narrative I worked from (gout already in chart, ignored) is the canonical example. The data was correct; the surfacing was the failure.

### 4.2 Implications for the agent

The agent must run a **deterministic reconciliation pass** before any LLM call. This means collapsing `lists` and `prescriptions` into a single Medication entity, normalizing diagnoses to ICD-10 plus SNOMED, and inferring "likely active vs likely resolved" using `enddate` plus heuristic rules.

The agent must also **mark provenance** on every fact it presents: table, row id, timestamp, and the user who entered it. This feeds the verification layer in Stage 5.

The agent must **report data gaps** as first-class output (e.g. "no documented thyroid-stimulating hormone (TSH) in the last 5 years"), since the gout case shows that what is missing matters as much as what is present.

---

## 5. Compliance and Regulatory Audit

### 5.1 HIPAA

OpenEMR is HIPAA-capable; the operating organization is the covered entity, and any subprocessor (cloud, LLM, vector store) must be a Business Associate.

| Requirement | OpenEMR today | Gap for agent |
|---|---|---|
| Audit logging of PHI access | `EventAuditLogger` (configurable) | Agent needs its own immutable audit log; OpenEMR's may be off. |
| Minimum necessary | ACL + scopes | Add per-task scope downscoping at the gateway. |
| Encryption at rest | Database-level (depends on Operating System (OS)/MySQL config) | Add encrypted vector store; encrypt snapshot cache. |
| Encryption in transit | Hypertext Transfer Protocol Secure (HTTPS) when configured | Mandatory; agent gateway will refuse plaintext. |
| Access controls | ACL | Add second-layer policy check at gateway. |
| Integrity controls | Limited | Sign every AI artifact with the gateway's key. |
| Breach notification | Operational | Add detection: anomalous access patterns, failed authorization spikes. |
| BAA chain | Operator's responsibility | OpenAI Enterprise BAA + ZDR endpoint, or Azure OpenAI under Microsoft BAA. Pgvector self-hosted inside the same trust boundary. |

### 5.2 Audit logging requirements

The Health Insurance Portability and Accountability Act (HIPAA) Security Rule's audit-controls standard (45 Code of Federal Regulations (CFR) 164.312(b)) requires the ability to record and examine activity in systems containing electronic PHI (ePHI). For an AI agent that means logging, at minimum:

1. Identity of the requesting clinician.
2. Patient compartment accessed.
3. Purpose of use.
4. Inputs to the model (with PHI present marked).
5. Tool calls and their results.
6. Final response shown to the user.
7. Whether the verifier accepted, modified, or blocked the response.
8. Token consumption, latency, and cost.

These logs must be tamper-evident (write-once, hash-chained) and retained per the operator's policy (typically 6 years for HIPAA, longer in some states).

### 5.3 Data retention

Set explicit retention on:

- Snapshot cache: 24 hours rolling, hard-deleted afterward.
- Vector store (per-patient): retained while patient is active; purged on chart deletion.
- AI audit log: 7 years (covers HIPAA 6 plus a buffer).
- LLM provider side: ZDR (zero retention) on OpenAI Enterprise endpoint.

### 5.4 Breach notification

If the agent gateway logs a suspected unauthorized disclosure (e.g. token exfiltration, scope escalation), it must:

1. Quarantine the affected client immediately.
2. Emit a high-priority alert to the privacy officer.
3. Preserve the audit slice for forensic review.

The 60-day breach notification clock starts on discovery.

### 5.5 Sidecar architecture and HIPAA compliance

A team-internal concern raised during architecture review was whether running the agent as a separate sidecar (rather than embedding it inside OpenEMR's PHP process) would compromise HIPAA compliance. It does not. The HIPAA Security Rule does not regulate deployment topology; it regulates safeguards (45 CFR Part 164, Subparts C and D) and the BAA chain. A sidecar is HIPAA-neutral on its own. What matters is that for every place ePHI flows, the safeguards are in place and every party in the chain is a Business Associate.

The mapping from each Security Rule technical safeguard to where this build implements it (full detail in `ARCHITECTURE.md` Section 1.2.1):

- Access control (45 CFR 164.312(a)): OAuth2 with PKCE, 5-minute downscoped tokens per `Patient/{id}` compartment, second-layer policy check on `(user, patient, purpose)` at the BFF.
- Audit controls (45 CFR 164.312(b)): hash-chained append-only `ai_audit_log` retained 7 years, separate from OpenEMR's `audit_master`.
- Integrity (45 CFR 164.312(c)): every artifact signed; periodic anchoring of the audit chain head to write-once external storage.
- Person or entity authentication (45 CFR 164.312(d)): mTLS plus signed JWT between BFF and sidecar; MFA required at the BFF.
- Transmission security (45 CFR 164.312(e)): HTTPS everywhere; egress restricted to BAA-covered LLM endpoints.
- BAA chain (45 CFR 164.308(b), 164.502(e)): OpenAI Enterprise BAA + ZDR primary, Azure OpenAI BAA fallback, self-hosted pgvector inside the trust boundary.

A monolith embedded inside OpenEMR is the riskier choice on several axes (bigger blast radius, no enforceable trust boundary between PHP procedural code and the LLM client, harder to push security patches without redeploying the whole EMR, harder to audit because the call site sits in legacy code with a long CVE tail). The sidecar narrows attack surface; it does not introduce one. External corroboration: [OCR HIPAA Security Series guidance on Technical Safeguards](https://www.hhs.gov/sites/default/files/ocr/privacy/hipaa/administrative/securityrule/techsafeguards.pdf).

### 5.6 BAA implications for LLM provider

| Provider | BAA available | Default ZDR | Recommendation |
|---|---|---|---|
| OpenAI API (Free/Pro) | No | No | Not usable for PHI. |
| OpenAI Enterprise | Yes | Yes (on request) | **Recommended primary**, given my empirical preference. See [OpenAI's HIPAA-readiness guidance](https://openai.com/enterprise-privacy/) and request the BAA via the [Trust Portal](https://trust.openai.com/). |
| Azure OpenAI | Yes (under Microsoft BAA) | Configurable | Strong fallback; many healthcare orgs already have a Microsoft BAA via Microsoft 365 / Azure. See [Azure HIPAA documentation](https://learn.microsoft.com/azure/compliance/offerings/offering-hipaa-hitech). |
| Self-hosted Llama / Mistral / Qwen | Not applicable (no third party) | Not applicable | Useful for a local de-identification step before sending to OpenAI. Models like [Microsoft Presidio](https://github.com/microsoft/presidio) and a local 8B class language model (LM) can run a Protected Health Information (PHI) scrub. |

The build will use **OpenAI Enterprise with BAA + ZDR** as the primary inference path, with **Presidio + a local Llama 3.1 8B** scrubber in front of it.

---

## 6. Cross-Cutting Risks the Agent Inherits

1. **Legacy injection surface.** The agent must never call `/interface/` paths, only `/src/`-backed REST and FHIR.
2. **Configuration drift.** OpenEMR globals control too much behavior (audit on/off, password rules, MFA requirements). The build will publish a "minimum-config bundle" that operators apply before enabling the agent.
3. **No native event bus.** Chart-update notifications must be polled or hooked through Symfony's event dispatcher; design for eventual consistency.
4. **Multi-tenant variance.** Different OpenEMR installs disable different modules. The agent must degrade gracefully when, say, `procedure_result` is empty.
5. **Clinical-rule drift.** ICD-10 and SNOMED catalogs update annually. The verifier's clinical-rule store needs a version pinning strategy.

---

## 7. What This Audit Does Not Cover

- Network architecture of the deployment (operator-specific).
- Backup and disaster recovery (operator-specific).
- Specific CVE remediation status of the running build (defer to the operator's vulnerability scan).
- User-facing accessibility and internationalization (out of scope for the agent's first version).

These are deferred to operational readiness review.

---

## 8. References (verified)

- `_rest_routes.inc.php` (route bootstrap, FHIR R4 + US Core 3.1.0)
- `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php`
- `src/RestControllers/Subscriber/AuthorizationListener.php`
- `src/Common/Acl/AclMain.php`
- `src/Common/Logging/EventAuditLogger.php` (with `LogTablesSink`, `AtnaSink`)
- `src/Common/Auth/AuthUtils.php`
- `src/Common/Uuid/UuidRegistry.php`
- `sql/database.sql` (schemas for `lists`, `patient_data`, `form_encounter`, `audit_master`, …)

External:

- [OpenEMR API documentation](https://github.com/openemr/openemr/blob/master/API_README.md)
- [OpenEMR FHIR documentation](https://github.com/openemr/openemr/blob/master/FHIR_README.md)
- [HHS guidance on HIPAA Security Rule audit controls](https://www.hhs.gov/hipaa/for-professionals/security/laws-regulations/index.html)
- [SMART App Launch specification](https://hl7.org/fhir/smart-app-launch/)
- [Microsoft Presidio](https://github.com/microsoft/presidio) (PHI scrubbing)
