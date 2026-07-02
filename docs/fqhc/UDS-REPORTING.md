# UDS Reporting: Data Elements, Coverage, and Gaps

The HRSA **Uniform Data System (UDS)** is the annual report every FQHC submits.
This document maps the UDS report tables to OpenEMR data, identifies what is
already captured, and lists the gaps this project must close.

> Scope note: line numbers and exact measures change year to year. This is a
> structural map, not a substitute for the current-year **UDS Manual**. Each
> reporting feature is built to the official specification for the reporting
> year and validated against it.

> **For the concrete OpenEMR changes** — field-level specs, the proposed side-
> table schema, payer mapping, and FHIR/UDS+ notes — see
> [`UDS-DATA-MODEL.md`](./UDS-DATA-MODEL.md). This document stays at the level
> of "which table needs what."

## UDS tables at a glance

| Table | Subject | Primary OpenEMR source | Coverage today |
|-------|---------|------------------------|----------------|
| Patients by ZIP | Service area | Demographics (address/ZIP) | ✅ mostly |
| 3A | Patients by age & gender | Demographics (DOB, sex) | ✅ |
| 3B | Race, ethnicity, language | Demographics (CDC-coded) | ✅ certified fields |
| 4 | Income (% FPL), insurance source, managed care, special populations | Demographics + **new** socioeconomic/special-pop data + insurance | ⚠️ partial — major gaps |
| 5 / 5A | Staffing, utilization, FTEs, tenure | Scheduling, encounters, users/facilities | ⚠️ partial |
| 6A | Selected diagnoses & services | Billing (ICD-10), encounters (CPT/HCPCS) | ✅ data exists; needs report |
| 6B | Clinical quality measures | **CQM/AMC/CDR engine** | ⚠️ measure map + report shipped (`src/FQHC/Reporting/Clinical/*`); live engine population counts pending |
| 7 | Health outcomes & disparities (A1c, BP, prenatal, birth weight) | CQM engine + clinical data | ⚠️ 2 eCQM-backed lines mapped; prenatal care/birth weight and disparity stratification not yet built |
| 8A | Financial costs | Accounting/billing | ❌ largely out of scope for EHR |
| 9D / 9E | Patient service & other revenue | Billing/payments | ⚠️ partial |
| Appendix D | Health IT | Configuration/attestation | ✅ certified capabilities |

Legend: ✅ captured · ⚠️ partial · ❌ gap.

## What OpenEMR already gives us

- **Demographics** with CDC/ONC-coded race, ethnicity, and preferred language —
  these are certified fields and feed Table 3B directly. **Reuse, do not
  re-code** (see [`PRINCIPLES.md`](./PRINCIPLES.md) #1).
- **Age/gender/ZIP** for Tables 3A and the ZIP-code table.
- **A working clinical-quality engine** — the CQM, AMC (Automated Measure
  Calculation), and CDR (Clinical Decision Rules) rulesets in
  `library/classes/rulesets/`, `src/Cqm/`, and `src/Services/Qdm/` already
  compute eCQMs. Many UDS Table 6B/7 measures are the same underlying eCQMs
  (e.g. controlling high blood pressure, diabetes A1c poor control, cervical /
  colorectal / breast cancer screening, childhood immunization status,
  depression screening and follow-up, weight assessment/BMI, tobacco screening
  and cessation). The work is **mapping and packaging**, not building a
  measure engine from scratch.
- **Billing data** (ICD-10 diagnoses, CPT/HCPCS services) for Table 6A and the
  visit/utilization counts behind Table 5.
- **Insurance** records that distinguish Medicaid / Medicare / private /
  self-pay, which Table 4's principal third-party source needs.

## The real gaps (FQHC-specific data not in stock OpenEMR)

These drive Table 4 and parts of Table 5, and are the bulk of the new data
capture work. They land in **new side tables** (Architecture strategy #2), not
edits to certified tables.

1. **Income as a percentage of the Federal Poverty Level (FPL).**
   Requires household size + household income + the current-year FPL guidelines
   to compute the percentage band (≤100%, 101–150%, 151–200%, >200%, unknown).
   Stock OpenEMR has no structured FPL model.

2. **Sliding Fee Discount Program (SFDP) tier.**
   The discount tier a patient qualifies for, derived from the FPL percentage,
   with an effective date and recertification tracking.

3. **Special population status** — homeless (and homeless type: shelter,
   transitional, street, doubling-up, permanent supportive housing),
   migrant/seasonal agricultural worker, public housing resident, veteran,
   school-based. Each is a Table 4 line and may also gate special grant
   funding.

4. **Principal third-party medical insurance at time of service**, normalized
   to UDS payer categories (None/uninsured, Medicaid/CHIP, Medicare, Other
   public, Private). Today insurance exists but is not classified to UDS
   buckets.

5. **Service-type and visit classification** for Table 5 (medical, dental,
   behavioral health/mental health, substance use, vision, enabling services,
   etc.) and provider-type FTE attribution.

6. **Enabling services encounters** (case management, eligibility assistance,
   transportation, interpretation, outreach) — frequently not modeled as
   billable encounters and therefore uncounted.

## Approach to UDS data capture

- New structured fields are added to demographics/intake via the **layout/form
  engine** and backed by new side tables, with validation at the boundary
  (parse, don't validate — see [`/CLAUDE.md`](../../CLAUDE.md)).
- FPL percentage and sliding-fee tier are **computed**, not free-typed, from
  household size/income against a versioned FPL guideline table (the guidelines
  change yearly; we store them as data with effective dates).
- A **UDS reporting service** consumes the existing CQM/AMC engine plus the new
  side tables and emits per-table outputs, with drill-down to the underlying
  patients for auditing and data-quality cleanup before submission.
- Every UDS field is traceable to its table/line, and every report number is
  reproducible and auditable.

## Out of scope (for the EHR)

Financial Tables 8A/9D/9E depend heavily on cost-accounting and payroll systems
outside the EHR. We capture the patient-service-revenue pieces that originate in
billing and provide exports/hooks for the rest, but full financial UDS tables
are a finance-system responsibility, not the EHR's.
