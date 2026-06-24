# UX Modernization: Design System, Roles, and Responsiveness

[Principle #3](./PRINCIPLES.md) makes experience equal to compliance. This
document describes how we get to a modern, role-based, fully responsive product
**without** destabilizing the certified UI (Principle #1).

## Current state

OpenEMR's UI is functional and deep but dated:

- A tab/frame shell (`interface/main/tabs/`) wrapping many independently styled
  legacy pages.
- Styling via SCSS themes in `interface/themes/` on **Bootstrap 4.6**, jQuery
  3.7, and Angular 1.8.
- Role differentiation today is mostly **menu filtering** (JSON menus like
  `standard.json`, `front_office.json`, `chart_review.json`) plus ACL checks —
  the underlying screens are largely the same for everyone.
- Many screens are not designed for small viewports; touch ergonomics are
  inconsistent.

This is a solid, certified foundation. We modernize **on top of it**, screen by
screen, rather than rewriting the shell.

## Strategy: a design-system layer, adopted incrementally

1. **Define a design system** — tokens (color, type scale, spacing, radius,
   elevation, focus states), a component library, and accessibility baselines
   (WCAG 2.1 AA; healthcare users include assistive-tech users and certification
   touches accessibility). Tokens are authored as SCSS variables/CSS custom
   properties layered over the existing theme variables so we restyle without
   forking `interface/themes`.
2. **Pick a component approach and commit to it.** Options range from
   Bootstrap-5-aligned components to a modern framework (e.g. Web Components or
   a contained React/Vue island per module). The decision criteria: must coexist
   with the existing Angular 1.8 / jQuery shell, must not require touching
   certified pages to render, and must be server-rendering-friendly for
   performance. This choice is an explicit, documented decision (see the
   "Design system foundation" issue) before broad rollout.
3. **Adopt per surface, highest-traffic first.** Reskin and re-flow the
   screens clinicians and front-desk staff touch dozens of times a day before
   the long tail of admin screens. Each adopted screen gets responsive layout,
   design-system components, and a performance budget at the same time.
4. **Never regress the certified path.** A modernized screen must produce the
   same data and satisfy the same ACLs as the screen it replaces, and the
   certification suite must stay green.

## True role-based interfaces

Today's roles filter menus; we want roles that change the *whole* experience —
the landing view, the default actions, the density, and the data surfaced.

Target FQHC roles (each gets a purpose-built home/workspace, not just a menu):

- **Provider / clinician** — schedule, patient summary, open encounters,
  results, orders, care gaps (including UDS-relevant gaps).
- **Nurse / MA** — rooming, vitals, intake, screenings, immunizations.
- **Front desk / registration** — check-in, demographics, insurance, sliding-fee
  eligibility intake.
- **Eligibility / enrollment / care management** — FPL & SFDP determination,
  enabling services, special-population tracking, follow-up worklists.
- **Behavioral health** — sensitive-data-aware workflows (42 CFR Part 2),
  screening tools, warm-handoff tracking.
- **Billing** — claims, payer mix, UDS revenue inputs.
- **Administrator / quality** — UDS dashboards, data-quality worklists,
  staffing/utilization, configuration.

Implementation builds on the existing ACL + JSON-menu system (so we inherit
certified access control) and adds **role-aware landing pages and workspaces**
as new modules. Roles are configurable per site because FQHCs combine duties
differently.

## Responsive: phone, tablet, desktop

- **Mobile/tablet-first layout** for newly adopted screens, with a single
  responsive codebase rather than a separate mobile app.
- **Touch ergonomics** — appropriately sized targets, no hover-only actions,
  fast and forgiving forms (intake and rooming are the priority because they
  happen on tablets at the point of care).
- **Performance is part of responsive.** A responsive screen that is slow on a
  clinic tablet over shared Wi-Fi is not done. Each adopted screen carries a
  performance budget (time-to-interactive, payload size, query count) enforced
  in review.
- **Patient portal** gets the same treatment — many FQHC patients are
  mobile-only, so the portal's responsiveness and clarity directly affect
  access to care.

## Accessibility & internationalization

- WCAG 2.1 AA as a baseline for every new/adopted screen.
- FQHC populations are linguistically diverse; preserve and extend OpenEMR's
  i18n so the modern UI is translatable, and surface preferred-language data
  (already captured for UDS) in the workflow.

## Definition of done for a modernized screen

A screen is "modernized" only when it is: design-system styled, responsive
across breakpoints, role-appropriate, WCAG 2.1 AA, within its performance
budget, functionally equivalent to the certified original, and covered by the
relevant tests (including render/fixture tests for Twig where applicable).
