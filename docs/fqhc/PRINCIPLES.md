# FQHC Project Principles

These are the non-negotiable rules for this project. Every design decision,
PR, and issue is checked against them.

## 1. Do not put ONC certification at risk

OpenEMR is **ONC Health IT certified**. That certification covers specific
capabilities (CCDA generation, FHIR US Core APIs, eCQM calculation, e-prescribing,
clinical decision support, audit logging, access control, and more). Breaking
any of these is the single worst outcome this project can produce.

Concretely:

- **Treat the certified surface as read-mostly.** Prefer to *add* alongside it
  rather than *modify* it. New tables, new services, new UI modules, new
  endpoints — not rewrites of certified code paths.
- **Never change the semantics of a certified data field.** UDS often needs
  data that looks similar to a certified field (e.g. race/ethnicity per the
  CDC/ONC value sets). Reuse the certified field as-is; do not re-map, re-code,
  or "improve" its allowed values.
- **Additive schema only on certified tables.** New columns are acceptable when
  unavoidable; renaming/removing/retyping existing columns is not. Prefer new
  side tables joined by patient/encounter id.
- **The Inferno / certification test suites must stay green.** The repo already
  runs an Inferno certification workflow. A red certification run blocks merge,
  full stop.
- **If a change might touch certified behavior, it gets explicit review** and a
  note in the PR describing the certification impact and the evidence it is
  unaffected.

See [`ARCHITECTURE.md`](./ARCHITECTURE.md) for the mechanics that make this
possible (extension points, side tables, module system, event subscribers).

## 2. Compliance is a feature, not an afterthought

UDS reporting and the data that feeds it (sliding fee, special populations,
payer source, clinical quality) are first-class product requirements. A field
that "exists somewhere in the chart" but cannot be reliably reported is not
done. Every UDS data element must be: captured in a structured, validated way;
mapped to its UDS table/line; and exercised by an automated report.

## 3. Experience is equal to compliance

The look, feel, and performance carry the same weight as functionality. We are
building something an FQHC would *choose*, not merely tolerate. That means a
real design system, genuinely role-specific interfaces, sub-second interactions
on common workflows, and full responsiveness from phone to desktop.

## 4. Follow the repo's modern engineering standards

All new code follows [`/CLAUDE.md`](../../CLAUDE.md): `declare(strict_types=1)`,
PSR-4 under `OpenEMR\`, constructor DI, `QueryUtils`/Doctrine instead of raw
SQL, PHPStan level 10, enums over magic strings, and Conventional Commits.
Legacy patterns in the surrounding code are **not** a license to write new code
the same way.

## 5. Build for multi-site reality

FQHCs run multiple service sites/locations under one grant. Facility/site scope
is part of the data model and the UI from day one, not bolted on later. UDS is
reported per grantee but operations are per site — both views must work.

## 6. Privacy and security are assumed, not optional

FQHC patients include some of the most vulnerable populations (homeless,
undocumented, behavioral health, substance use, minors). 42 CFR Part 2 and
sensitive-data handling must be respected. New data we capture (income,
housing status, veteran status) is PII/PHI and is access-controlled, audited,
and never leaked into logs or user-facing error messages.
