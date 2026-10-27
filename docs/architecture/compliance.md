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

## US ONC criteria → architecture components

Criterion letters/numbers are ONC §170.315 identifiers. "Governed" means a change can affect
conformance and requires re-validation against the standard.

| Criterion | What it requires | Architecture components |
|---|---|---|
| **b1** — Transitions of Care | Create, send, receive, and view a conformant **C-CDA** for care transitions | `src/Services/Cda/` (parse/import/validate), `ccdaservice/` (generation), the `Carecoordination` zend module, Direct transport (**h1**) |
| **g6** — C-CDA creation performance | Generated C-CDA validates against the standard | `ccdaservice/` generation, Schematron validation `src/Services/Cda/Schematron/` |
| **g7 / g9** — Application access (patient selection / all data request) | Programmatic access to a patient's data | the REST/FHIR API (`_rest_routes.inc.php`, `src/RestControllers/`, `src/FHIR/`) |
| **g8** — Application access (data category request) | *(Retired in the Cures Update — folded into the standardized API.)* Verify current status. | — |
| **g10** — Standardized API for patient and population services | **FHIR R4 + US Core**, **SMART App Launch**, and **Bulk Data** export | `src/FHIR/` (US Core resources), `src/FHIR/SMART/` + `src/RestControllers/SMART/` (SMART launch), `src/FHIR/Export/` (`$export` bulk), OAuth2 (`src/RestControllers/AuthorizationController.php`) |
| **g1 / g2** — Automated numerator recording / measure calculation | **CQM** calculation and reporting via **QRDA I** (patient) and **QRDA III** (aggregate) | `src/Cqm/` (CQL/QDM engine, value-set OIDs in `oids_qdm_*.json`), `src/Cqm/QrdaControllers/QrdaReportController.php` (also the **c1–c4** CQM criteria) |
| **h1** (with **b1**) — Direct Project transport | **Direct** secure, trusted email exchange (trust bundles / HISP) for sending/receiving clinical documents | EMR Direct's **phiMail** integration: `interface/modules/zend_modules/module/Application/src/Application/Plugin/Phimail.php`, `library/direct_message_check.inc.php`, trust certs under `public/certs/phimail/` |
| **g10** — API registration & app approval | Dynamic client registration **plus manual admin approval/enable** of API apps, and token/trust revocation | `src/FHIR/SMART/ClientAdminController.php` (admin approve/enable/revoke), `interface/smart/register-app.php` + `admin-client.php`, `src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php` |
| **d1–d13** — Privacy & security | Authentication, authorization, audit, encryption, access control | ACL ([subsystems.md](subsystems.md#permissions-and-authorization)), OAuth2 ([subsystems.md](subsystems.md#oauth2-scopes-and-api-authorization)), encryption ([subsystems.md](subsystems.md#security-encryption-keys-and-api-authorization)), audit logging |
| **e1 / e3** — View/Download/Transmit; secure messaging | Patient access to their data and messaging | the patient portal ([portal.md](portal.md)) |

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
- **QRDA** (g1/g2, c1–c4) — `src/Cqm/` produces QRDA I/III from CQL/QDM measures; the
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
