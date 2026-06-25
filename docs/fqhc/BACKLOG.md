# FQHC Issue Backlog

> **Status:** These have been **filed as GitHub issues** (see the mapping
> below) and linked into a parent/child hierarchy. This file remains the
> source text for them and the place to draft new issues before filing. See
> [`ROADMAP.md`](./ROADMAP.md) for sequencing and [`PRINCIPLES.md`](./PRINCIPLES.md)
> for the non-negotiables every issue inherits.

### Filed issues

| Backlog item | Issue |
|--------------|-------|
| EPIC-0 Program | [#2](https://github.com/Simonparkershames/openemr-fqhc/issues/2) |
| EPIC-1 Certification safety & extension architecture | [#3](https://github.com/Simonparkershames/openemr-fqhc/issues/3) |
| EPIC-2 UDS reporting | [#4](https://github.com/Simonparkershames/openemr-fqhc/issues/4) |
| EPIC-3 Design system & visual modernization | [#5](https://github.com/Simonparkershames/openemr-fqhc/issues/5) |
| EPIC-4 True role-based interfaces | [#6](https://github.com/Simonparkershames/openemr-fqhc/issues/6) |
| EPIC-5 Responsive & performance | [#7](https://github.com/Simonparkershames/openemr-fqhc/issues/7) |
| TASK-0.1 CI certification gate | [#8](https://github.com/Simonparkershames/openemr-fqhc/issues/8) |
| TASK-0.2 Upstream-sync process | [#9](https://github.com/Simonparkershames/openemr-fqhc/issues/9) |
| TASK-0.3 `OpenEMR\FQHC` module skeleton | [#10](https://github.com/Simonparkershames/openemr-fqhc/issues/10) |
| TASK-0.4 UDS data-element specs | [#11](https://github.com/Simonparkershames/openemr-fqhc/issues/11) |
| TASK-0.5 Design-system foundation decision | [#12](https://github.com/Simonparkershames/openemr-fqhc/issues/12) |

---

## EPIC-0 — [Program] OpenEMR for FQHCs — UDS compliance + modern UX, certification-safe

**Labels:** `epic`, `fqhc`

Adapt OpenEMR into a best-in-class EHR for FQHCs with three equally-weighted
objectives: **compliance** (HRSA UDS + the sliding-fee/special-population/
payer/clinical-quality data behind it), **experience** (design system, true
role-based interfaces, full responsiveness), and **certification safety** (no
risk to the ONC-certified core).

Planning docs: [`docs/fqhc/`](./README.md).

**Workstream epics (sub-issues):** EPIC-1 … EPIC-5 below.

**Phases:** Phase 0 foundations → Phase 1 UDS data capture & first report →
Phase 2 design system + role workspaces → Phase 3 depth & polish (see ROADMAP).

**Success:** certification-green FQHC distribution tracking upstream; complete
auditable UDS tables; cohesive design system; true role-based, responsive
workflows meeting performance and WCAG 2.1 AA budgets.

---

## EPIC-1 — Certification safety & extension architecture

**Labels:** `epic`, `fqhc`, `certification`
**Parent:** EPIC-0

Make it structurally safe to add FQHC capability without destabilizing the
ONC-certified core. Establishes the extension pattern (module system, events,
side tables, role menus) and the CI guardrails that prove certification is
unaffected. Details in [`ARCHITECTURE.md`](./ARCHITECTURE.md).

Child issues: TASK-0.1, TASK-0.2, TASK-0.3 (below).

---

## EPIC-2 — UDS reporting: data capture & report generation

**Labels:** `epic`, `fqhc`, `uds`
**Parent:** EPIC-0

Close the FQHC-specific data gaps (FPL %, sliding-fee tier, special populations,
UDS payer classification) in new side tables, and build a UDS reporting service
that emits auditable tables (3A/3B/4, ZIP, 6A, 6B/7 via the CQM/AMC engine, and
Table 5 utilization) with patient-level drill-down. Details in
[`UDS-REPORTING.md`](./UDS-REPORTING.md).

Child issues: TASK-0.4 (specs), then Phase 1 build issues (to be split out).

---

## EPIC-3 — Design system & visual modernization

**Labels:** `epic`, `fqhc`, `ux`
**Parent:** EPIC-0

Define and ship a design-system layer (tokens, components, accessibility
baseline) over the existing themes, adopted screen-by-screen starting with the
highest-traffic surfaces, never regressing the certified path. Details in
[`UX-MODERNIZATION.md`](./UX-MODERNIZATION.md).

Child issues: TASK-0.5 (design-system decision), then per-surface adoption issues.

---

## EPIC-4 — True role-based interfaces

**Labels:** `epic`, `fqhc`, `ux`
**Parent:** EPIC-0

Move beyond menu filtering to purpose-built workspaces per FQHC role (provider,
nurse/MA, front desk, eligibility/care management, behavioral health, billing,
admin/quality), built on the existing ACL + JSON-menu system and configurable
per site. Details in [`UX-MODERNIZATION.md`](./UX-MODERNIZATION.md).

---

## EPIC-5 — Responsive (phone/tablet/desktop) & performance

**Labels:** `epic`, `fqhc`, `ux`, `performance`
**Parent:** EPIC-0

Make adopted screens and the patient portal fully responsive with a single
codebase and touch-first ergonomics, each carrying an enforced performance
budget (time-to-interactive, payload, query count) validated on real clinic
hardware. Details in [`UX-MODERNIZATION.md`](./UX-MODERNIZATION.md).

---

# Phase 0 — actionable next-step issues

These are the immediate, do-now issues. Small and mostly low-risk; they unblock
everything else.

## TASK-0.1 — CI certification gate & "certification impact" PR checklist

**Labels:** `fqhc`, `certification`, `ci`
**Parent:** EPIC-1

- Make the Inferno/certification workflow and the full test suites
  (unit/api/e2e/services/isolated) **required to merge** on FQHC branches.
- Add a PR-template checklist item: "Certification impact — what certified code
  was touched, why it is safe, and the test evidence."

**Done when:** a red certification run blocks merge, and every PR states its
certification impact.

## TASK-0.2 — Upstream-sync process

**Labels:** `fqhc`, `certification`, `chore`
**Parent:** EPIC-1

- Document and automate periodic sync of `master` with upstream
  `openemr/openemr` (scheduled merge/rebase + full CI incl. certification).

**Done when:** divergence stays small and an upstream sync runs on a cadence
with certification proof it didn't regress.

## TASK-0.3 — `OpenEMR\FQHC` module skeleton

**Labels:** `fqhc`, `architecture`
**Parent:** EPIC-1

- Stand up an installable module shell proving the extension pattern end to end:
  new PSR-4 namespace `OpenEMR\FQHC`, an ACL section, an empty Doctrine
  migration, a registered route/menu entry, and a smoke test. Strict types,
  DI, PHPStan 10.

**Done when:** the module installs, registers, and is reachable without editing
any certified code path; CI green.

## TASK-0.4 — UDS data-element specs (current-year UDS Manual)

**Labels:** `fqhc`, `uds`, `docs`
**Parent:** EPIC-2

- Turn the [`UDS-REPORTING.md`](./UDS-REPORTING.md) gap list into concrete field
  specs and table/line mappings against the current-year UDS Manual: FPL %
  (household size/income + versioned FPL guidelines), sliding-fee tier,
  special-population statuses, UDS payer categories, service/visit
  classification.

**Done when:** each new field has a spec (type, validation, side table, UDS
table/line) ready to implement in Phase 1.

## TASK-0.5 — Design-system foundation decision

**Labels:** `fqhc`, `ux`
**Parent:** EPIC-3

- Choose the component approach (must coexist with the Angular 1.8 / jQuery
  shell, must not require touching certified pages, server-render-friendly),
  define initial tokens (color/type/spacing/elevation/focus), and document the
  decision and accessibility baseline (WCAG 2.1 AA).

**Done when:** the approach and tokens are documented and a single throwaway
screen demonstrates the layer rendering over an existing theme.
