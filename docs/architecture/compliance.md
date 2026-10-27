# Jurisdiction & compliance

Part of the [OpenEMR architecture docs](README.md). This document identifies the parts of
the architecture that are **governed by regulatory compliance** — primarily the **US ONC
Health IT Certification Program** — so that contributors and AI agents know which components
cannot be changed casually, and what re-validation a change implies.

---

## Why this document exists

Most of the codebase can be refactored freely as long as tests pass. A subset cannot: its
behavior, output format, and vocabularies are fixed by a certification criterion or a
regulatory standard. Changing the shape of a C-CDA document, a FHIR resource, a QRDA report,
a code-system mapping, or the API app-approval flow can **break certification** even when
every unit test is green, because conformance is judged against external standards and test
procedures, not against our own tests alone.

Treat the components mapped below as **regulated surfaces**: understand the governing
criterion before you touch them, and re-validate against the standard (not just CI) after.

### Scope and caveats

- **Jurisdiction:** this document is **US-centric** (ONC / 45 CFR 170.315). OpenEMR is
  deployed worldwide; other jurisdictions have their own regimes (see
  [Non-US jurisdictions](#non-us-jurisdictions)). US ONC specifics do **not** transfer to
  those, and vice versa.
- **Point-in-time mapping.** The criterion↔component map below is a guide, **not** the
  authoritative spec. ONC criteria evolve (e.g. the 2015 Edition Cures Update, HTI
  rulemakings); some criteria have been retired. Always verify against the **current** ONC
  criteria and test procedures.
- **Certification status is tracked elsewhere,** not here — in the product's certification
  records and the Real World Testing plan (`src/Reports/RealWorldTesting.php` exercises
  certified capabilities). This document describes *where the governed code is*, not *what is
  currently certified*.
- This is an architectural overview; operational/API detail lives in `API_README.md`,
  `FHIR_README.md`, `Documentation/api/`, and the module/CQM READMEs cited below.

---

## US ONC certified criteria → architecture components

**Certified product (authoritative source).** OpenEMR 8.1.0 is certified under the ONC Health
IT Certification Program — **CHPL listing 11757**, certification number
**15.05.05.3115.OPEN.02.01.1.260130** (certified 2026-01-30). The table below lists *that
product's* certified §170.315 criteria mapped to their code. **CHPL is authoritative and
updated as certification changes — confirm the current set at <https://chpl.healthit.gov>
(listing 11757) before relying on this.** Two capabilities use **relied-upon software**: the
**EMR Direct Interoperability Engine 2017** (for **(b)(1)** and **(h)(1)**) and **AccessGUDID
v2** (for **(a)(14)**).

**Certified CQMs:** CMS22, CMS69, CMS122, CMS124, CMS125, CMS127, CMS130, CMS138, CMS147,
CMS165.

"Governed" means a change can affect conformance and requires re-validation against the
standard, not just CI.

| Certified criterion | What it covers | Architecture component |
|---|---|---|
| **(a)(1) / (a)(2)** — CPOE: medications / labs | Computerized provider order entry | order entry / e-prescribing; `interface/forms/procedure_order/` |
| **(a)(5)** — Demographics | USCDI demographic data | `patient_data`, `src/Services/PatientService.php` |
| **(a)(12)** — Family health history | Structured family history | the family-history form |
| **(a)(14)** — Implantable device list | Record device UDIs, parse via GUDID | `src/Services/DeviceService.php` + **AccessGUDID v2** (relied-upon) |
| **(b)(1)** — Transitions of care | Create / send / receive / view a conformant **C-CDA** | `src/Services/Cda/`, `ccdaservice/`, the `Carecoordination` module; Direct (**(h)(1)**) + **EMR Direct** (relied-upon) |
| **(b)(10)** — EHI export | Export all electronic health information | the `oe-module-ehi-exporter` module; schema map `Documentation/EHI_Export/` (+ `openemr.meta.xml`, [layers.md](layers.md#database-schema-design-and-governance)) |
| **(b)(11)** — Decision support interventions (DSI) | Evidence-based + predictive DSIs (HTI-1; replaced the former CDS (a)(9)) | `library/clinical_rules.php`, `src/ClinicalDecisionRules/` |
| **(c)(1)–(c)(3)** — CQM record / import / report | Capture, import/calculate, and report CQMs as **QRDA I / III** | `src/Cqm/`, `src/Cqm/QrdaControllers/QrdaReportController.php` |
| **(g)(2)** — Automated measure calculation | CQM calculation engine — note **(g)(1)** numerator recording is **not** in this cert | `src/Cqm/` (CQL/QDM engine, OIDs in `oids_qdm_*.json`, `PhpCqmCalculation.php`) |
| **(g)(6)** — C-CDA creation performance | Generated C-CDA validates against the standard | `ccdaservice/` + Schematron `src/Services/Cda/Schematron/` |
| **(g)(7)** — App access: patient selection | API returns a patient context to an app | the REST/FHIR API (`src/RestControllers/`, `src/FHIR/`) |
| **(g)(9)** — App access: all data request | API returns all USCDI data for a patient | the REST/FHIR API, `src/FHIR/` |
| **(g)(10)** — Standardized API (FHIR) | **FHIR R4 + US Core**, **SMART App Launch**, **Bulk Data**, client registration + **manual app approval** | `src/FHIR/`, `src/FHIR/SMART/` + `src/RestControllers/SMART/` (incl. `ClientAdminController` approve/enable/revoke; `interface/smart/register-app.php`), `src/FHIR/Export/` (`$export`), OAuth2 (`src/RestControllers/AuthorizationController.php`) |
| **(h)(1)** — Direct Project transport | Direct secure, trusted exchange of clinical documents | EMR Direct **phiMail**: `interface/modules/zend_modules/module/Application/src/Application/Plugin/Phimail.php`, `library/direct_message_check.inc.php`, trust certs `public/certs/phimail/` + **EMR Direct** (relied-upon) |
| **(d)(1)–(d)(9), (d)(12), (d)(13)** — Privacy & security | Auth, access control, audit, encryption, trusted connections | ACL ([subsystems.md](subsystems.md#permissions-and-authorization)), OAuth2 ([subsystems.md](subsystems.md#oauth2-scopes-and-api-authorization)), encryption ([subsystems.md](subsystems.md#security-encryption-keys-and-api-authorization)), audit logging |
| **(e)(3)** — Secure messaging | Patient–provider secure messaging | the patient portal ([portal.md](portal.md)). *(Note: **(e)(1)** View/Download/Transmit is **not** part of this certification.)* |
| **(g)(3) / (g)(4) / (g)(5)** — SED / QMS / accessibility | Safety-enhanced design, quality management, accessibility-centered design | process & documentation criteria, not a single code surface |

> **Not certified ≠ not present.** OpenEMR ships features beyond this list (e.g.
> View/Download/Transmit, additional CQMs); they are simply not part of *this* certification.
> Conversely, do not assume an uncertified area is unregulated in a given deployment.

### C-CDA, FHIR, QRDA, and Direct are the four regulated output surfaces

Four output pipelines carry the heaviest compliance weight, because external validators and
test procedures judge their exact output:

- **C-CDA** (b1, g6) — generation in `ccdaservice/`, validation via Schematron in
  `src/Services/Cda/Schematron/`. Changing a template, section, or code binding can fail
  validation. Note `ccdaservice/` is also the **Node runtime slated to move to PHP** — any
  such move must preserve C-CDA conformance exactly
  ([tech-stack.md](tech-stack.md#runtime-services)).
- **FHIR** (g10) — US Core resource shapes, SMART launch, and Bulk Data in `src/FHIR/`. The
  resource structure, search parameters, and `CapabilityStatement` are conformance-bound.
- **QRDA** ((g)(2), (c)(1)–(c)(3)) — `src/Cqm/` produces QRDA I/III from CQL/QDM measures; the
  value-set OIDs and measure logic are regulated.
- **Direct** (h1, b1) — phiMail trust configuration and certificate handling; the *trust*
  (who you will exchange with) is the compliance-sensitive part, not just the transport.

---

## Code sets and code systems

Clinical data must be coded in the **standard terminologies** the regulations (USCDI / ONC)
require — among them **ICD-10-CM/PCS**, **SNOMED CT**, **LOINC**, **RxNorm**, **CPT/HCPCS**,
**CVX** (immunizations), **NDC**, and the **CDC Race & Ethnicity** code set. These feed every
regulated output above (C-CDA, FHIR, QRDA), so their identifiers and mappings are
compliance-sensitive.

- **Where they live:** the `code_types` table, `library/code_types.inc.php` /
  `custom/code_types.inc.php`, and `src/Services/CodeTypesService.php` (plus the importers
  under `src/Services/CodeTypes/`). CQM value sets are expressed as OIDs
  (`src/Cqm/oids_qdm_*.json`).
- **How coded values are encoded in the data.** Throughout the schema a coded value is stored
  as a **`codesystem:code`** pair — a terminology prefix (a key from `code_types`, e.g.
  `ICD10`, `SNOMED-CT`, `LOINC`) joined by a colon to the code itself. Where a field may carry
  several codes — a service's related diagnoses, a CDS rule binding, etc. — the pairs are
  concatenated with a **semicolon**, so a single column holds, e.g.:

  ```
  ICD10:E11.9;SNOMED-CT:44054006
  ```

  Readers split on `;` then on `:` (e.g. `explode(';', $related_code)` in
  `library/FeeSheet.class.php`). A few list/history contexts use a **double** colon
  (`codetype::code`) instead — check the field before parsing. The prefix **must** be a
  recognized `code_types` system id: the regulated outputs (C-CDA / FHIR / QRDA) translate that
  prefix to the standard code system's OID (C-CDA/QRDA) or URI (FHIR), so an unknown or wrong
  prefix silently produces non-conformant output.
- **Compliance sensitivity:** do **not** alter a code-system identifier, OID, or mapping
  casually — a wrong binding silently produces non-conformant C-CDA/FHIR/QRDA and bad
  interoperability, which CI will not catch. Terminology updates (new RxNorm/SNOMED/LOINC
  releases) go through the code-type import tooling, not hand edits.

---

## Working in a compliance-governed area

1. **Identify the governing criterion** (the table above) before changing anything in those
   components.
2. **Preserve the output contract.** Do not change C-CDA/FHIR/QRDA structure, code-system
   bindings, Direct trust configuration, or the API scope/approval behavior unless the change
   *is* the intent and is checked against the standard.
3. **Re-validate against the standard, not just CI:** Schematron for C-CDA, FHIR validators /
   `CapabilityStatement` for FHIR, QRDA schema for CQM output, and the relevant ONC test
   procedure where applicable. Green unit tests are necessary, not sufficient.
4. **Mind Real World Testing.** Certified capabilities are exercised and reported on annually
   (`src/Reports/RealWorldTesting.php`); regressions here have reporting and certification
   consequences, not just bugs.
5. **These are core robustness responsibilities.** Even when an integration or module is
   involved (phiMail, a FHIR client), a correctness/escaping/error-path fix in core must hold
   regardless of whether that integration is configured — see the Module/Core boundary rules
   (`AGENTS.md` → Module/Core Boundaries).

---

## Non-US jurisdictions

OpenEMR runs under many regulatory regimes. Jurisdiction-specific interoperability — national
patient identifiers, country code systems, local document/exchange standards — belongs in
**modules**, not hardcoded into core, and carries its **own** compliance regime distinct from
US ONC (examples in-tree include the Irish/UK OPIT and IHI integrations). Two rules follow:

- **Keep jurisdiction-specific compliance logic in the owning module** and wire it to core via
  events, not by branching core on a country (the core↔module rule, [README.md](README.md#2-target-architecture-at-a-glance)).
- **Do not assume US semantics are universal.** A code system, identifier format, or exchange
  standard that is mandatory in one jurisdiction may be wrong in another; core should stay
  jurisdiction-neutral and let modules supply the specifics.

---

## Authoritative references

- **ONC Health IT Certification Program** and the **§170.315** criteria + test procedures
  (external — the controlling spec).
- `API_README.md`, `FHIR_README.md`, and `Documentation/api/` — API, OAuth2, scopes.
- `src/Cqm/README` — CQM / QRDA.
- The `Carecoordination` module — C-CDA import/export and Direct send/receive.
- `src/Reports/RealWorldTesting.php` — the Real World Testing surface.
