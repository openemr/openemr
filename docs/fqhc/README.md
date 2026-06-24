# OpenEMR for FQHCs

This directory documents the goals, principles, and plan for adapting OpenEMR
into a **best-in-class EHR for Federally Qualified Health Centers (FQHCs)** and
look-alikes.

The work has three intertwined objectives that are treated as equally
important:

1. **Compliance** — capture every data element required for HRSA **UDS**
   (Uniform Data System) reporting, and the underlying clinical quality
   measures, sliding-fee, and special-population data that feed it.
2. **Experience** — deliver the look, feel, and performance of a modern,
   web-based SaaS product: a coherent design system, true role-based
   interfaces, and full tablet/mobile/desktop responsiveness.
3. **Certification safety** — do all of the above **without putting the
   ONC-certified core at risk**. The certified surface is a constraint we
   design around, not something we refactor through.

> If you read only one thing, read [`PRINCIPLES.md`](./PRINCIPLES.md) (the
> non-negotiables) and [`ROADMAP.md`](./ROADMAP.md) (what we do next and what
> we deliver long-term).

## Why FQHCs need this

FQHCs operate under requirements that general-practice EHRs do not model well:

- **UDS reporting** to HRSA every calendar year (tables on patients, staffing,
  utilization, clinical quality, costs, and revenue).
- **Sliding Fee Discount Program (SFDP)** driven by household income as a
  percentage of the Federal Poverty Level (FPL).
- **Special populations** tracking (homeless, migrant/seasonal agricultural
  worker, public housing resident, veteran, school-based).
- **Payer mix** that skews heavily Medicaid, uninsured, and grant-funded.
- A workforce spanning providers, nurses, care managers, enrollment/eligibility
  staff, front desk, and behavioral health — each needing a focused interface.

Stock OpenEMR is ONC-certified and already contains much of the clinical and
reporting machinery (the CQM/AMC/CDR engine, demographics, ACL-based roles,
patient portal). It does **not** capture FQHC-specific data elements out of the
box, its UI predates modern responsive design, and its roles are coarse. This
project closes those gaps.

## Document index

| Document | Purpose |
|----------|---------|
| [`PRINCIPLES.md`](./PRINCIPLES.md) | The non-negotiable rules — especially certification safety. Read first. |
| [`ARCHITECTURE.md`](./ARCHITECTURE.md) | How we extend OpenEMR without forking or destabilizing the certified core. |
| [`UDS-REPORTING.md`](./UDS-REPORTING.md) | UDS data elements, where OpenEMR already captures them, and the gaps. |
| [`UX-MODERNIZATION.md`](./UX-MODERNIZATION.md) | Design system, role-based interfaces, and responsive strategy. |
| [`ROADMAP.md`](./ROADMAP.md) | Phased plan: immediate next steps and longer-term deliverables. |
| [`BACKLOG.md`](./BACKLOG.md) | Import-ready issue set (program epic, workstream epics, Phase 0 next steps). |

## How this maps to GitHub issues

The roadmap is mirrored as one **program epic** linking a small number of
**workstream epics** (certification safety, UDS, design system, role-based UI,
responsive/performance), each with concrete near-term issues.

> **Note:** GitHub Issues is currently **disabled** on this repository, so the
> issue set lives in [`BACKLOG.md`](./BACKLOG.md) as import-ready content.
> Enable Issues (Settings → Features → Issues) and create them top-down from
> that file. Start from the program epic to navigate.

## Status

Planning. No FQHC-specific code has been written yet — this documentation set
and the linked issues define the work before any core code is touched.
