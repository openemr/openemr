# Architecture: Extending OpenEMR Without Breaking It

This document describes *how* we add FQHC capability while honoring
[Principle #1](./PRINCIPLES.md) — do not put ONC certification at risk.

## The core idea: extend, don't fork

We treat upstream OpenEMR (`openemr/openemr`) as a dependency we track, not a
codebase we diverge from. The more our FQHC work lives in **additive, isolated
extension points**, the easier it is to:

- keep certified code paths byte-for-byte identical where possible,
- pull upstream security and certification updates with minimal conflicts, and
- reason about what is "ours" vs "theirs" during review.

### Where new code goes

| Concern | Location | Notes |
|---------|----------|-------|
| Domain services, value objects, DTOs | `src/` under `OpenEMR\FQHC\...` | New PSR-4 namespace. Strict types, DI, PHPStan 10. |
| FQHC-specific schema | new tables in `sql/` + Doctrine migrations | Side tables keyed by `pid`/`encounter`; additive columns only when unavoidable. |
| UI modules / widgets | OpenEMR module system under `interface/modules/` or `modules/` | Self-contained; registered via the module installer. |
| Cross-cutting hooks | Symfony `EventDispatcher` subscribers | React to clinical/billing events without editing core flow. |
| Reporting | new report services consuming the existing CQM/AMC engine | Read certified clinical data; never mutate it. |
| Design system assets | new SCSS partials + a theme layer | Layered on top of existing `interface/themes`, not a rewrite of them. |

### Extension mechanisms already in the codebase

OpenEMR gives us seams we should use instead of editing core:

- **Module system** — installable modules with their own routes, ACLs, and
  schema (`interface/modules/`, `src/Core/ModulesApplication`, `Menu` events).
- **Event system** — `OpenEMR\Events\*` dispatched through Symfony
  EventDispatcher; subscribe to patient/encounter/menu/render events.
- **Service layer** — `BaseService` + `QueryUtils`; new services extend these.
- **Role-based menus** — JSON menu definitions in
  `interface/main/tabs/menu/menus/` (e.g. `standard.json`, `front_office.json`)
  select what each role sees. New role menus are data, not core edits.
- **ACL (`AclMain` / gacl)** — fine-grained access controls we extend with new
  sections rather than replace.
- **Globals/registry** — feature flags via `OEGlobalsBag` so FQHC features can
  be toggled per install.

## Schema strategy

UDS and FQHC operations need data the certified schema does not model. The
rule:

1. **First choice — reuse an existing certified field unchanged.** Race,
   ethnicity, preferred language, and many demographics already exist and are
   coded to the value sets certification expects. Use them as the source of
   truth; do not duplicate or re-code them.
2. **Second choice — a new side table** keyed by patient/encounter id (e.g.
   `fqhc_patient_socioeconomic` for income/FPL/sliding-fee tier,
   `fqhc_special_population` for homeless/MSAW/public-housing/veteran/school).
   This keeps certified tables untouched.
3. **Last resort — an additive column** on an existing table, only when a side
   table would be unreasonable, and never a rename/retype/removal.

All schema changes ship as Doctrine migrations and are documented against the
UDS line item they support (see [`UDS-REPORTING.md`](./UDS-REPORTING.md)).

## Certification guardrails in CI

- The **Inferno certification** workflow and the existing test suites
  (unit, api, e2e, services, isolated) must stay green; certification failures
  block merge.
- New code must pass PHPStan level 10, phpcs, and rector on changed files.
- PRs that touch anything near a certified code path carry a short
  "certification impact" note: what was touched, why it is safe, and what test
  evidence supports that.

## Upstream tracking

We keep `master` aligned with upstream OpenEMR and land FQHC work on top. A
periodic upstream sync (rebase/merge + full CI including certification) keeps
divergence small and surfaces conflicts early, while the certification suite
proves the merge did not regress certified behavior.
