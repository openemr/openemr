# FHIR Bundle importer

Counterpart to `contrib/util/ccda_import/import_ccda.php` for the FHIR path.
Walks Synthea FHIR R4 Bundle `*.json` files and POSTs each entry to the
per-resource FHIR write endpoints added in #13281.

- CLI entry: `bin/console openemr:fhir-import`
  (`src/Common/Command/FhirImport.php`).
- Core: `src/Services/FHIR/Import/FhirBundleImporter.php`.
- Driven by `openemr-cmd irp --format=fhir` (openemr-devops repo), which
  reaches the command via
  `docker/flex/utilities/devtoolsLibrary.source::importRandomPatients`.

The importer registers a one-shot confidential oauth2 client via DCR, flips
`grant_types=password` and `is_enabled=1` in `oauth_clients` (DCR cannot grant
either), password-grants an admin bearer token, and POSTs resources ordered by
dependency (Organization → Practitioner → Patient → clinical) with a
`urn:uuid: → <Resource>/<uuid>` map carried across all bundles. The map and
ordering let per-patient bundles resolve references into the shared
`hospitalInformation*.json` / `practitionerInformation*.json` bundles that
Synthea produces alongside them.

The reference rewriter resolves three Synthea reference styles uniformly: the
within-bundle `urn:uuid:...` form, the FHIR search-URL form
(`Practitioner?identifier=<sys>|<val>`), and the Reference datatype's
logical-identifier form (`{identifier: {system, value}}` with no `reference`
string). The map is populated with all three keys per resource at POST time.

A **peelable transform layer** in `FhirBundleImporter` works around each
misfit between Synthea's output and OpenEMR's current write validators. Every
transform is tagged with its checklist item in this file (e.g. `[README A1]`);
when the upstream fix lands, a single method plus its registry entry in the
constructor can be deleted and the next irp run exercises the real path.

Log: `/tmp/fhir_import.log` (absolute path so the write works under
`run_php_as_apache` with no writable cwd). Counters printed at the end:
`bundles / POSTed / skipped (unsupported) / filtered-by-transform / failed`.

## FHIR irp readiness checklist

Items marked ✅ have a working peelable transform shim; the OpenEMR change can
land independently, and when it does we delete the transform. Items marked ⬜
are fully open — Synthea produces resources that fail silently (skipped) or
noisily (counted under `failed`).

### A. Server-side bugs (fix on the write path)

- ✅ **A1 — `extension[]` universally crashes `getUrl() on array`.**
  Root cause: `FHIRElement::__construct` (`src/FHIR/R4/FHIRElement.php:141-143`)
  stores raw JSON-decoded extension arrays into `$this->extension` without
  wrapping them as `FHIRExtension` objects. Any service that iterates
  `->getExtension()` and calls `->getUrl()` on an item crashes.
  Transform: `transformStripExtensions` strips `extension[]` from every
  resource. Known loss: race/ethnicity/birthsex/mother's-maiden-name/birthplace
  demographics and synthea telemetry extensions. Delete the transform once
  the base-class constructor wraps raw arrays into `FHIRExtension`.

- ✅ **A2 — Services only read HumanName with `use="official"`, leaving
  fname/lname empty when no name carries that attribute.**
  Observed on `FhirPractitionerService::parseFhirResource`. Synthea names omit
  `use` entirely. Transform: `transformTagFirstNameOfficial` sets
  `name[0].use = "official"` when unset. Delete when the server falls back to
  `name[0]` on no-official-match.

- ✅ **A3 — Non-ASCII email local parts rejected.**
  Synthea generates `Arturo47.Valentín837@example.com` style emails for
  internationalized given names; the write path's email validator refuses
  them. Transform: `transformStripNonAsciiTelecomEmails` drops any
  `telecom[]` entry with `system=email` whose value contains non-ASCII
  characters (keeps the rest of telecom). Delete when the validator accepts
  RFC 6531 internationalized local parts (or confirms it never will — in
  which case this transform stays).

- ⬜ **A4 — Encounter write path hides specific validation detail behind an
  opaque OperationOutcome ("Invalid FHIR resource: see server logs for
  details (incident <hex>)").** Not a blocker any longer (all Encounters
  land once Practitioner refs resolve), but every new misfit surfaces as a
  server-log scavenger hunt rather than a client-side diagnostic. Surface
  the specific validation detail (or a stable correlation-key lookup) in
  the response body.

### B. Write validators that could relax to accept standard R4 input

- ✅ **B1 — `MedicationRequest.medicationReference is not supported`.** The
  write path accepts only `medicationCodeableConcept`. Standard R4 allows
  either form. Transform: `transformInlineMedicationReference` resolves
  the referenced Medication from `bundleIndex` and inlines its `code` as a
  `medicationCodeableConcept`. Delete when the write path dereferences
  medicationReference server-side.

- ✅ **B2 — `Only CarePlan.intent "plan" is supported`.** R4 valueset is
  `proposal | plan | order | option | directive`; Synthea emits "order" for
  care plans tied to orders. Transform: `transformCareplanForcePlanIntent`
  rewrites non-plan intents to "plan". Semantic drift is tolerable for a
  dev tool. Delete when the write path accepts the full R4 valueset.

- ✅ **B3 — Observation.subject vital-signs profile cardinality enforcement
  firing on non-vital-signs profiled Observations, plus explicit
  code-level bans.** Simplified by filtering upstream: tier 1 drops the
  `laboratory` / `survey` / `social-history` categories entirely; tier 2
  drops specific LOINCs inside the accepted `vital-signs` category (BMI
  39156-5 "derived from height and weight", pain scale 72514-3 "written
  through form"). Transform:
  `transformObservationDropRejectedLoincs`. Trim tier 1 as the write
  path adds laboratory / survey / social-history Observation support.
  Trim tier 2 as the narrow bans relax or get re-classified.

- ✅ **B4 — `CareTeam.participant.member supports only Practitioner
  references on write`.** Synthea emits three participants per CareTeam
  (Patient, Practitioner, Organization). Transform:
  `transformCareteamKeepPractitionerParticipants` filters to
  Practitioner-only. If every participant is non-Practitioner the whole
  CareTeam drops (participant has 1..* cardinality). Delete when the
  write path accepts Patient/Organization members alongside Practitioner.

### C. FHIR writes not implemented yet

Explicit `fhirWriteNotImplemented` entries in the FHIR route table — Synthea
produces resources of these types and the importer skips them silently (they
land in the `skipped (unsupported)` counter, not `failed`):

- ⬜ **C1 — resource types without any write endpoint:**
  `POST /fhir/Procedure`, `POST /fhir/DocumentReference`,
  `POST /fhir/DiagnosticReport`, `POST /fhir/MedicationDispense`,
  `POST /fhir/Location`, `POST /fhir/Group`, `POST /fhir/Provenance`.

- ✅ **C2 — Laboratory `Observation.code` writes rejected as "written
  through their procedure order; Observation write for laboratory is not
  implemented yet".** Covered by the category filter in B3. When laboratory
  Observations become writable, remove `laboratory` from
  `OBSERVATION_ACCEPTED_CATEGORIES` → it's automatically re-included.

- ✅ **C3 — Form-sourced and survey Observations rejected as "written
  through their form".** Covered by the category filter in B3 (`survey` and
  `social-history` categories; the stray pain-scale 72514-3 mis-tagged as
  vital-signs is caught by the LOINC tier). Re-include categories as those
  write paths land.

### D. Likely permanent — Synthea → OpenEMR transform needed on the client side

These enforce OpenEMR semantics (and in Organization's case likely
ONC-certification boundaries) that will not and should not relax to accept
Synthea's shape. The transforms here are expected to live on indefinitely.

- ✅ **D1 — `Organization.facility_npi` required.** Synthea Organizations
  carry only `identifier[system=https://github.com/.../synthea]`.
  Transform: `transformMintOrganizationNpi` mints a deterministic
  10-digit NPI from a SHA-1 hash of the Organization's `id` so re-runs
  produce the same NPI (idempotent across bundle imports).

- ⬜ **D2 — Practitioner identifier flow.** Synthea's Practitioners already
  carry `identifier[system=http://hl7.org/fhir/sid/us-npi]`, so no mint is
  needed on the client side today, but the write path's practitioner
  identifier handling is still coupled to Synthea's exact
  `name[0].use="official"` fix (A2). If OpenEMR's practitioner write adds
  its own NPI requirements analogous to Organization's, a mint transform
  will join D1 here.

### E. Resolved by upstream fixes to A / B / D (no longer surfacing)

Historical cascade failures. Not independent problems — all driven by Patient
500 (A1) or Practitioner fname/lname empty (A2) or Organization missing NPI
(D1), and all stopped firing once those were shimmed. Recorded so future
runs don't re-triage them as new issues:

- Observation.subject / Condition.puuid / Device.patient /
  MedicationRequest.patient_id / Immunization.patient_id — all cascaded
  from Patient failing.
- Encounter.participant[0].individual.reference — cascaded from
  Practitioner failing.
- PractitionerRole.practitioner — cascaded from Practitioner failing +
  identifier-form reference resolution gap. The rewriter now handles both
  identifier forms (search URL + logical identifier object).

## Observed baseline after transforms

A clean 3-patient run produces, roughly:
- ~670 POSTed
- ~1400 skipped (unsupported resource types)
- ~550 filtered-by-transform
- 0 failed
- ~175 seconds end-to-end

Numbers vary bundle-to-bundle because Synthea produces different medical
histories per patient (an older patient with many encounters generates many
more Observations than a healthy young one).
