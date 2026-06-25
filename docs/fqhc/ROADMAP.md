# FQHC Roadmap

This roadmap sequences the work. It is intentionally not exhaustive — it makes
clear **what we do next** and **what the longer-term deliverables are**, and it
is mirrored in GitHub issues (one program epic linking workstream epics).

## Phasing principle

We sequence so that **certification safety and foundations come first**, then
data capture, then experience — but the three workstreams overlap. Every phase
keeps the certification suite green.

---

## Phase 0 — Foundations & guardrails (next steps, do now)

The immediate work. Small, mostly non-code or low-risk, unblocks everything
else.

- **Document the program** (this docs set) and open the issue structure. ✅ in
  progress.
- **Establish certification guardrails in CI** — make Inferno/certification and
  the full test suites required to merge; add a "certification impact" PR
  checklist item.
- **Set up the upstream-sync process** — keep `master` tracking upstream
  OpenEMR with periodic merge + full CI, so divergence stays small.
- **Stand up the `OpenEMR\FQHC` namespace and module skeleton** — an installable
  module shell (routes, ACL section, empty schema migration) proving the
  extension pattern from [`ARCHITECTURE.md`](./ARCHITECTURE.md) end to end.
- **Author UDS data-element specs to the current-year UDS Manual** — turn
  [`UDS-REPORTING.md`](./UDS-REPORTING.md)'s gap list into concrete field specs
  and table mappings.
- **Design-system discovery & decision** — choose the component approach
  (Principle: must coexist with the Angular/jQuery shell and not touch certified
  pages), define initial tokens, and document the decision.

## Phase 1 — UDS data capture & first report

Close the structured-data gaps so a real UDS number can be produced.

- New side tables + migrations: socioeconomic (household size/income → FPL %),
  sliding-fee tier, special-population status, UDS payer classification.
- Intake/demographics UI for the new fields (validated at the boundary,
  computed FPL/SFDP — not free-typed).
- A **UDS reporting service** that produces Table 3A/3B/4 and ZIP outputs from
  demographics + new data, with patient-level drill-down for data cleanup.
- Map the existing CQM/AMC measures to UDS Table 6B/7 and emit those tables.
- Utilization/Table 5 counts from encounters/scheduling.

**Deliverable:** an FQHC can generate auditable UDS patient, clinical-quality,
and utilization tables from data captured in the product.

## Phase 2 — Experience: design system + role workspaces

Make it feel like a modern product, starting with the highest-traffic surfaces.

- Ship the design-system foundation (tokens, core components, accessibility
  baseline) as a layer over the existing themes.
- Build **role-aware landing pages/workspaces** for provider, nurse/MA, front
  desk, and eligibility/care-management roles on top of the existing ACL + menu
  system.
- Modernize and make responsive the top point-of-care screens (intake, rooming,
  patient summary, check-in) to phone/tablet/desktop, each with a performance
  budget.

**Deliverable:** the daily-driver workflows are modern, role-specific, and
fully responsive, with the certified core untouched.

## Phase 3 — Depth & polish

Broaden coverage and harden.

- Remaining role workspaces (behavioral health, billing, admin/quality).
- **UDS dashboards** with year-round data-quality worklists (don't let problems
  pile up until reporting season).
- Patient-portal modernization for mobile-only patients.
- Enabling-services and sensitive-data (42 CFR Part 2) workflows.
- Performance pass across adopted surfaces; i18n coverage for new UI.

---

## Longer-term deliverables (the destination)

- A **certified-safe FQHC distribution** of OpenEMR that tracks upstream and
  passes the certification suite continuously.
- **Complete, auditable UDS reporting** for the patient, clinical-quality,
  utilization, and patient-service-revenue tables, built to each reporting
  year's manual, with drill-down and data-quality tooling.
- A **cohesive design system** and a set of **true role-based workspaces** that
  make OpenEMR feel like a modern SaaS EHR.
- **Full responsiveness** (phone/tablet/desktop) across the daily-driver
  clinical and front-office workflows and the patient portal.
- **Performance budgets** met on common workflows on real clinic hardware.
- WCAG 2.1 AA accessibility and strong multilingual support across new UI.

## Where to start — picking the next ticket (solo-dev guide)

This project is built mostly by **one developer pairing with an AI assistant**,
so the sequencing favors small, shippable, independently-verifiable slices over
big parallel workstreams. Use this quick decision guide each time you sit down:

1. **Is the foundation in place?** If [#10 `OpenEMR\FQHC` module
   skeleton](https://github.com/Simonparkershames/openemr-fqhc/issues/10) isn't
   done, **do it first.** Everything else installs into that module, and it
   proves the certification-safe extension pattern end to end. This is the
   single best *first build*.
2. **Want guardrails before you write features?** [#8 CI certification
   gate](https://github.com/Simonparkershames/openemr-fqhc/issues/8) is cheap,
   mostly-config, and means you can refactor fearlessly afterward. Good to pair
   with #10.
3. **Ready for the first real feature?** Start the UDS data capture as the
   **smallest vertical slice first**: the FPL foundation (guideline data +
   income side table + computation service, no UI), per the slice list in
   [`UDS-DATA-MODEL.md`](./UDS-DATA-MODEL.md) §7. It's pure domain logic, fully
   unit-testable without the UI, and unblocks the income/SFDP intake screen
   next.
4. **Blocked or it's a "thinking" day?** Do the spec work —
   [#11 UDS data-element specs](https://github.com/Simonparkershames/openemr-fqhc/issues/11)
   or [#12 design-system decision](https://github.com/Simonparkershames/openemr-fqhc/issues/12).
   Both are low-code and unblock larger builds.

**Recommended path for the first few sessions:** #10 → #8 → FPL foundation
slice (#4/#11) → income/FPL intake UI → special-population statuses. Each lands
green and demoable on its own.

**Heuristics for "is this the right next ticket?"**
- Prefer tickets that are **unblocked**, **small enough to finish in a session**,
  and **independently verifiable** (a test or a screen you can click).
- Prefer a thin **vertical slice** (data → service → one screen) over a broad
  horizontal layer — you learn more and ship something usable.
- When two are equal, do the one that **unblocks the most** downstream work.
- Keep the certification suite green at every step; never start a slice you
  can't finish behind a feature flag.

The issues carry `Phase 0` framing and parent/child links so the next step is
visible without a project board. When in doubt, ask the assistant to "pick the
next ticket" — it can apply the rules above against the open issues.

## How we track progress

- The **program epic** issue links the workstream epics and shows phase status.
- Each workstream epic lists its near-term issues; "Phase 0" issues are the
  actionable next steps.
- This file is the human-readable source of truth for sequencing; the issues
  are the unit of execution.
