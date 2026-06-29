# UDS Data-Element Validation Against the Authoritative Manual

**Issue:** #11 (UDS data-element specs to the current-year UDS Manual) ·
**Parent:** #4 · Phase 0

This document validates the FQHC UDS data-element specs against the
**authoritative manual text** stored in this repository at
[`Documentation/UDS/UDS_2025_Manual.txt`](../../Documentation/UDS/UDS_2025_Manual.txt)
(HRSA *2025 Uniform Data System Manual*, OMB No. 0915-0193). Every table
and line number cited below was confirmed against that text; line
references in parentheses (e.g. *Table 4, Line 1*) point at the exact form
layout in the manual.

It complements [`UDS-DATA-MODEL.md`](./UDS-DATA-MODEL.md) (the concrete
schema/field design): that file says *what to build*; this file is the
*proof that the build matches the manual*, plus the discrepancies found
while checking. When the two disagree, the manual wins — fix the data
model, then update this file's status.

> **Read the reporting-year note first — it changes which rules are in
> force.** See [§0](#0-which-manual-which-reporting-year).

---

## 0. Which manual, which reporting year

There are two different documents in play, and conflating them produces
wrong line numbers and wrong required fields:

| Document | Data captured | Reports due | Status |
|----------|---------------|-------------|--------|
| **2025 UDS Manual** (stored here) | Calendar year **2025** | **Feb 15, 2026** | **In force now** — authoritative for data being reported in the current cycle |
| **CY2026 changes** (Proposed PAL) | Calendar year **2026** | Feb 2027 | Future — proposed/forward-looking |

Issue #11 and parts of `UDS-DATA-MODEL.md` describe the "current-year
(2026) UDS Manual" and bake in **CY2026 changes** (managed-care member
months removed; SOGI eliminated). Those changes are **not yet in force**:
the 2025 manual still requires the items #11 says are gone. Implications:

- **Build the data model to capture the 2025 superset** so the product can
  produce a correct report for the cycle due Feb 2026, then drop/relax
  fields for CY2026 behind a reporting-year switch rather than removing the
  capability outright.
- Anywhere a field is "removed for CY2026," treat that as a
  **reporting-year-scoped** behavior, not a schema deletion.

**Resolution (owner-directed):** we do **not** pick one year. The product
is **reporting-year-aware** — it shows each staff member the field set for
the manual version in force for the data they are working on, keyed off the
**encounter date of service** (or an explicit reporting-year context for
patient-level attributes), never the data-entry timestamp. A late CY2025
correction and CY2026 charting coexist in the same install. The full design
is in [§8](#8-reporting-year-aware-field-rendering). Member-months and SOGI
are therefore **captured in the schema but shown/validated per version**,
not deleted.

---

## 1. Income & FPL — Table 4, Lines 1–6 ✅ verified

Manual form layout (Table 4, "Income as Percentage of Poverty Guideline"):

| Line | Band | Data-model `FplBand` |
|------|------|----------------------|
| 1 | 100% and below | `≤100` |
| 2 | 101–150% | `101–150` |
| 3 | 151–200% | `151–200` |
| 4 | Over 200% | `>200` |
| 5 | **Unknown** | `Unknown` |
| 6 | TOTAL (Sum of Lines 1–5) | (derived) |

**Confirmed rules (manual narrative, "Income as a Percentage of Poverty
Guideline, Lines 1–6"):**

- Use the FPG **based on the location of the health center**; all states
  except **Alaska and Hawaii** use the standard guidelines, which justifies
  the `contiguous / AK / HI` split in `fqhc_fpl_guideline`. ✅
- Income must be **collected at or within 12 months of the last calendar
  year visit**; otherwise report on **Line 5 as "Unknown."** ✅ matches the
  "missing data ⇒ Unknown" rule.
- **DO NOT** allocate "Unknown" income to other bands. ✅
- **DO NOT** classify homeless / migratory-seasonal / Medicaid patients as
  below-FPG on those factors alone. ⚠️ **Spec gap:** the data model should
  *not* infer income band from special-population or Medicaid status. Add an
  explicit note/guard so a future "derive band from status" optimization is
  never introduced.

**Verdict:** band enum and Unknown handling match the manual exactly. One
spec note to add (no-inference guard).

---

## 2. Sliding-fee tier — derived, no UDS line ✅ conceptually clean

The sliding-fee discount program (SFDP) tier is **not a UDS Table 4 line**;
it is an operational value derived from the FPL band against the center's
board-approved schedule. The manual references board policy and the Health
Center Program Compliance Manual rather than prescribing tiers. So:

- Keep `SlidingFeeTier` / `SlidingFeeSchedule` as **center-configurable**,
  not hard-coded to UDS bands. ✅
- Effective + recertification dates are an operational requirement, not a
  UDS field — fine to keep in the side table.

**Verdict:** no manual line to validate against; design is appropriately
config-driven. No change.

---

## 3. Special populations — Table 4, Lines 14–26 ⚠️ enum corrections

Manual form layout (Table 4, "Special Medically Underserved
Populations"):

| Line | Population | Notes from manual |
|------|-----------|-------------------|
| 14 | Migratory Agricultural Workers / family | **330g awardees only** |
| 15 | Seasonal Agricultural Workers / family | **330g awardees only** |
| 16 | Total Migratory & Seasonal | **all health centers report** |
| 17 | Homeless — Shelter | 330h awardees only |
| 18 | Homeless — Transitional | 330h awardees only |
| 19 | Homeless — Doubling Up | 330h awardees only |
| 20 | Homeless — Street | 330h awardees only |
| 21a | Homeless — Permanent Supportive Housing | 330h awardees only |
| 21 | Homeless — Other | 330h awardees only |
| 22 | Homeless — **Unknown** | 330h awardees only |
| 23 | Total Homeless Population | **all health centers report** |
| 24 | Total School-Based Service Site Patients | all report; derive from Form 5B site |
| 25 | Total Veterans | all report |
| 26 | Total Residents of Public Housing | all report |

**Discrepancies vs. issue #11 / data model:**

1. ⚠️ **Homeless housing type is missing `Unknown` (Line 22).** Issue #11
   lists `shelter / transitional / street / doubling_up /
   permanent_supportive_housing / other`. The manual has a seventh
   reportable category, **Unknown (Line 22)** — "known to be experiencing
   homelessness whose housing arrangements are unknown." The
   `HomelessStatus` enum **must add an `Unknown` case**, or Line 22 can
   never be populated and the homeless total (Line 23) will under-count.
2. ℹ️ **Line ordering** in the manual is shelter(17), transitional(18),
   doubling-up(19), street(20), permanent-supportive-housing(21a),
   other(21), unknown(22) — note 21a precedes 21. Persist a stable code per
   case; do **not** rely on enum order for line mapping.
3. ✅ **Public housing = Line 26**, veterans = Line 25, school-based =
   Line 24 — all confirmed as #11 states.
4. ℹ️ **Awardee-type gating:** the migratory/seasonal breakout (14/15) and
   all homeless breakout lines (17–22) are reported **only by the relevant
   330g/330h awardees**; the *totals* (16, 23) are reported by everyone.
   The reporting layer needs the center's **funding authorities** (330g/h/i)
   as config to know which breakout lines to emit. Capture status for all
   patients regardless; gate only the *report output*.
5. ✅ Effective-dating (status counted if held **any time in the year**) is
   consistent with the manual's annual-counting model.

**Verdict:** add `Unknown` to the homeless enum; record awardee-type config
as a reporting-layer input; otherwise aligned.

---

## 4. Insurance → UDS payer category — Table 4, Lines 7–12 ⚠️ granularity

Manual form layout (Table 4, "Primary Third-Party Medical Insurance,"
columns split 0–17 / 18+):

| Line | Category |
|------|----------|
| 7 | None/Uninsured |
| 8a | Medicaid (Title XIX) |
| 8b | CHIP Medicaid |
| 8 | Total Medicaid (8a + 8b) |
| 9a | Dually Eligible (Medicare and Medicaid) — *memo* |
| 9 | Medicare (inclusive of dually eligible) |
| 10a | Other Public Insurance (Non-CHIP) |
| 10b | Other Public Insurance CHIP |
| 10 | Total Public Insurance (10a + 10b) |
| 11 | Private Insurance |
| 12 | TOTAL (7 + 8 + 9 + 10 + 11) |

**Confirmed rules (manual narrative + ZIP-code table notes):**

- **No "Unknown" insurance** category exists on Table 4 — every patient is
  classified. ⚠️ The payer classifier must always resolve to one of the
  five top-level buckets (it may not emit "Unknown"). This differs from the
  income rule and is a common modeling mistake.
- **Dually eligible → report as Medicare** (Medicare billed before
  Medicaid), *except* a still-working Medicare patient with employer
  coverage → primary is the employer plan. ✅ This is the "principal
  insurance" tie-break and must live in the classifier.
- Medicare administered by a private company → **Medicare** (not Private).
- Medicaid/CHIP managed care administered by a private company →
  **Medicaid/CHIP/Other Public** (not Private).
- ℹ️ The 5-category model in #11/`UdsPayerCategory`
  (None / Medicaid / Medicare / Other Public / Private) matches the **ZIP
  Code Table** grouping (which combines Medicaid+CHIP+Other Public into one
  column) and the **Table 4 top-level** lines — but Table 4 itself requires
  the **8a/8b, 9a, 10a/10b sub-splits**. **Decision needed:** if the product
  must emit Table 4 (not just the Snapshot/ZIP rollup), the mapping table
  `fqhc_payer_uds_map` needs to resolve to the **sub-line** granularity,
  not only the 5 buckets. For the Snapshot (#17) the 5 buckets are
  sufficient; for the Table 4 report (#4) they are not.

**CY2026 note:** issue #17 correctly flags **managed-care member months
(Lines 13a–13c) removed for CY2026** — but they are **present and required
in the 2025 manual** (Capitated / Fee-for-service / Total Member Months, by
Medicaid/Medicare/Other Public/Private). See [§0](#0-which-manual-which-reporting-year):
if a CY2025 report is in scope, member months must be captured.

**Verdict:** classifier must (a) never emit Unknown, (b) encode the
dually-eligible and managed-care tie-breaks, (c) reach sub-line granularity
if Table 4 output is required. Member-months capability is reporting-year
scoped.

---

## 5. Service/visit classification — Table 5 & Table 6A ✅ spec'd

### 5.1 Table 5 service categories (verified against the form)

The manual ("Instructions for Table 5") enumerates the service categories
that carry visits and patients, each reported across **Column A** (FTE),
**Column B** (clinic / in-person visits), **Column B2** (virtual visits),
and **Column C** (unduplicated patients *within* that category):

| UDS service category | Maps from (OpenEMR) |
|----------------------|---------------------|
| Medical | encounter category / provider specialty flagged medical |
| Dental | dental encounters / dental module |
| Mental health | behavioral-health encounters (MH) |
| Substance use disorder (SUD) | behavioral-health encounters (SUD) |
| Vision | optometry/ophthalmology encounters |
| Other professional | other clinical encounters not above |
| Enabling | case management, eligibility, outreach, transportation, interpretation, health education (non-clinical) |

**Critical counting rule (differs from Tables 3A/3B/4/ZIP):** Table 5
patient counts are **unduplicated *within* a service category but duplicated
*across* categories** — a patient seen for medical and dental counts once in
each. The reporting service must therefore count per-category, **not** reuse
the global unduplicated patient set. This is the single biggest correctness
trap in Table 5.

**FTE (Column A) rules confirmed:**

- Personnel are allocated by **function as annualized FTE**, *except*
  physicians and dentists (reported by headcount/standard rules).
- **DO NOT** report FTE for fee-for-service-paid individuals (no time
  basis) — but their **visits still count** in Column B/B2 and their
  patients in Column C. ⚠️ So provider FTE and provider visits are
  **separately gated** — a provider can contribute visits without
  contributing FTE. The data model must not assume FTE ⇒ visits or vice
  versa.
- FTE is reported only on the **Universal Report**, not Grant Reports.

### 5.2 Table 6A — Selected Diagnoses and Services Rendered

- Diagnosis/service **Lines 1–20f**, mapped from **coded encounter data**
  (ICD-10 diagnoses, CPT/service codes) — this is a code-set mapping
  problem, well-suited to the existing CQM/AMC engine rather than new
  capture (consistent with epic #4's "wire 6B/7 to the CQM engine").
- The manual notes (Changes & Highlights) that several 6A codes moved lines
  year-over-year — another reason 6A mapping must be **manual-version-aware**
  (see §8), keyed to the encounter's service date.

### 5.3 Pharmacy caveat vs. issue #11

Issue #11 lists "pharmacy" as a Table 5/6A service line. Per the manual,
pharmacy surfaces as **Table 8A cost lines (8a Pharmacy not incl.
pharmaceuticals / 8b Pharmaceuticals)** and within Table 6A services — it is
**not** one of the seven Table 5 *utilization* service categories above.
Treat pharmacy as a cost/6A element, not a Table 5 visit category.

**Verdict:** service categories and counting rules confirmed against the
manual. Implementation follow-up (epic #4, not the #13 Snapshot pathway):
build the OpenEMR encounter-category / specialty → UDS-service-category map
and the provider personnel-type → FTE map, both manual-version-aware.

---

## 6. Cross-table integrity checks worth encoding as tests

The manual states equalities the reporting layer can assert (cheap data-
quality guards, and good acceptance tests for #4):

- `Table 3B, Line 8 = Table 3A, Line 39 = ZIP Code Table total = Table 4,
  Lines 6 and 12` (total unduplicated patients agree across tables).
- ZIP Code Table **Column C (Medicaid/CHIP/Other Public)** must equal the
  sum of the corresponding Table 4 insurance lines; **Column D (Medicare)**
  must equal Table 4 Line 9.
- Special-population **totals** (Lines 16, 23) are reported by all centers;
  breakouts only by the relevant awardee type.

---

## 7. Summary of required changes (actionable)

| # | Element | Change | Severity |
|---|---------|--------|----------|
| 1 | Homeless enum | Add `Unknown` case (Table 4 Line 22) | **blocking** for Line 23 accuracy |
| 2 | Income | Add no-inference guard (never derive band from status/Medicaid) | correctness |
| 3 | Payer classifier | Never emit "Unknown"; encode dually-eligible + managed-care tie-breaks | correctness |
| 4 | Payer mapping | Resolve to Table 4 sub-lines (8a/8b/9a/10a/10b) if Table 4 output is in scope | depends on #4 scope |
| 5 | Reporting year | Reporting-year-aware rendering keyed off service date (§8); CY2026 removals are version-scoped, not schema deletions | **resolved** (§8) |
| 6 | Awardee config | Capture 330g/h/i funding authorities to gate breakout lines | reporting-layer |
| 7 | Service lines | Table 5 categories + counting rules spec'd (§5); OpenEMR mapping is epic #4 follow-up | spec'd |
| 8 | Table 5 counting | Count patients per-service-category (duplicated across, unduplicated within) — not the global patient set | **correctness trap** |
| 9 | FTE vs visits | Provider FTE and provider visits are separately gated (FFS providers: visits yes, FTE no) | correctness |

Items 1–3 and 8–9 are concrete corrections to the `OpenEMR\FQHC`
enums/classifier and the reporting service in PR #1; item 5 is resolved by
the design in §8.

---

## 8. Reporting-year-aware field rendering

**Goal:** staff entering or editing data automatically see the field set for
the **manual version in force for that data**, so a late CY2025 correction
and CY2026 charting coexist in one install without manual toggling and
without misclassifying late/retrospective entries.

### 8.1 Principle: key off the service date, never the entry date

The UDS reporting year for a datum is the **calendar year of the encounter's
date of service**, not when staff typed it. A visit dated 2025-12-30 entered
on 2026-03-01 belongs to **CY2025** and must show CY2025 fields. Keying off
the data-entry timestamp is the bug this design exists to prevent.

- **Encounter-scoped data** → resolve the manual version from the
  encounter's **date of service**.
- **Patient-level annual attributes** (income, special-population status,
  payer) → resolve from an **explicit reporting-year context** (the screen's
  selected reporting period), defaulting to the current open period;
  combined with the existing effective-dating so a patient active in both
  2025 and 2026 carries the right value per year.

### 8.2 Mechanism

1. **Schema stores the superset.** No UDS field is ever physically removed.
   "Removed for CY2026" (member months, SOGI) means *not shown / not
   validated* for that version — the columns persist so prior years stay
   reportable.
2. **`UdsManualVersion` enum** — `Cy2025`, `Cy2026`, … (unit enum; runtime
   state). Each future manual adds a case.
3. **`ManualVersionResolver`** (injected, no globals) —
   `fromServiceDate(DateTimeImmutable): UdsManualVersion` and
   `fromReportingYear(ReportingYear): UdsManualVersion`. The calendar-year →
   version mapping is a small versioned lookup.
4. **Version-aware field sets** — each card/screen resolves "which fields,
   enum cases, and validations apply?" via an **exhaustive `match`** on the
   version (no `default`, so a new manual year fails the build until handled
   — exactly the project's exhaustive-matching rule).
5. **Same map drives reports** — Table generation projects the stored
   superset onto the target year's layout using the identical version map,
   so capture and reporting can never drift (and 6A line-number shifts, §5.2,
   are handled in one place).

### 8.3 Edge cases

- **Cross-year patients:** effective-dated annual attributes + per-year
  version filter resolve differing income/payer across 2025 and 2026.
- **Standalone profile edits** (no encounter): require an explicit
  reporting-year selector; default to the current open reporting period.
- **New encounter dated today:** entry date and service date coincide, so
  the default is correct — but the resolver still reads the *service date*
  field, so back-dating immediately flips the field set.

This generalizes to every future manual change, not just the 2025→2026
transition, and is the recommended implementation of the §0 resolution.
