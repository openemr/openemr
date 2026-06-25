# UDS Data Model: Concrete OpenEMR Changes for FQHC Reporting

This is the **heart of the functionality**: the specific data-capture changes
OpenEMR needs so an FQHC can produce its UDS report. It expands the gap list in
[`UDS-REPORTING.md`](./UDS-REPORTING.md) into field-level specs and a proposed
schema.

> **Authoritative source & a caveat on line numbers.** Build every feature to
> the **current reporting-year UDS Manual** and the HRSA **Table fact sheets**.
> HRSA publishes a *Proposed PAL* (Program Assistance Letter) each year (the
> 2026 changes were announced via the 2026 Proposed PAL) and finalizes the
> manual later. Category *names* and reporting *rules* below are stable
> year-to-year and were confirmed against HRSA guidance; exact **line numbers**
> shift and must be reconciled against the 2026 manual before a table is
> certified "done." Treat FPL guidelines and measure value sets as **versioned
> data**, not hard-coded constants.
>
> **Known 2026 change captured here:** SOGI (sexual orientation / gender
> identity) was *optional* for 2025 and is being **eliminated for 2026** — so
> this project does **not** add SOGI as a new UDS requirement (OpenEMR already
> has the fields if a center wants them locally).

---

## 1. What OpenEMR already has vs. what UDS needs

| UDS need | OpenEMR today | Action |
|----------|---------------|--------|
| ZIP of residence (ZIP table) | `patient_data.postal_code` | **Reuse** |
| Age & sex (Table 3A) | `DOB`, `sex` | **Reuse** |
| Race (Table 3B) | `patient_data.race` (CDC-coded) | **Reuse**; verify mapping to UDS race rollups |
| Hispanic/Latino ethnicity (3B) | `patient_data.ethnicity` | **Reuse** |
| Language barrier (3B) | `patient_data.language` | **Reuse**; derive "best served in a language other than English" |
| Income as % FPL (Table 4) | — none — | **NEW**: household size + income + versioned FPL table → computed band |
| Sliding-fee discount tier | partial (`sliding_fee`/fee schedules exist but not FPL-driven) | **NEW**: derive from FPL %, store with effective date |
| Principal medical insurance, UDS categories (Table 4) | `insurance_data` (payer + type) | **NEW mapping** to UDS buckets + "from last visit" logic |
| Managed care member months (Table 4) | coverage dates in `insurance_data` | **NEW computation** |
| Agricultural worker — migratory/seasonal (Table 4) | — none — | **NEW** structured status |
| Homeless + homeless type (Table 4) | — none — | **NEW** structured status |
| Resident of public housing (Table 4, line 26) | — none — | **NEW** structured status |
| Veteran status (Table 4) | — none (not structured for UDS) — | **NEW** structured status |
| School-based health center patient (Table 4) | — none — | **NEW** (or derive from service site) |
| Service/visit type for utilization (Table 5) | encounters, `categories`, providers | **Reuse + classify** to UDS service lines |
| Provider type / FTE (Table 5, 5A) | `users`, facilities | **NEW** FTE/personnel-type config |
| Selected diagnoses & services (Table 6A) | ICD-10 (`lists`), CPT (`billing`) | **Reuse**; build report mapping |
| Clinical quality measures (Table 6B) | **CQM/AMC/CDR engine** | **Reuse engine**; map measures to UDS lines |
| Health outcomes & disparities (Table 7) | CQM engine + clinical data | **Reuse engine**; map measures |
| Financial (8A, 9D, 9E) | billing/payments (partial) | **Mostly out of scope** — finance system owns these |

**Bottom line:** the demographics/Table-4 socioeconomic and special-population
data is the real build. Clinical tables are mostly *mapping* onto the engine
OpenEMR already ships.

---

## 2. New data elements — field specs

All new patient-level data lands in **side tables keyed by `pid`** (never edits
to certified columns — see [`ARCHITECTURE.md`](./ARCHITECTURE.md) §"Schema
strategy"), surfaced in the demographics/intake UI via the layout engine.

### 2.1 Income & Federal Poverty Level

UDS reports patients by income band as a % of FPL: **≤100%, 101–150%,
151–200%, >200%, Unknown**. The % is *computed*, never typed.

Reporting rules baked into the computation:
- Use the **FPL guideline for the health center's location/state**, not the
  patient's home state.
- If income or household size is missing, the band is **Unknown** — do **not**
  default to ≤100%.
- FPL guidelines change annually and differ for the 48 contiguous states vs.
  Alaska vs. Hawaii.

Captured fields (`fqhc_patient_income`, one current + history):

| Field | Type | Notes |
|-------|------|-------|
| `pid` | int | FK to patient |
| `household_size` | int (≥1) | annual household/family size |
| `annual_income` | decimal | self-reported household income |
| `income_unknown` | bool | explicit "declined/unknown" |
| `effective_date` | date | when this determination was taken |
| `recorded_by` | int | user id (audit) |

Derived (not stored, or stored as a materialized snapshot for the report year):
`fpl_percent`, `fpl_band` (enum), against a versioned guideline table.

`fqhc_fpl_guideline` (reference data, versioned):

| Field | Type |
|-------|------|
| `guideline_year` | int |
| `region` | enum: `contiguous` / `alaska` / `hawaii` |
| `base_amount` | decimal (1-person household) |
| `per_person_increment` | decimal |

> Domain primitive: model `FplPercentage` / `FplBand` as typed value objects;
> compute via an injected service that takes the center's region + year.

### 2.2 Sliding Fee Discount Program (SFDP) tier

| Field | Type | Notes |
|-------|------|-------|
| `pid` | int | |
| `fpl_band` | enum | source of the determination |
| `discount_tier` | enum/int | center-configured nominal-fee tiers |
| `effective_date` / `recert_due_date` | date | recertification tracking |

Tier is derived from FPL % against the center's **configurable** SFDP schedule
(centers set their own tier breakpoints within HRSA rules). Schedule is config,
not hard-coded.

### 2.3 Special-population statuses

UDS counts a patient in a special population if the status applied **at any
point during the reporting year**, so these are effective-dated statuses, not a
single current flag.

`fqhc_special_population` (one row per status per patient):

| Field | Type | Notes |
|-------|------|-------|
| `pid` | int | |
| `population` | enum | `agricultural_worker`, `homeless`, `public_housing`, `veteran`, `school_based` |
| `subtype` | enum/null | see below |
| `as_of_date` | date | when recorded / applies |
| `source` | enum | self-report, intake, derived |

Subtype value sets:
- **Agricultural worker** → `migratory` \| `seasonal` (UDS distinguishes these).
- **Homeless** → housing status: `shelter`, `transitional`, `street`,
  `doubling_up`, `permanent_supportive_housing`, `other` (per UDS homeless
  categories).
- **Public housing**, **veteran**, **school-based** → no subtype (school-based
  may instead be *derived* from the service site/facility flag).

Model each `population` and each subtype as a PHP **enum**; use exhaustive
`match` when rolling up to UDS lines so a new category can't be silently
dropped.

### 2.4 Insurance → UDS payer classification

UDS Table 4 reports **principal third-party medical insurance** in fixed
categories, generally split by age (under 18 / 18 and older):
**None/Uninsured, Medicaid (incl. CHIP where applicable), Medicare, Other
Public (incl. non-Medicaid CHIP), Private.** Plus **managed care member
months**.

OpenEMR has `insurance_data` (payer, plan, coverage dates, type) but does not
classify to UDS buckets. Work:
- A mapping from OpenEMR payer/insurance-type → `UdsPayerCategory` enum
  (configurable per install, because local payer setups vary).
- "**Principal insurance from the last visit in the year**" selection logic —
  report by the coverage in effect at the patient's last visit, *even if that
  visit wasn't billed*.
- **Managed care member months**: compute from coverage spans within the
  reporting year by payer category.

No new patient table needed — this is a **reporting-time classifier** plus a
small config/mapping table (`fqhc_payer_uds_map`).

### 2.5 Service / visit classification (utilization, Tables 5 & 6A)

Table 5 needs visits and patients by **service type**: medical, dental, mental
health, substance use disorder, vision, **enabling services**, pharmacy, and
other professional services — with provider **FTEs** by personnel type.

- Map OpenEMR encounter categories / provider specialties → a
  `UdsServiceLine` enum (config-backed mapping).
- **Enabling services** (case management, eligibility assistance,
  transportation, interpretation, outreach, health education) are frequently
  *not* modeled as countable encounters today → define an enabling-services
  encounter/visit type so they're captured and counted.
- **FTE / personnel-type** config per user/facility for the staffing lines
  (Table 5 / 5A tenure) — a new config surface, low complexity but new.

---

## 3. Clinical quality (Tables 6B & 7): reuse the engine, build the mapping

OpenEMR already computes eCQMs via the CQM / AMC / CDR engine
(`src/Cqm/`, `src/Services/Qdm/`, `library/classes/rulesets/`). Most UDS
clinical measures are the same underlying eCQMs. The work is a **UDS measure
map** (UDS line ↔ eCQM/CMS id ↔ value sets) plus packaging into the report,
not a new measure engine.

Representative measures to map (validate the exact set + specs against the 2026
manual):

- **Table 6B (process):** childhood immunization status; cervical cancer
  screening; colorectal cancer screening; breast cancer screening; weight
  assessment & counseling (child/adolescent BMI); adult weight screening &
  follow-up; tobacco use screening & cessation; depression screening &
  follow-up; dental sealants; HIV screening; screening for social drivers of
  health (SDOH).
- **Table 7 (outcomes/disparities):** controlling high blood pressure;
  diabetes HbA1c poor control (>9%); early entry into prenatal care (first
  trimester); low birth weight; HIV linkage to care.

Each mapped measure needs: numerator/denominator/exclusion definitions tied to
the engine, the UDS age stratifications, and the disparity stratifications
(by race/ethnicity/sex/special-population) that Table 7 requires.

---

## 4. UDS+ patient-level submission (FHIR) — design for it now

HRSA's **UDS Modernization / UDS+** initiative is moving from aggregate tables
toward **patient-level data** submitted as **FHIR** bundles. OpenEMR is already
**ONC-certified for FHIR US Core**, which is a strong foundation.

Implication for this project: the new FQHC data above should be **FHIR-mappable**
so we can emit UDS+ bundles without re-collecting anything —
- income/FPL, housing/homeless status, veteran status, and agricultural-worker
  status map to FHIR observations / extensions (several align with **US Core
  SDOH** and **Gravity Project** value sets);
- demographics, insurance (Coverage), encounters, and conditions already have
  US Core profiles.

Action: when specifying the side tables (§2), record the intended FHIR
profile/value-set for each element so UDS+ export is a mapping layer, not a
second data model. This also keeps us aligned with — not divergent from — the
certified FHIR surface.

---

## 5. Reporting service & data-quality tooling

- A **`UdsReportService`** (new, in `OpenEMR\FQHC`) consumes demographics + the
  new side tables + the CQM engine + billing, and emits each table for a
  reporting year, scoped per grantee and per site.
- Every output row supports **drill-down to the underlying patients** so staff
  can fix data *before* submission (e.g., patients with Unknown FPL, missing
  insurance classification, or unmapped service lines).
- Year-round **data-quality worklists** (Phase 3) surface the same gaps
  continuously rather than at reporting season.
- Numbers must be **reproducible and auditable** — store the FPL guideline
  version, payer map version, and measure value-set version used for a given
  report run.

---

## 6. Explicitly out of scope (for the EHR)

Financial **Tables 8A (costs), 9D (patient service revenue), 9E (other
revenue)** depend on cost accounting and payroll outside the EHR. We expose the
billing-originated patient-service-revenue pieces and provide exports/hooks, but
full financial tables remain a finance-system responsibility.

---

## 7. How this becomes tickets

This document is the detail behind issues **#4** (UDS epic) and **#11** (UDS
data-element specs). The natural Phase 1 vertical slices, smallest-first:

1. **FPL foundation** — `fqhc_fpl_guideline` data + income side table +
   computation service + band enum (no UI yet, fully unit-tested).
2. **Income/FPL intake UI** — demographics fields → side table, showing the
   computed band and SFDP tier.
3. **Special-population statuses** — enums + side table + intake UI.
4. **Payer UDS classifier** — mapping table + "last visit" logic + member
   months.
5. **First report tables** — ZIP, 3A, 3B, 4 from the above, with drill-down.
6. **Clinical mapping** — wire 6B/7 to the CQM engine.

Each slice is independently shippable and certification-safe.
