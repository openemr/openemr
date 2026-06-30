# Starter Pathway: Essential UDS Fields in a Modern UI (UX-first)

This is the **chosen first path** for the project. The goal is concrete and
visible: **open a patient and see all the essential UDS-required fields in a
good-looking, responsive screen** — then make those fields editable. We build
the experience first and grow the data/reporting behind it.

Why UX-first here: it gives a demoable result every session, it forces the
design system to exist early (so everything after inherits the look), and it
surfaces the real UDS data model through a screen you can actually use rather
than through abstract tables.

> Tracked as the pathway epic **#13**. Work its steps top-to-bottom. Each step
> lands green, looks good, and is demoable on its own.

## The "essential UDS fields" this pathway surfaces

Per-patient data that feeds UDS Tables 3A/3B/4 (see
[`UDS-DATA-MODEL.md`](./UDS-DATA-MODEL.md) for full specs):

**Already in OpenEMR (reuse — just display):**
- Age / date of birth, sex
- Race, Hispanic/Latino ethnicity
- Preferred language / language barrier
- ZIP code of residence

**New (this project adds):**
- Income & **% of Federal Poverty Level** band (household size + income)
- **Sliding-fee** eligibility/tier (derived from FPL)
- **Special populations:** agricultural worker (migratory/seasonal), homeless
  (+ housing type), public-housing resident, veteran, school-based
- **Principal insurance → UDS payer category** (None/Medicaid/Medicare/Other
  Public/Private)

## The pathway (ordered)

### Step 1 — Host shell + minimal design system  → builds #10 + #12
Stand up the `OpenEMR\FQHC` module so it can render a modern page, and create
the **minimum** design-system foundation it renders with: design tokens
(color, type scale, spacing, radius, focus), a responsive page layout, and a
handful of components (page header, card, definition/field row, status badge,
empty-state). Keep it lean — just enough to make Step 2 look good.

**Visible result:** an FQHC page in the app that already looks modern and
reflows cleanly on phone/tablet/desktop.

### Step 2 — UDS Patient Snapshot (read-only)  → #14
A new page for a selected patient that lays out the essential UDS fields above
in the design system. Pull the **existing** demographics live; show the **new**
fields as styled "Not yet recorded" empty-states. Responsive: a single column
on phone, multi-column cards on desktop.

**Visible result:** open a patient → see their full UDS data picture, beautiful
and responsive, in one place. (No new data captured yet — this is the "wow"
milestone and the skeleton everything else fills in.)

### Step 3 — Capture income & FPL band  → #15
Make the income card editable: household size + annual income → **computed FPL
band** shown live, plus the derived sliding-fee tier. Backed by the FPL
foundation (versioned guideline table + computation service + income side
table) from `UDS-DATA-MODEL.md` §2.1–2.2.

**Visible result:** enter income, watch the FPL band and fee tier update.

### Step 4 — Capture special-population statuses  → #16
Make the special-populations card editable: agricultural worker
(migratory/seasonal), homeless (+ housing type), public housing, veteran,
school-based — effective-dated, per `UDS-DATA-MODEL.md` §2.3.

**Visible result:** record and see a patient's special-population statuses.

### Step 5 — Surface insurance as UDS payer category  → #17
Classify the patient's existing insurance into the UDS payer bucket
(None/Medicaid/Medicare/Other Public/Private) using the "last visit" rule, and
show it on the Snapshot. Config-backed payer map (`fqhc_payer_uds_map`).

**Visible result:** the insurance card shows the UDS payer category, not just
the raw plan name.

## Done = the first milestone

After Step 5 you can open any patient and **see and edit every essential
UDS-required field** in a modern, responsive interface. That is the deliverable
this pathway targets.

## What comes after (not part of this pathway)
- Roll the captured data up into the **report tables** (ZIP, 3A, 3B, 4) with
  drill-down — UDS epic #4.
- Wire **Tables 6B/7 clinical measures** to the CQM engine at the 2026 eCQM
  versions — UDS epic #4 / `UDS-DATA-MODEL.md` §3.
- Role-specific workspaces (#6) and broader responsive rollout (#7).

## Guardrails (do in parallel, not blocking)
- [#8 CI certification gate](https://github.com/Simonparkershames/openemr-fqhc/issues/8)
  is worth turning on early so the Snapshot work can't regress the certified
  core. [#9 upstream-sync](https://github.com/Simonparkershames/openemr-fqhc/issues/9)
  can wait until there's a cadence to maintain.
- Every step keeps the certification suite green and touches the certified core
  **additively only** (see [`PRINCIPLES.md`](./PRINCIPLES.md) /
  [`ARCHITECTURE.md`](./ARCHITECTURE.md)).
