# CCDA PHP Migration Plan

## Overview

This document provides a complete, executable plan to migrate OpenEMR's CCDA generation from the current Node.js-based architecture to a pure PHP implementation.

### Current State
- PHP (`EncounterccdadispatchTable`) fetches data and formats as proprietary XML
- XML sent via TCP socket to Node.js service on port 6661
- Node.js parses XML, restructures to JSON, uses `oe-blue-button-generate` templates
- Node.js returns CDA-compliant XML

### Target State
- PHP fetches data and hydrates typed model objects
- PHP renderer converts models directly to CDA XML using DOMDocument
- No external processes, sockets, or Node.js dependencies

### Success Criteria
1. Generated CCDA output is byte-for-byte identical (after timestamp normalization) to current Node output
2. All existing tests pass
3. Schematron validation passes (via healthit.gov or local validator)
4. Node.js service and related code completely removed

---

## Specification References

All OIDs, template IDs, code systems, and other identifiers used in this implementation come from official specifications. **Every magic constant MUST include a docblock comment citing its source.**

### Primary Specifications

| Specification | URL | Used For |
|--------------|-----|----------|
| HL7 CDA R2 | https://www.hl7.org/implement/standards/product_brief.cfm?product_id=7 | Base CDA schema, structural elements |
| C-CDA 2.1 | https://www.hl7.org/ccdasearch/ | Template IDs, section requirements, entry constraints |
| C-CDA 3.0 | https://hl7.org/cda/us/ccda/3.0.0/ | Updated template IDs with 2023-05-01 extensions |
| C-CDA Companion Guide | https://www.hl7.org/implement/standards/product_brief.cfm?product_id=447 | Implementation guidance |

### Code System Registries

| Registry | URL | Used For |
|----------|-----|----------|
| HL7 OID Registry | https://www.hl7.org/oid/index.cfm | All HL7-assigned OIDs |
| SNOMED CT | https://www.snomed.org/ | Clinical terminology codes |
| LOINC | https://loinc.org/ | Lab/observation codes, document type codes |
| RxNorm | https://www.nlm.nih.gov/research/umls/rxnorm/ | Medication codes |
| CVX | https://www2.cdc.gov/vaccines/iis/iisstandards/vaccines.asp?rpt=cvx | Vaccine codes |
| ICD-10-CM | https://www.cms.gov/medicare/coding-billing/icd-10-codes | Diagnosis codes |
| CPT | https://www.ama-assn.org/amaone/cpt-current-procedural-terminology | Procedure codes |
| NPI Registry | https://npiregistry.cms.hhs.gov/ | Provider identifiers |

### Template ID Lookup

The authoritative source for C-CDA template IDs is the **C-CDA Template ID Lookup Tool**:
https://www.hl7.org/ccdasearch/

Each template ID should be documented with:
1. The template name
2. The specification section (e.g., "C-CDA 2.1 Section 3.4")
3. The template OID and any version-specific extensions

### Documentation Requirements

Every enum case, constant, or hardcoded identifier MUST include a docblock comment with:

```php
/**
 * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.6.1
 *      C-CDA Allergies and Intolerances Section (entries required)
 */
case ALLERGIES_SECTION = '2.16.840.1.113883.10.20.22.2.6.1';
```

For code system OIDs:

```php
/**
 * SNOMED CT (Systematized Nomenclature of Medicine - Clinical Terms)
 *
 * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.96
 * @see https://www.snomed.org/
 */
case SNOMED_CT = '2.16.840.1.113883.6.96';
```

For fixed code values (e.g., status codes, severity codes):

```php
/**
 * Active allergy status.
 *
 * SNOMED CT code for "Active" status in allergy observations.
 *
 * @see https://browser.ihtsdotools.org/?perspective=full&conceptId1=55561003
 *      SNOMED CT Concept: Active (qualifier value)
 * @see C-CDA 2.1 Section 3.4.1 - Allergy Status Observation
 */
case ACTIVE = '55561003';
```

### Tracing Values from Existing Node.js Code

When porting values from the existing Node.js implementation (`ccdaservice/serveccda.js` and `oe-blue-button-generate/`), you MUST verify each hardcoded value against the official specification before documenting it. The Node.js code is the source of truth for *what values are currently used*, but the official specs are the source of truth for *whether those values are correct*.

Process for each value:
1. Find the value in the Node.js code
2. Look up the value in the appropriate specification (SNOMED browser, VSAC, etc.)
3. Verify it matches the expected meaning
4. Document with `@see` links to the official source
5. If the Node.js value appears incorrect, flag it for review rather than blindly copying

---

## Phase 1: Foundation

### General Requirements for All Code in This Phase

**Documentation of Magic Constants:**

Every OID, template ID, code system identifier, SNOMED/LOINC/RxNorm code, and other "magic" constant MUST include a docblock comment that:

1. Explains what the value represents
2. Links to the authoritative specification where the value is defined
3. References the relevant section of C-CDA, HL7, or other standard

This enables future developers to:
- Verify the value is correct
- Understand when/why to use it
- Update it if specifications change
- Find related documentation

**Example format:**
```php
/**
 * Brief description of what this constant represents.
 *
 * Additional context about when/how it's used if helpful.
 *
 * @see https://url.to/authoritative/source
 *      Description of what that link contains
 * @see C-CDA 2.1 IG Section X.X.X (if applicable)
 */
```

---

### Task 1.1: Create Directory Structure

Create the following directory structure:

```
src/Cda/
├── Enum/
├── ValueObject/
├── Model/
│   ├── Document/
│   ├── Header/
│   └── Section/
│       ├── Allergy/
│       ├── Medication/
│       ├── Problem/
│       ├── Procedure/
│       ├── Immunization/
│       ├── Vital/
│       ├── Result/
│       ├── Encounter/
│       ├── SocialHistory/
│       ├── CareTeam/
│       ├── Payer/
│       ├── MedicalDevice/
│       ├── AdvanceDirective/
│       ├── PlanOfCare/
│       ├── FunctionalStatus/
│       ├── Referral/
│       └── ClinicalNote/
├── Hydrator/
├── Renderer/
├── Parser/
└── Service/
```

**Acceptance Criteria:**
- All directories exist
- Empty `.gitkeep` files removed once real files are added

---

### Task 1.2: Create Code System Enum

**File:** `src/Cda/Enum/CodeSystem.php`

```php
<?php

/**
 * Code System OIDs used in CDA documents.
 *
 * Each OID is registered in the HL7 OID Registry and identifies a specific
 * terminology or code system used for clinical coding.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    [Author Name] <[email]>
 * @copyright Copyright (c) 2026 [Copyright Holder]
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @see https://www.hl7.org/oid/index.cfm HL7 OID Registry (authoritative source for all OIDs)
 * @see https://terminology.hl7.org/ HL7 Terminology (THO) for code system definitions
 */

declare(strict_types=1);

namespace OpenEMR\Cda\Enum;

enum CodeSystem: string
{
    //
    // Standard Clinical Terminology Code Systems
    // @see https://www.hl7.org/fhir/terminologies-systems.html
    //

    /**
     * SNOMED CT (Systematized Nomenclature of Medicine - Clinical Terms).
     * Used for: clinical findings, procedures, body structures, organisms, substances.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.96
     * @see https://www.snomed.org/
     */
    case SNOMED_CT = '2.16.840.1.113883.6.96';

    /**
     * RxNorm - Normalized names for clinical drugs.
     * Used for: medication coding in US healthcare.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.88
     * @see https://www.nlm.nih.gov/research/umls/rxnorm/
     */
    case RXNORM = '2.16.840.1.113883.6.88';

    /**
     * LOINC (Logical Observation Identifiers Names and Codes).
     * Used for: laboratory tests, clinical observations, document types.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.1
     * @see https://loinc.org/
     */
    case LOINC = '2.16.840.1.113883.6.1';

    /**
     * ICD-10-CM (International Classification of Diseases, 10th Revision, Clinical Modification).
     * Used for: diagnosis coding in US healthcare.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.90
     * @see https://www.cms.gov/medicare/coding-billing/icd-10-codes
     */
    case ICD10_CM = '2.16.840.1.113883.6.90';

    /**
     * ICD-9-CM (International Classification of Diseases, 9th Revision, Clinical Modification).
     * Legacy diagnosis coding system, still referenced in historical data.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.103
     */
    case ICD9_CM = '2.16.840.1.113883.6.103';

    /**
     * CPT-4 (Current Procedural Terminology, 4th Edition).
     * Used for: procedure coding, maintained by AMA.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.12
     * @see https://www.ama-assn.org/amaone/cpt-current-procedural-terminology
     */
    case CPT4 = '2.16.840.1.113883.6.12';

    /**
     * CVX (Vaccine Administered Code Set).
     * Used for: vaccine/immunization coding, maintained by CDC.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.12.292
     * @see https://www2.cdc.gov/vaccines/iis/iisstandards/vaccines.asp?rpt=cvx
     */
    case CVX = '2.16.840.1.113883.12.292';

    /**
     * NDC (National Drug Code).
     * Used for: drug product identification, maintained by FDA.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.69
     * @see https://www.fda.gov/drugs/drug-approvals-and-databases/national-drug-code-directory
     */
    case NDC = '2.16.840.1.113883.6.69';

    /**
     * HCPCS (Healthcare Common Procedure Coding System).
     * Used for: procedures, supplies, services not covered by CPT.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.285
     * @see https://www.cms.gov/medicare/coding-billing/healthcare-common-procedure-system
     */
    case HCPCS = '2.16.840.1.113883.6.285';

    //
    // HL7 Internal Code Systems
    // @see https://terminology.hl7.org/codesystems.html
    //

    /**
     * HL7 ActCode - Codes for act types.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.4
     * @see https://terminology.hl7.org/CodeSystem/v3-ActCode
     */
    case HL7_ACT_CODE = '2.16.840.1.113883.5.4';

    /**
     * HL7 ParticipationType - Codes for participation roles.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.90
     * @see https://terminology.hl7.org/CodeSystem/v3-ParticipationType
     */
    case HL7_PARTICIPATION_TYPE = '2.16.840.1.113883.5.90';

    /**
     * HL7 RoleCode - Codes for entity roles.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.111
     * @see https://terminology.hl7.org/CodeSystem/v3-RoleCode
     */
    case HL7_ROLE_CODE = '2.16.840.1.113883.5.111';

    /**
     * HL7 Confidentiality - Document confidentiality codes.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.25
     * @see https://terminology.hl7.org/CodeSystem/v3-Confidentiality
     */
    case HL7_CONFIDENTIALITY = '2.16.840.1.113883.5.25';

    /**
     * HL7 NullFlavor - Codes indicating missing/unknown data.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.1008
     * @see https://terminology.hl7.org/CodeSystem/v3-NullFlavor
     */
    case HL7_NULL_FLAVOR = '2.16.840.1.113883.5.1008';

    /**
     * HL7 AdministrativeGender - Patient gender codes.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.1
     * @see https://terminology.hl7.org/CodeSystem/v3-AdministrativeGender
     */
    case HL7_ADMINISTRATIVE_GENDER = '2.16.840.1.113883.5.1';

    /**
     * HL7 MaritalStatus - Marital status codes.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.2
     * @see https://terminology.hl7.org/CodeSystem/v3-MaritalStatus
     */
    case HL7_MARITAL_STATUS = '2.16.840.1.113883.5.2';

    /**
     * HL7 ReligiousAffiliation - Religious affiliation codes.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.5.1076
     * @see https://terminology.hl7.org/CodeSystem/v3-ReligiousAffiliation
     */
    case HL7_RELIGIOUS_AFFILIATION = '2.16.840.1.113883.5.1076';

    /**
     * CDC Race and Ethnicity Code Set.
     * Used for both race and ethnicity in US healthcare.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.238
     * @see https://www.cdc.gov/phin/resources/vocabulary/
     */
    case HL7_RACE = '2.16.840.1.113883.6.238';
    case HL7_ETHNICITY = '2.16.840.1.113883.6.238';

    //
    // Provider and Organization Identifiers
    //

    /**
     * NPI (National Provider Identifier).
     * Unique identifier for healthcare providers in the US.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.4.6
     * @see https://npiregistry.cms.hhs.gov/
     */
    case NPI = '2.16.840.1.113883.4.6';

    /**
     * Healthcare Provider Taxonomy Code Set.
     * Classifies healthcare provider types and specializations.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.6.101
     * @see https://taxonomy.nucc.org/
     */
    case TAXONOMY = '2.16.840.1.113883.6.101';

    //
    // Other Code Systems
    //

    /**
     * FDA Route of Administration.
     * Codes for medication administration routes.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.3.26.1.1
     * @see https://ncit.nci.nih.gov/ncitbrowser/ (NCI Thesaurus)
     */
    case MEDICATION_ROUTE_FDA = '2.16.840.1.113883.3.26.1.1';

    /**
     * Source of Payment Typology.
     * Payer/coverage type codes.
     *
     * @see https://www.hl7.org/oid/index.cfm?Comp_OID=2.16.840.1.113883.3.221.5
     * @see https://www.nahdo.org/sopt
     */
    case SOURCE_OF_PAYMENT = '2.16.840.1.113883.3.221.5';

    public function displayName(): string
    {
        return match ($this) {
            self::SNOMED_CT => 'SNOMED CT',
            self::RXNORM => 'RxNorm',
            self::LOINC => 'LOINC',
            self::ICD10_CM => 'ICD-10-CM',
            self::ICD9_CM => 'ICD-9-CM',
            self::CPT4 => 'CPT-4',
            self::CVX => 'CVX',
            self::NDC => 'NDC',
            self::HCPCS => 'HCPCS',
            self::HL7_ACT_CODE => 'ActCode',
            self::HL7_PARTICIPATION_TYPE => 'ParticipationType',
            self::HL7_ROLE_CODE => 'RoleCode',
            self::HL7_CONFIDENTIALITY => 'Confidentiality',
            self::HL7_NULL_FLAVOR => 'NullFlavor',
            self::HL7_ADMINISTRATIVE_GENDER => 'AdministrativeGender',
            self::HL7_MARITAL_STATUS => 'MaritalStatus',
            self::HL7_RELIGIOUS_AFFILIATION => 'ReligiousAffiliation',
            self::HL7_RACE, self::HL7_ETHNICITY => 'Race & Ethnicity - CDC',
            self::NPI => 'NPI',
            self::TAXONOMY => 'Healthcare Provider Taxonomy',
            self::MEDICATION_ROUTE_FDA => 'Medication Route FDA',
            self::SOURCE_OF_PAYMENT => 'Source of Payment Typology',
        };
    }

    public static function fromString(string $name): ?self
    {
        return match (strtoupper(str_replace(['-', ' '], ['_', '_'], $name))) {
            'SNOMED', 'SNOMED_CT', 'SNOMEDCT' => self::SNOMED_CT,
            'RXNORM', 'RXCUI' => self::RXNORM,
            'LOINC' => self::LOINC,
            'ICD10', 'ICD10_CM', 'ICD10CM' => self::ICD10_CM,
            'ICD9', 'ICD9_CM', 'ICD9CM' => self::ICD9_CM,
            'CPT', 'CPT4', 'CPT_4' => self::CPT4,
            'CVX' => self::CVX,
            'NDC' => self::NDC,
            'HCPCS' => self::HCPCS,
            default => null,
        };
    }
}
```

**Acceptance Criteria:**
- Enum compiles without errors
- PHPStan passes at level 10
- All code systems used in current Node implementation are covered
- Every enum case has a docblock with `@see` link to HL7 OID Registry entry
- Every enum case has a docblock with `@see` link to the code system's official site

---

### Task 1.3: Create Template ID Enum

**File:** `src/Cda/Enum/CcdaTemplateId.php`

```php
<?php

/**
 * C-CDA Template IDs (OIDs) for document, section, and entry templates.
 *
 * Template IDs identify the conformance rules that apply to a particular
 * element in a CDA document. Each template ID corresponds to a specific
 * set of constraints defined in the C-CDA Implementation Guide.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @see https://www.hl7.org/ccdasearch/ C-CDA Template ID Lookup Tool (authoritative)
 * @see https://hl7.org/cda/us/ccda/3.0.0/ C-CDA 3.0 Implementation Guide
 * @see https://www.hl7.org/implement/standards/product_brief.cfm?product_id=492 C-CDA 2.1 IG
 */

declare(strict_types=1);

namespace OpenEMR\Cda\Enum;

enum CcdaTemplateId: string
{
    //
    // Document-Level Templates
    // @see C-CDA 2.1 IG Section 1.1 - Document Templates
    //

    /**
     * US Realm Header.
     * Required template for all US Realm CDA documents.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.1.1
     * @see C-CDA 2.1 IG Section 1.1.1
     */
    case US_REALM_HEADER = '2.16.840.1.113883.10.20.22.1.1';

    /**
     * Continuity of Care Document (CCD).
     * Primary document type for patient summary information.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.1.2
     * @see C-CDA 2.1 IG Section 1.1.2
     */
    case CCD = '2.16.840.1.113883.10.20.22.1.2';

    /**
     * Unstructured Document.
     * For documents containing non-structured (e.g., PDF, image) content.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.1.10
     * @see C-CDA 2.1 IG Section 1.1.22
     */
    case UNSTRUCTURED_DOCUMENT = '2.16.840.1.113883.10.20.22.1.10';

    //
    // Section-Level Templates
    // @see C-CDA 2.1 IG Section 2 - Section Templates
    //

    /**
     * Allergies and Intolerances Section (entries required).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.6.1
     * @see C-CDA 2.1 IG Section 2.4.1
     */
    case ALLERGIES_SECTION = '2.16.840.1.113883.10.20.22.2.6.1';

    /**
     * Medications Section (entries required).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.1.1
     * @see C-CDA 2.1 IG Section 2.39.1
     */
    case MEDICATIONS_SECTION = '2.16.840.1.113883.10.20.22.2.1.1';

    /**
     * Problem Section (entries required).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.5.1
     * @see C-CDA 2.1 IG Section 2.53.1
     */
    case PROBLEMS_SECTION = '2.16.840.1.113883.10.20.22.2.5.1';

    /**
     * Procedures Section (entries required).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.7.1
     * @see C-CDA 2.1 IG Section 2.61.1
     */
    case PROCEDURES_SECTION = '2.16.840.1.113883.10.20.22.2.7.1';

    /**
     * Results Section (entries required).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.3.1
     * @see C-CDA 2.1 IG Section 2.64.1
     */
    case RESULTS_SECTION = '2.16.840.1.113883.10.20.22.2.3.1';

    /**
     * Encounters Section (entries optional).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.22.1
     * @see C-CDA 2.1 IG Section 2.16.1
     */
    case ENCOUNTERS_SECTION = '2.16.840.1.113883.10.20.22.2.22.1';

    /**
     * Immunizations Section (entries optional).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.2.1
     * @see C-CDA 2.1 IG Section 2.32.1
     */
    case IMMUNIZATIONS_SECTION = '2.16.840.1.113883.10.20.22.2.2.1';

    /**
     * Vital Signs Section (entries required).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.4.1
     * @see C-CDA 2.1 IG Section 2.70.1
     */
    case VITAL_SIGNS_SECTION = '2.16.840.1.113883.10.20.22.2.4.1';

    /**
     * Social History Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.17
     * @see C-CDA 2.1 IG Section 2.66
     */
    case SOCIAL_HISTORY_SECTION = '2.16.840.1.113883.10.20.22.2.17';

    /**
     * Payers Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.18
     * @see C-CDA 2.1 IG Section 2.45
     */
    case PAYERS_SECTION = '2.16.840.1.113883.10.20.22.2.18';

    /**
     * Plan of Treatment Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.10
     * @see C-CDA 2.1 IG Section 2.49
     */
    case PLAN_OF_CARE_SECTION = '2.16.840.1.113883.10.20.22.2.10';

    /**
     * Goals Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.60
     * @see C-CDA 2.1 IG Section 2.21
     */
    case GOALS_SECTION = '2.16.840.1.113883.10.20.22.2.60';

    /**
     * Health Concerns Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.58
     * @see C-CDA 2.1 IG Section 2.27
     */
    case HEALTH_CONCERNS_SECTION = '2.16.840.1.113883.10.20.22.2.58';

    /**
     * Care Team Section.
     * Note: This is a newer template added in C-CDA 2.1 Companion Guide.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.500
     * @see C-CDA Companion Guide Section 5.1
     */
    case CARE_TEAM_SECTION = '2.16.840.1.113883.10.20.22.2.500';

    /**
     * Medical Equipment Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.23
     * @see C-CDA 2.1 IG Section 2.36
     */
    case MEDICAL_EQUIPMENT_SECTION = '2.16.840.1.113883.10.20.22.2.23';

    /**
     * Functional Status Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.14
     * @see C-CDA 2.1 IG Section 2.18
     */
    case FUNCTIONAL_STATUS_SECTION = '2.16.840.1.113883.10.20.22.2.14';

    /**
     * Mental Status Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.56
     * @see C-CDA 2.1 IG Section 2.38
     */
    case MENTAL_STATUS_SECTION = '2.16.840.1.113883.10.20.22.2.56';

    /**
     * Advance Directives Section (entries required).
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.21.1
     * @see C-CDA 2.1 IG Section 2.1.1
     */
    case ADVANCE_DIRECTIVES_SECTION = '2.16.840.1.113883.10.20.22.2.21.1';

    /**
     * Reason for Referral Section.
     * Note: This uses an IHE PCC template ID, not a C-CDA template ID.
     *
     * @see https://wiki.ihe.net/index.php/PCC_TF_Vol2_Appendix_K IHE PCC Technical Framework
     */
    case REASON_FOR_REFERRAL_SECTION = '1.3.6.1.4.1.19376.1.5.3.1.3.1';

    /**
     * Notes Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.65
     * @see C-CDA 2.1 IG Section 2.43
     */
    case CLINICAL_NOTES_SECTION = '2.16.840.1.113883.10.20.22.2.65';

    /**
     * Assessment Section.
     *
     * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.2.8
     * @see C-CDA 2.1 IG Section 2.5
     */
    case ASSESSMENT_SECTION = '2.16.840.1.113883.10.20.22.2.8';
    
    // Entry-level templates
    case ALLERGY_PROBLEM_ACT = '2.16.840.1.113883.10.20.22.4.30';
    case ALLERGY_OBSERVATION = '2.16.840.1.113883.10.20.22.4.7';
    case ALLERGY_STATUS_OBSERVATION = '2.16.840.1.113883.10.20.22.4.28';
    case REACTION_OBSERVATION = '2.16.840.1.113883.10.20.22.4.9';
    case SEVERITY_OBSERVATION = '2.16.840.1.113883.10.20.22.4.8';
    
    case MEDICATION_ACTIVITY = '2.16.840.1.113883.10.20.22.4.16';
    case MEDICATION_SUPPLY_ORDER = '2.16.840.1.113883.10.20.22.4.17';
    case MEDICATION_DISPENSE = '2.16.840.1.113883.10.20.22.4.18';
    case MEDICATION_INFORMATION = '2.16.840.1.113883.10.20.22.4.23';
    case INSTRUCTIONS = '2.16.840.1.113883.10.20.22.4.20';
    
    case PROBLEM_CONCERN_ACT = '2.16.840.1.113883.10.20.22.4.3';
    case PROBLEM_OBSERVATION = '2.16.840.1.113883.10.20.22.4.4';
    case PROBLEM_STATUS = '2.16.840.1.113883.10.20.22.4.6';
    
    case PROCEDURE_ACTIVITY_ACT = '2.16.840.1.113883.10.20.22.4.12';
    case PROCEDURE_ACTIVITY_OBSERVATION = '2.16.840.1.113883.10.20.22.4.13';
    case PROCEDURE_ACTIVITY_PROCEDURE = '2.16.840.1.113883.10.20.22.4.14';
    
    case RESULT_ORGANIZER = '2.16.840.1.113883.10.20.22.4.1';
    case RESULT_OBSERVATION = '2.16.840.1.113883.10.20.22.4.2';
    
    case ENCOUNTER_ACTIVITY = '2.16.840.1.113883.10.20.22.4.49';
    case ENCOUNTER_DIAGNOSIS = '2.16.840.1.113883.10.20.22.4.80';
    
    case IMMUNIZATION_ACTIVITY = '2.16.840.1.113883.10.20.22.4.52';
    case IMMUNIZATION_REFUSAL_REASON = '2.16.840.1.113883.10.20.22.4.53';
    
    case VITAL_SIGNS_ORGANIZER = '2.16.840.1.113883.10.20.22.4.26';
    case VITAL_SIGN_OBSERVATION = '2.16.840.1.113883.10.20.22.4.27';
    
    case SOCIAL_HISTORY_OBSERVATION = '2.16.840.1.113883.10.20.22.4.38';
    case SMOKING_STATUS = '2.16.840.1.113883.10.20.22.4.78';
    case TOBACCO_USE = '2.16.840.1.113883.10.20.22.4.85';
    
    case COVERAGE_ACTIVITY = '2.16.840.1.113883.10.20.22.4.60';
    case POLICY_ACTIVITY = '2.16.840.1.113883.10.20.22.4.61';
    
    case PLANNED_ACT = '2.16.840.1.113883.10.20.22.4.39';
    case PLANNED_OBSERVATION = '2.16.840.1.113883.10.20.22.4.44';
    case PLANNED_PROCEDURE = '2.16.840.1.113883.10.20.22.4.41';
    case PLANNED_ENCOUNTER = '2.16.840.1.113883.10.20.22.4.40';
    case PLANNED_MEDICATION_ACTIVITY = '2.16.840.1.113883.10.20.22.4.42';
    
    case CARE_TEAM_ORGANIZER = '2.16.840.1.113883.10.20.22.4.500';
    case CARE_TEAM_MEMBER_ACT = '2.16.840.1.113883.10.20.22.4.500.1';
    
    case MEDICAL_EQUIPMENT_ORGANIZER = '2.16.840.1.113883.10.20.22.4.135';
    case NON_MEDICINAL_SUPPLY_ACTIVITY = '2.16.840.1.113883.10.20.22.4.50';
    
    case FUNCTIONAL_STATUS_OBSERVATION = '2.16.840.1.113883.10.20.22.4.67';
    case COGNITIVE_STATUS_OBSERVATION = '2.16.840.1.113883.10.20.22.4.68';
    
    case ADVANCE_DIRECTIVE_OBSERVATION = '2.16.840.1.113883.10.20.22.4.48';
    case ADVANCE_DIRECTIVE_ORGANIZER = '2.16.840.1.113883.10.20.22.4.108';
    
    case NOTE_ACTIVITY = '2.16.840.1.113883.10.20.22.4.202';
    
    case AUTHOR_PARTICIPATION = '2.16.840.1.113883.10.20.22.4.119';
    case INDICATION = '2.16.840.1.113883.10.20.22.4.19';

    /**
     * Get the extension date for a specific CCDA version.
     * Returns null if no extension applies.
     */
    public function extension(CcdaVersion $version): ?string
    {
        return match ([$this, $version]) {
            // US Realm Header
            [self::US_REALM_HEADER, CcdaVersion::V3_0] => '2023-05-01',
            [self::US_REALM_HEADER, CcdaVersion::V2_1] => '2015-08-01',
            
            // Section templates - most use 2015-08-01 for 2.1+
            [self::ALLERGIES_SECTION, CcdaVersion::V3_0],
            [self::ALLERGIES_SECTION, CcdaVersion::V2_1] => '2015-08-01',
            
            [self::MEDICATIONS_SECTION, CcdaVersion::V3_0],
            [self::MEDICATIONS_SECTION, CcdaVersion::V2_1] => '2014-06-09',
            
            [self::PROBLEMS_SECTION, CcdaVersion::V3_0],
            [self::PROBLEMS_SECTION, CcdaVersion::V2_1] => '2015-08-01',
            
            [self::PROCEDURES_SECTION, CcdaVersion::V3_0],
            [self::PROCEDURES_SECTION, CcdaVersion::V2_1] => '2014-06-09',
            
            [self::RESULTS_SECTION, CcdaVersion::V3_0],
            [self::RESULTS_SECTION, CcdaVersion::V2_1] => '2015-08-01',
            
            // Entry templates with extensions
            [self::ALLERGY_PROBLEM_ACT, CcdaVersion::V3_0],
            [self::ALLERGY_PROBLEM_ACT, CcdaVersion::V2_1] => '2015-08-01',
            
            [self::ALLERGY_OBSERVATION, CcdaVersion::V3_0],
            [self::ALLERGY_OBSERVATION, CcdaVersion::V2_1] => '2014-06-09',
            
            [self::MEDICATION_ACTIVITY, CcdaVersion::V3_0],
            [self::MEDICATION_ACTIVITY, CcdaVersion::V2_1] => '2014-06-09',
            
            [self::PROBLEM_CONCERN_ACT, CcdaVersion::V3_0],
            [self::PROBLEM_CONCERN_ACT, CcdaVersion::V2_1] => '2015-08-01',
            
            [self::PROBLEM_OBSERVATION, CcdaVersion::V3_0],
            [self::PROBLEM_OBSERVATION, CcdaVersion::V2_1] => '2015-08-01',
            
            default => null,
        };
    }
}
```

**Acceptance Criteria:**
- All template IDs from `sectionLevel2.js` and `entryLevel/*.js` are included
- Extensions are correct per CCDA 2.1 and 3.0 specs
- PHPStan passes

---

### Task 1.4: Create CCDA Version Enum

**File:** `src/Cda/Enum/CcdaVersion.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Enum;

enum CcdaVersion: string
{
    case V2_1 = '2.1';
    case V3_0 = '3.0';
    
    public function usRealmHeaderExtension(): string
    {
        return match ($this) {
            self::V2_1 => '2015-08-01',
            self::V3_0 => '2023-05-01',
        };
    }
}
```

---

### Task 1.5: Create Null Flavor Enum

**File:** `src/Cda/Enum/NullFlavor.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Enum;

enum NullFlavor: string
{
    case NO_INFORMATION = 'NI';
    case UNKNOWN = 'UNK';
    case ASKED_BUT_UNKNOWN = 'ASKU';
    case TEMPORARILY_UNAVAILABLE = 'NAV';
    case NOT_ASKED = 'NASK';
    case NOT_APPLICABLE = 'NA';
    case MASKED = 'MSK';
    case OTHER = 'OTH';
}
```

---

### Task 1.6: Create Time Precision Enum

**File:** `src/Cda/Enum/TimePrecision.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Enum;

enum TimePrecision: string
{
    case YEAR = 'year';
    case MONTH = 'month';
    case DAY = 'day';
    case HOUR = 'hour';
    case MINUTE = 'minute';
    case SECOND = 'second';
    case TIMEZONE = 'tz';

    public function formatString(): string
    {
        return match ($this) {
            self::YEAR => 'Y',
            self::MONTH => 'Ym',
            self::DAY => 'Ymd',
            self::HOUR => 'YmdH',
            self::MINUTE => 'YmdHi',
            self::SECOND => 'YmdHis',
            self::TIMEZONE => 'YmdHisO',
        };
    }
}
```

---

### Task 1.7: Create Core Value Objects

**File:** `src/Cda/ValueObject/Identifier.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

use OpenEMR\Cda\Enum\NullFlavor;

final readonly class Identifier
{
    public function __construct(
        public string $root,
        public string $extension = '',
        public ?NullFlavor $nullFlavor = null,
    ) {}

    public static function fromNpi(string $npi): self
    {
        return new self(
            root: '2.16.840.1.113883.4.6',
            extension: $npi,
        );
    }

    public static function unknown(): self
    {
        return new self(
            root: '',
            nullFlavor: NullFlavor::UNKNOWN,
        );
    }
}
```

**File:** `src/Cda/ValueObject/Code.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\Enum\NullFlavor;

final readonly class Code
{
    public function __construct(
        public string $code,
        public CodeSystem $codeSystem,
        public string $displayName = '',
        public ?NullFlavor $nullFlavor = null,
        public ?Code $translation = null,
    ) {}

    public static function unknown(): self
    {
        return new self(
            code: '',
            codeSystem: CodeSystem::SNOMED_CT,
            nullFlavor: NullFlavor::UNKNOWN,
        );
    }

    public function withTranslation(Code $translation): self
    {
        return new self(
            code: $this->code,
            codeSystem: $this->codeSystem,
            displayName: $this->displayName,
            nullFlavor: $this->nullFlavor,
            translation: $translation,
        );
    }
}
```

**File:** `src/Cda/ValueObject/EffectiveTime.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

use DateTimeImmutable;
use OpenEMR\Cda\Enum\NullFlavor;
use OpenEMR\Cda\Enum\TimePrecision;

final readonly class EffectiveTime
{
    public function __construct(
        public ?DateTimeImmutable $point = null,
        public ?DateTimeImmutable $low = null,
        public ?DateTimeImmutable $high = null,
        public TimePrecision $precision = TimePrecision::DAY,
        public ?NullFlavor $nullFlavor = null,
    ) {}

    public static function point(DateTimeImmutable $time, TimePrecision $precision = TimePrecision::DAY): self
    {
        return new self(point: $time, precision: $precision);
    }

    public static function range(
        ?DateTimeImmutable $low,
        ?DateTimeImmutable $high,
        TimePrecision $precision = TimePrecision::DAY,
    ): self {
        return new self(low: $low, high: $high, precision: $precision);
    }

    public static function unknown(): self
    {
        return new self(nullFlavor: NullFlavor::UNKNOWN);
    }
}
```

**File:** `src/Cda/ValueObject/Address.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

final readonly class Address
{
    /**
     * @param list<string> $streetLines
     */
    public function __construct(
        public array $streetLines = [],
        public string $city = '',
        public string $state = '',
        public string $postalCode = '',
        public string $country = 'US',
        public ?string $use = null,
    ) {}
}
```

**File:** `src/Cda/ValueObject/Telecom.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

final readonly class Telecom
{
    public function __construct(
        public string $value,
        public string $use = 'WP',
    ) {}

    public static function phone(string $number, string $use = 'WP'): self
    {
        $formatted = preg_replace('/[^0-9+]/', '', $number);
        return new self(value: "tel:{$formatted}", use: $use);
    }

    public static function email(string $email, string $use = 'WP'): self
    {
        return new self(value: "mailto:{$email}", use: $use);
    }
}
```

**File:** `src/Cda/ValueObject/PersonName.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

final readonly class PersonName
{
    public function __construct(
        public string $family,
        public string $given = '',
        public string $prefix = '',
        public string $suffix = '',
        public ?string $use = null,
    ) {}
}
```

**Acceptance Criteria:**
- All value objects are immutable (readonly)
- All have proper type declarations
- PHPStan passes at level 10
- Any hardcoded OIDs or identifiers include `@see` references to specifications

---

### Task 1.8: Create Author Value Object

**File:** `src/Cda/ValueObject/Author.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

use DateTimeImmutable;
use OpenEMR\Cda\Enum\CodeSystem;

final readonly class Author
{
    public function __construct(
        public Identifier $id,
        public PersonName $name,
        public ?DateTimeImmutable $time = null,
        public ?Code $code = null,
        public ?Address $address = null,
        public ?Telecom $telecom = null,
        public ?Organization $organization = null,
    ) {}

    public static function fromProviderData(array $data): self
    {
        $id = !empty($data['npi'])
            ? Identifier::fromNpi($data['npi'])
            : new Identifier(root: $data['id'] ?? '', extension: 'NI');

        return new self(
            id: $id,
            name: new PersonName(
                family: $data['lname'] ?? '',
                given: $data['fname'] ?? '',
            ),
            time: isset($data['time']) ? new DateTimeImmutable($data['time']) : null,
            code: isset($data['physician_type_code']) ? new Code(
                code: $data['physician_type_code'],
                codeSystem: CodeSystem::fromString($data['physician_type_system'] ?? '') ?? CodeSystem::SNOMED_CT,
                displayName: $data['physician_type'] ?? '',
            ) : null,
            organization: isset($data['facility_name']) ? new Organization(
                id: new Identifier(
                    root: $data['facility_oid'] ?? '2.16.840.1.113883.4.6',
                    extension: $data['facility_npi'] ?? 'NI',
                ),
                name: $data['facility_name'],
            ) : null,
        );
    }
}
```

**File:** `src/Cda/ValueObject/Organization.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\ValueObject;

final readonly class Organization
{
    /**
     * @param list<Telecom> $telecoms
     */
    public function __construct(
        public Identifier $id,
        public string $name,
        public ?Address $address = null,
        public array $telecoms = [],
    ) {}
}
```

---

## Phase 2: Section Models

### Task 2.1: Create Allergy Section Models

**File:** `src/Cda/Model/Section/Allergy/AllergySeverity.php`

```php
<?php

/**
 * Allergy severity codes from SNOMED CT.
 *
 * These codes are used in the Severity Observation template within
 * Allergy-Intolerance Observation entries.
 *
 * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.4.8
 *      C-CDA Severity Observation template
 * @see https://vsac.nlm.nih.gov/valueset/2.16.840.1.113883.3.88.12.3221.6.8/expansion
 *      Problem Severity value set (contains these codes)
 */

declare(strict_types=1);

namespace OpenEMR\Cda\Model\Section\Allergy;

enum AllergySeverity: string
{
    /**
     * Mild severity.
     *
     * @see https://browser.ihtsdotools.org/?perspective=full&conceptId1=255604002
     *      SNOMED CT: Mild (qualifier value)
     */
    case MILD = '255604002';

    /**
     * Moderate severity.
     *
     * @see https://browser.ihtsdotools.org/?perspective=full&conceptId1=6736007
     *      SNOMED CT: Moderate (severity modifier)
     */
    case MODERATE = '6736007';

    /**
     * Severe severity.
     *
     * @see https://browser.ihtsdotools.org/?perspective=full&conceptId1=24484000
     *      SNOMED CT: Severe (severity modifier)
     */
    case SEVERE = '24484000';

    public function displayName(): string
    {
        return match ($this) {
            self::MILD => 'Mild',
            self::MODERATE => 'Moderate',
            self::SEVERE => 'Severe',
        };
    }

    public static function fromCode(?string $code): ?self
    {
        if ($code === null || $code === '') {
            return null;
        }
        return self::tryFrom($code);
    }
}
```

**File:** `src/Cda/Model/Section/Allergy/AllergyStatus.php`

```php
<?php

/**
 * Allergy status codes from SNOMED CT.
 *
 * These codes are used in the Allergy Status Observation template to indicate
 * whether an allergy is currently active or has been resolved.
 *
 * @see https://www.hl7.org/ccdasearch/?templateId=2.16.840.1.113883.10.20.22.4.28
 *      C-CDA Allergy Status Observation template
 * @see https://vsac.nlm.nih.gov/valueset/2.16.840.1.113883.3.88.12.80.68/expansion
 *      Allergy/Adverse Event Status value set
 */

declare(strict_types=1);

namespace OpenEMR\Cda\Model\Section\Allergy;

enum AllergyStatus: string
{
    /**
     * Active status - the allergy is currently active.
     *
     * @see https://browser.ihtsdotools.org/?perspective=full&conceptId1=55561003
     *      SNOMED CT: Active (qualifier value)
     */
    case ACTIVE = '55561003';

    /**
     * Resolved status - the allergy has been resolved/is no longer active.
     *
     * @see https://browser.ihtsdotools.org/?perspective=full&conceptId1=73425007
     *      SNOMED CT: Inactive (qualifier value)
     */
    case RESOLVED = '73425007';

    public function displayName(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::RESOLVED => 'Resolved',
        };
    }

    public static function fromCode(?string $code): self
    {
        return match ($code) {
            '73425007' => self::RESOLVED,
            default => self::ACTIVE,
        };
    }
}
```

**File:** `src/Cda/Model/Section/Allergy/AllergyReaction.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Model\Section\Allergy;

use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\EffectiveTime;
use OpenEMR\Cda\ValueObject\Identifier;

final readonly class AllergyReaction
{
    public function __construct(
        public Identifier $id,
        public Code $code,
        public ?EffectiveTime $effectiveTime = null,
    ) {}
}
```

**File:** `src/Cda/Model/Section/Allergy/AllergyObservation.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Model\Section\Allergy;

use OpenEMR\Cda\ValueObject\Author;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\EffectiveTime;
use OpenEMR\Cda\ValueObject\Identifier;

final readonly class AllergyObservation
{
    /**
     * @param list<AllergyReaction> $reactions
     */
    public function __construct(
        public Identifier $id,
        public Code $allergen,
        public AllergyStatus $status,
        public EffectiveTime $effectiveTime,
        public ?AllergySeverity $severity = null,
        public array $reactions = [],
        public ?Author $author = null,
    ) {}
}
```

**File:** `src/Cda/Model/Section/Allergy/AllergiesSection.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Model\Section\Allergy;

final readonly class AllergiesSection
{
    /**
     * @param list<AllergyObservation> $allergies
     */
    public function __construct(
        public array $allergies = [],
        public bool $noKnownAllergies = false,
    ) {}

    public function isEmpty(): bool
    {
        return $this->allergies === [] && !$this->noKnownAllergies;
    }
}
```

**Acceptance Criteria:**
- All models are readonly
- All arrays use proper PHPDoc types
- PHPStan passes
- All enum cases (status codes, severity codes, etc.) have `@see` links to SNOMED CT browser or value set definitions
- Any template-specific identifiers reference the C-CDA template they correspond to

---

### Task 2.2 - 2.17: Create Remaining Section Models

Create similar model structures for each section. Each section needs:

1. **Section container class** (e.g., `MedicationsSection`)
2. **Entry/Observation class** (e.g., `MedicationActivity`)
3. **Status enum** if applicable
4. **Any section-specific value enums**

**Sections to implement:**

| Task | Section | Main Model Class | Key Properties |
|------|---------|------------------|----------------|
| 2.2 | Medications | `MedicationActivity` | drug, rxnorm, dose, route, frequency, startDate, endDate, status, author |
| 2.3 | Problems | `ProblemObservation` | code, status, effectiveTime, author |
| 2.4 | Procedures | `ProcedureActivity` | code, status, effectiveTime, performer, targetSiteCode |
| 2.5 | Immunizations | `ImmunizationActivity` | vaccineCode, administeredDate, lotNumber, manufacturer, performer |
| 2.6 | Vitals | `VitalSignsOrganizer`, `VitalSignObservation` | effectiveTime, observations (BP, temp, height, weight, etc.) |
| 2.7 | Results | `ResultOrganizer`, `ResultObservation` | code, effectiveTime, observations (value, unit, referenceRange) |
| 2.8 | Encounters | `EncounterActivity` | code, effectiveTime, performer, diagnoses |
| 2.9 | Social History | `SocialHistoryObservation`, `SmokingStatus` | code, value, effectiveTime |
| 2.10 | Care Team | `CareTeamOrganizer`, `CareTeamMember` | members, status, effectiveTime |
| 2.11 | Payers | `CoverageActivity`, `PolicyActivity` | payerId, coverageType, effectiveTime |
| 2.12 | Medical Devices | `MedicalEquipment` | code, status, effectiveTime |
| 2.13 | Advance Directives | `AdvanceDirectiveObservation` | code, effectiveTime, documentReference |
| 2.14 | Plan of Care | `PlannedActivity` | code, moodCode, effectiveTime |
| 2.15 | Functional Status | `FunctionalStatusObservation` | code, value, effectiveTime |
| 2.16 | Referrals | `ReferralNote` | reason, effectiveTime |
| 2.17 | Clinical Notes | `NoteActivity` | noteType, text, effectiveTime, author |

For each, follow the pattern established in Task 2.1.

---

### Task 2.18: Create Document Header Models

**File:** `src/Cda/Model/Header/PatientRole.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Model\Header;

use DateTimeImmutable;
use OpenEMR\Cda\ValueObject\Address;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\Identifier;
use OpenEMR\Cda\ValueObject\PersonName;
use OpenEMR\Cda\ValueObject\Telecom;

final readonly class PatientRole
{
    /**
     * @param list<Identifier> $ids
     * @param list<Address> $addresses
     * @param list<Telecom> $telecoms
     * @param list<PersonName> $names
     */
    public function __construct(
        public array $ids,
        public array $names,
        public ?Code $administrativeGenderCode = null,
        public ?DateTimeImmutable $birthTime = null,
        public ?Code $maritalStatusCode = null,
        public ?Code $religiousAffiliationCode = null,
        public ?Code $raceCode = null,
        public ?Code $ethnicGroupCode = null,
        public ?string $languageCode = null,
        public array $addresses = [],
        public array $telecoms = [],
        public ?Organization $providerOrganization = null,
    ) {}
}
```

Create similar models for:
- `DocumentAuthor`
- `Custodian`
- `InformationRecipient`
- `LegalAuthenticator`
- `Authenticator`
- `Participant`
- `DocumentationOf`
- `ServiceEvent`

**File:** `src/Cda/Model/Document/CcdaDocument.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Model\Document;

use DateTimeImmutable;
use OpenEMR\Cda\Enum\CcdaVersion;
use OpenEMR\Cda\Model\Header\Custodian;
use OpenEMR\Cda\Model\Header\DocumentAuthor;
use OpenEMR\Cda\Model\Header\InformationRecipient;
use OpenEMR\Cda\Model\Header\LegalAuthenticator;
use OpenEMR\Cda\Model\Header\PatientRole;
use OpenEMR\Cda\Model\Section\Allergy\AllergiesSection;
use OpenEMR\Cda\Model\Section\AdvanceDirective\AdvanceDirectivesSection;
use OpenEMR\Cda\Model\Section\CareTeam\CareTeamSection;
use OpenEMR\Cda\Model\Section\ClinicalNote\ClinicalNotesSection;
use OpenEMR\Cda\Model\Section\Encounter\EncountersSection;
use OpenEMR\Cda\Model\Section\FunctionalStatus\FunctionalStatusSection;
use OpenEMR\Cda\Model\Section\Immunization\ImmunizationsSection;
use OpenEMR\Cda\Model\Section\MedicalDevice\MedicalEquipmentSection;
use OpenEMR\Cda\Model\Section\Medication\MedicationsSection;
use OpenEMR\Cda\Model\Section\Payer\PayersSection;
use OpenEMR\Cda\Model\Section\PlanOfCare\PlanOfCareSection;
use OpenEMR\Cda\Model\Section\Problem\ProblemsSection;
use OpenEMR\Cda\Model\Section\Procedure\ProceduresSection;
use OpenEMR\Cda\Model\Section\Referral\ReferralSection;
use OpenEMR\Cda\Model\Section\Result\ResultsSection;
use OpenEMR\Cda\Model\Section\SocialHistory\SocialHistorySection;
use OpenEMR\Cda\Model\Section\Vital\VitalSignsSection;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\Identifier;

final readonly class CcdaDocument
{
    /**
     * @param list<DocumentAuthor> $authors
     * @param list<InformationRecipient> $informationRecipients
     */
    public function __construct(
        // Required header elements
        public Identifier $id,
        public Code $code,
        public string $title,
        public DateTimeImmutable $effectiveTime,
        public Code $confidentialityCode,
        public PatientRole $recordTarget,
        public array $authors,
        public Custodian $custodian,

        // Optional header elements
        public CcdaVersion $version = CcdaVersion::V3_0,
        public ?LegalAuthenticator $legalAuthenticator = null,
        public array $informationRecipients = [],
        public ?string $languageCode = 'en-US',
        public ?Identifier $setId = null,
        public ?int $versionNumber = null,

        // Sections
        public ?AllergiesSection $allergies = null,
        public ?MedicationsSection $medications = null,
        public ?ProblemsSection $problems = null,
        public ?ProceduresSection $procedures = null,
        public ?ResultsSection $results = null,
        public ?EncountersSection $encounters = null,
        public ?ImmunizationsSection $immunizations = null,
        public ?VitalSignsSection $vitalSigns = null,
        public ?SocialHistorySection $socialHistory = null,
        public ?CareTeamSection $careTeam = null,
        public ?PayersSection $payers = null,
        public ?MedicalEquipmentSection $medicalEquipment = null,
        public ?AdvanceDirectivesSection $advanceDirectives = null,
        public ?PlanOfCareSection $planOfCare = null,
        public ?FunctionalStatusSection $functionalStatus = null,
        public ?ReferralSection $referral = null,
        public ?ClinicalNotesSection $clinicalNotes = null,
    ) {}
}
```

---

## Phase 3: Hydrators

### Task 3.1: Create Base Hydrator Infrastructure

**File:** `src/Cda/Hydrator/HydratorInterface.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Hydrator;

interface HydratorInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function hydrate(array $data): mixed;
}
```

**File:** `src/Cda/Hydrator/AbstractHydrator.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Hydrator;

use DateTimeImmutable;
use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\ValueObject\Author;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\EffectiveTime;
use OpenEMR\Cda\ValueObject\Identifier;

abstract class AbstractHydrator implements HydratorInterface
{
    protected function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '' || $value === '00000000') {
            return null;
        }

        // Handle various date formats from OpenEMR
        $formats = ['YmdHisO', 'YmdHis', 'Ymd', 'Y-m-d H:i:s', 'Y-m-d'];
        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            if ($date !== false) {
                return $date;
            }
        }

        return null;
    }

    protected function cleanCode(?string $code): string
    {
        if ($code === null) {
            return '';
        }
        return trim(preg_replace('/[^a-zA-Z0-9.]/', '', $code) ?? '');
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function hydrateAuthor(array $data): ?Author
    {
        if (empty($data)) {
            return null;
        }
        return Author::fromProviderData($data);
    }

    /**
     * @param array<string, mixed> $row
     */
    protected function hydrateCodeFromRow(
        array $row,
        string $codeKey,
        string $codeSystemKey,
        string $displayNameKey,
    ): Code {
        $codeSystemName = $row[$codeSystemKey] ?? '';
        $codeSystem = CodeSystem::fromString($codeSystemName) ?? CodeSystem::SNOMED_CT;

        return new Code(
            code: $this->cleanCode($row[$codeKey] ?? ''),
            codeSystem: $codeSystem,
            displayName: $row[$displayNameKey] ?? '',
        );
    }

    protected function hydrateIdentifier(?string $root, ?string $extension): Identifier
    {
        return new Identifier(
            root: $root ?? '',
            extension: $extension ?? '',
        );
    }

    protected function hydrateEffectiveTime(?string $low, ?string $high): EffectiveTime
    {
        return EffectiveTime::range(
            low: $this->parseDate($low),
            high: $this->parseDate($high),
        );
    }
}
```

---

### Task 3.2: Create Allergies Hydrator

**File:** `src/Cda/Hydrator/AllergiesHydrator.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Hydrator;

use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\Model\Section\Allergy\AllergiesSection;
use OpenEMR\Cda\Model\Section\Allergy\AllergyObservation;
use OpenEMR\Cda\Model\Section\Allergy\AllergyReaction;
use OpenEMR\Cda\Model\Section\Allergy\AllergySeverity;
use OpenEMR\Cda\Model\Section\Allergy\AllergyStatus;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\Identifier;

final class AllergiesHydrator extends AbstractHydrator
{
    /**
     * Default SHA-1 based UUID for allergy entries when none is provided.
     *
     * This is a legacy identifier format used for backwards compatibility with
     * existing CCDA documents. New implementations should generate proper UUIDs.
     *
     * @see RFC 4122 Section 4.3 - UUID version 1 (time-based)
     *      This appears to be a pre-generated v1 UUID used as a default root.
     */
    private const DEFAULT_SHA_ID = '36e3e930-7b14-11db-9fe1-0800200c9a66';

    /**
     * Default identifier root for reaction observations.
     *
     * Similar to DEFAULT_SHA_ID, this is a legacy v1 UUID used for reaction
     * entry identifiers when no specific ID is available.
     */
    private const REACTION_ID = '4adc1020-7b14-11db-9fe1-0800200c9a64';

    /**
     * @param array<int, array<string, mixed>> $data
     */
    public function hydrate(array $data): AllergiesSection
    {
        if ($data === []) {
            return new AllergiesSection(noKnownAllergies: true);
        }

        $allergies = [];
        foreach ($data as $row) {
            $allergies[] = $this->hydrateOne($row);
        }

        return new AllergiesSection(allergies: $allergies);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateOne(array $row): AllergyObservation
    {
        return new AllergyObservation(
            id: new Identifier(
                root: $row['sha_id'] ?? self::DEFAULT_SHA_ID,
                extension: (string) ($row['id'] ?? ''),
            ),
            allergen: $this->hydrateAllergenCode($row),
            status: AllergyStatus::fromCode($row['status_code'] ?? null),
            effectiveTime: $this->hydrateEffectiveTime(
                $row['begdate'] ?? $row['startdate'] ?? null,
                $row['enddate'] ?? null,
            ),
            severity: AllergySeverity::fromCode($row['outcome_code'] ?? null),
            reactions: $this->hydrateReactions($row),
            author: $this->hydrateAuthor($row['author'] ?? []),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateAllergenCode(array $row): Code
    {
        // Prefer RXNORM, fall back to SNOMED
        if (!empty($row['rxnorm_code'])) {
            return new Code(
                code: $this->cleanCode($row['rxnorm_code']),
                codeSystem: CodeSystem::RXNORM,
                displayName: $row['rxnorm_code_text'] ?? $row['title'] ?? '',
            );
        }

        if (!empty($row['snomed_code'])) {
            return new Code(
                code: $this->cleanCode($row['snomed_code']),
                codeSystem: CodeSystem::SNOMED_CT,
                displayName: $row['snomed_code_text'] ?? $row['title'] ?? '',
            );
        }

        // No coded value, use title as display
        return new Code(
            code: '',
            codeSystem: CodeSystem::RXNORM,
            displayName: $row['title'] ?? '',
        );
    }

    /**
     * @param array<string, mixed> $row
     * @return list<AllergyReaction>
     */
    private function hydrateReactions(array $row): array
    {
        if (empty($row['reaction_code'])) {
            return [];
        }

        return [
            new AllergyReaction(
                id: new Identifier(root: self::REACTION_ID),
                code: new Code(
                    code: $this->cleanCode($row['reaction_code']),
                    codeSystem: CodeSystem::SNOMED_CT,
                    displayName: $row['reaction_text'] ?? '',
                ),
                effectiveTime: $this->hydrateEffectiveTime(
                    $row['begdate'] ?? $row['startdate'] ?? null,
                    $row['enddate'] ?? null,
                ),
            ),
        ];
    }
}
```

---

### Task 3.3 - 3.18: Create Remaining Hydrators

Create hydrators for each section following the pattern in Task 3.2:

| Task | Hydrator | Input Source (EncounterccdadispatchTable method) |
|------|----------|--------------------------------------------------|
| 3.3 | `MedicationsHydrator` | `getMedications()` |
| 3.4 | `ProblemsHydrator` | `getProblemList()` |
| 3.5 | `ProceduresHydrator` | `getProcedures()` |
| 3.6 | `ImmunizationsHydrator` | `getImmunization()` |
| 3.7 | `VitalSignsHydrator` | `getVitals()` |
| 3.8 | `ResultsHydrator` | `getResults()` |
| 3.9 | `EncountersHydrator` | `getEncounterHistory()` |
| 3.10 | `SocialHistoryHydrator` | `getSocialHistory()` |
| 3.11 | `CareTeamHydrator` | `getPatientCareTeam()` |
| 3.12 | `PayersHydrator` | `getPayers()` |
| 3.13 | `MedicalEquipmentHydrator` | `getMedicalDeviceList()` |
| 3.14 | `AdvanceDirectivesHydrator` | `getAdvanceDirectives()` |
| 3.15 | `PlanOfCareHydrator` | `getPlanOfCare()` |
| 3.16 | `FunctionalStatusHydrator` | `getFunctionalCognitiveStatus()` |
| 3.17 | `ReferralHydrator` | `getReferrals()` |
| 3.18 | `ClinicalNotesHydrator` | `getClinicalNotes()` |

Each hydrator must:
1. Accept array data from the corresponding `get*()` method
2. Return the appropriate section model
3. Handle null/empty values gracefully
4. Parse dates using `parseDate()` from AbstractHydrator
5. Clean codes using `cleanCode()` from AbstractHydrator

---

### Task 3.19: Create Header Hydrators

**File:** `src/Cda/Hydrator/PatientRoleHydrator.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Hydrator;

use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\Model\Header\PatientRole;
use OpenEMR\Cda\ValueObject\Address;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\Identifier;
use OpenEMR\Cda\ValueObject\PersonName;
use OpenEMR\Cda\ValueObject\Telecom;

final class PatientRoleHydrator extends AbstractHydrator
{
    /**
     * @param array<string, mixed> $data
     */
    public function hydrate(array $data): PatientRole
    {
        $ids = [];
        if (!empty($data['uuid'])) {
            $ids[] = new Identifier(
                root: $data['facility_oid'] ?? '2.16.840.1.113883.19.5.99999.1',
                extension: $data['uuid'],
            );
        }

        return new PatientRole(
            ids: $ids,
            names: [
                new PersonName(
                    family: $data['lname'] ?? '',
                    given: $data['fname'] ?? '',
                    prefix: $data['prefix'] ?? '',
                    suffix: $data['suffix'] ?? '',
                ),
            ],
            administrativeGenderCode: $this->hydrateGender($data['sex'] ?? null),
            birthTime: $this->parseDate($data['dob'] ?? $data['DOB'] ?? null),
            maritalStatusCode: $this->hydrateMaritalStatus($data['status'] ?? null),
            raceCode: $this->hydrateRace($data['race'] ?? null),
            ethnicGroupCode: $this->hydrateEthnicity($data['ethnicity'] ?? null),
            languageCode: $data['language'] ?? null,
            addresses: $this->hydrateAddresses($data),
            telecoms: $this->hydrateTelecoms($data),
        );
    }

    private function hydrateGender(?string $sex): ?Code
    {
        if ($sex === null) {
            return null;
        }

        $code = match (strtoupper($sex)) {
            'MALE', 'M' => 'M',
            'FEMALE', 'F' => 'F',
            default => 'UN',
        };

        return new Code(
            code: $code,
            codeSystem: CodeSystem::HL7_ADMINISTRATIVE_GENDER,
            displayName: match ($code) {
                'M' => 'Male',
                'F' => 'Female',
                default => 'Undifferentiated',
            },
        );
    }

    // Add remaining hydrate methods for marital status, race, ethnicity, addresses, telecoms
}
```

Create similar hydrators for other header elements.

---

### Task 3.20: Create Document Hydrator

**File:** `src/Cda/Hydrator/CcdaDocumentHydrator.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Hydrator;

use DateTimeImmutable;
use OpenEMR\Cda\Enum\CcdaVersion;
use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\Model\Document\CcdaDocument;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\Identifier;

final class CcdaDocumentHydrator extends AbstractHydrator
{
    public function __construct(
        private readonly PatientRoleHydrator $patientRoleHydrator,
        private readonly AllergiesHydrator $allergiesHydrator,
        private readonly MedicationsHydrator $medicationsHydrator,
        private readonly ProblemsHydrator $problemsHydrator,
        private readonly ProceduresHydrator $proceduresHydrator,
        private readonly ImmunizationsHydrator $immunizationsHydrator,
        private readonly VitalSignsHydrator $vitalSignsHydrator,
        private readonly ResultsHydrator $resultsHydrator,
        private readonly EncountersHydrator $encountersHydrator,
        private readonly SocialHistoryHydrator $socialHistoryHydrator,
        private readonly CareTeamHydrator $careTeamHydrator,
        private readonly PayersHydrator $payersHydrator,
        private readonly MedicalEquipmentHydrator $medicalEquipmentHydrator,
        private readonly AdvanceDirectivesHydrator $advanceDirectivesHydrator,
        private readonly PlanOfCareHydrator $planOfCareHydrator,
        private readonly FunctionalStatusHydrator $functionalStatusHydrator,
        private readonly ClinicalNotesHydrator $clinicalNotesHydrator,
        // Add remaining hydrators
    ) {}

    /**
     * @param array<string, mixed> $data Complete data array from data provider
     */
    public function hydrate(array $data): CcdaDocument
    {
        return new CcdaDocument(
            id: new Identifier(
                root: $data['document_oid'] ?? '2.16.840.1.113883.19.5.99999.1',
                extension: $data['document_id'] ?? 'OE-DOC-' . bin2hex(random_bytes(4)),
            ),
            code: new Code(
                code: '34133-9',
                codeSystem: CodeSystem::LOINC,
                displayName: 'Summarization of Episode Note',
            ),
            title: $data['title'] ?? 'Summarization of Episode Note',
            effectiveTime: new DateTimeImmutable(),
            confidentialityCode: new Code(
                code: 'N',
                codeSystem: CodeSystem::HL7_CONFIDENTIALITY,
                displayName: 'Normal',
            ),
            recordTarget: $this->patientRoleHydrator->hydrate($data['patient'] ?? []),
            authors: $this->hydrateAuthors($data['authors'] ?? []),
            custodian: $this->hydrateCustodian($data['custodian'] ?? []),
            version: CcdaVersion::V3_0,

            // Sections
            allergies: isset($data['allergies'])
                ? $this->allergiesHydrator->hydrate($data['allergies'])
                : null,
            medications: isset($data['medications'])
                ? $this->medicationsHydrator->hydrate($data['medications'])
                : null,
            problems: isset($data['problems'])
                ? $this->problemsHydrator->hydrate($data['problems'])
                : null,
            procedures: isset($data['procedures'])
                ? $this->proceduresHydrator->hydrate($data['procedures'])
                : null,
            immunizations: isset($data['immunizations'])
                ? $this->immunizationsHydrator->hydrate($data['immunizations'])
                : null,
            vitalSigns: isset($data['vitals'])
                ? $this->vitalSignsHydrator->hydrate($data['vitals'])
                : null,
            results: isset($data['results'])
                ? $this->resultsHydrator->hydrate($data['results'])
                : null,
            encounters: isset($data['encounters'])
                ? $this->encountersHydrator->hydrate($data['encounters'])
                : null,
            socialHistory: isset($data['social_history'])
                ? $this->socialHistoryHydrator->hydrate($data['social_history'])
                : null,
            careTeam: isset($data['care_team'])
                ? $this->careTeamHydrator->hydrate($data['care_team'])
                : null,
            payers: isset($data['payers'])
                ? $this->payersHydrator->hydrate($data['payers'])
                : null,
            medicalEquipment: isset($data['medical_devices'])
                ? $this->medicalEquipmentHydrator->hydrate($data['medical_devices'])
                : null,
            advanceDirectives: isset($data['advance_directives'])
                ? $this->advanceDirectivesHydrator->hydrate($data['advance_directives'])
                : null,
            planOfCare: isset($data['plan_of_care'])
                ? $this->planOfCareHydrator->hydrate($data['plan_of_care'])
                : null,
            functionalStatus: isset($data['functional_status'])
                ? $this->functionalStatusHydrator->hydrate($data['functional_status'])
                : null,
            clinicalNotes: isset($data['clinical_notes'])
                ? $this->clinicalNotesHydrator->hydrate($data['clinical_notes'])
                : null,
        );
    }
}
```

---

## Phase 4: Renderer

### Task 4.1: Create Renderer Base Infrastructure

**File:** `src/Cda/Renderer/CdaRenderer.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Renderer;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use OpenEMR\Cda\Enum\CcdaTemplateId;
use OpenEMR\Cda\Enum\CcdaVersion;
use OpenEMR\Cda\Enum\TimePrecision;
use OpenEMR\Cda\Model\Document\CcdaDocument;
use OpenEMR\Cda\ValueObject\Author;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\EffectiveTime;
use OpenEMR\Cda\ValueObject\Identifier;

final class CdaRenderer
{
    private const NS_CDA = 'urn:hl7-org:v3';
    private const NS_SDTC = 'urn:hl7-org:sdtc';
    private const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    private DOMDocument $doc;
    private CcdaVersion $version;

    public function render(CcdaDocument $document): DOMDocument
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $this->version = $document->version;

        // Add XML stylesheet processing instruction
        $xsl = $this->doc->createProcessingInstruction(
            'xml-stylesheet',
            'type="text/xsl" href="CDA.xsl"'
        );
        $this->doc->appendChild($xsl);

        // Create root element
        $root = $this->createRootElement();
        $this->doc->appendChild($root);

        // Render header
        $this->renderHeader($root, $document);

        // Render body
        $this->renderBody($root, $document);

        return $this->doc;
    }

    private function createRootElement(): DOMElement
    {
        $root = $this->doc->createElementNS(self::NS_CDA, 'ClinicalDocument');
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            self::NS_XSI
        );
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:voc',
            'urn:hl7-org:v3/voc'
        );
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:sdtc',
            self::NS_SDTC
        );

        return $root;
    }

    private function renderHeader(DOMElement $root, CcdaDocument $document): void
    {
        // realmCode
        $realmCode = $this->createElement('realmCode');
        $realmCode->setAttribute('code', 'US');
        $root->appendChild($realmCode);

        // typeId
        $typeId = $this->createElement('typeId');
        $typeId->setAttribute('root', '2.16.840.1.113883.1.3');
        $typeId->setAttribute('extension', 'POCD_HD000040');
        $root->appendChild($typeId);

        // templateIds
        $this->appendTemplateId($root, CcdaTemplateId::US_REALM_HEADER);
        $this->appendTemplateId($root, CcdaTemplateId::CCD);

        // id
        $root->appendChild($this->renderIdentifier($document->id));

        // code
        $root->appendChild($this->renderCode($document->code));

        // title
        $title = $this->createElement('title', $document->title);
        $root->appendChild($title);

        // effectiveTime
        $root->appendChild($this->renderEffectiveTimePoint(
            $document->effectiveTime,
            TimePrecision::TIMEZONE
        ));

        // confidentialityCode
        $root->appendChild($this->renderCode($document->confidentialityCode, 'confidentialityCode'));

        // languageCode
        if ($document->languageCode !== null) {
            $languageCode = $this->createElement('languageCode');
            $languageCode->setAttribute('code', $document->languageCode);
            $root->appendChild($languageCode);
        }

        // setId
        if ($document->setId !== null) {
            $root->appendChild($this->renderIdentifier($document->setId, 'setId'));
        }

        // versionNumber
        if ($document->versionNumber !== null) {
            $versionNumber = $this->createElement('versionNumber');
            $versionNumber->setAttribute('value', (string) $document->versionNumber);
            $root->appendChild($versionNumber);
        }

        // recordTarget
        $this->renderRecordTarget($root, $document);

        // authors
        foreach ($document->authors as $author) {
            $this->renderAuthor($root, $author);
        }

        // custodian
        $this->renderCustodian($root, $document);

        // informationRecipients
        foreach ($document->informationRecipients as $recipient) {
            $this->renderInformationRecipient($root, $recipient);
        }

        // legalAuthenticator
        if ($document->legalAuthenticator !== null) {
            $this->renderLegalAuthenticator($root, $document->legalAuthenticator);
        }
    }

    private function renderBody(DOMElement $root, CcdaDocument $document): void
    {
        $component = $this->createElement('component');
        $structuredBody = $this->createElement('structuredBody');

        // Render each section if present
        if ($document->allergies !== null && !$document->allergies->isEmpty()) {
            $structuredBody->appendChild($this->renderAllergiesSection($document->allergies));
        }

        if ($document->medications !== null) {
            $structuredBody->appendChild($this->renderMedicationsSection($document->medications));
        }

        if ($document->problems !== null) {
            $structuredBody->appendChild($this->renderProblemsSection($document->problems));
        }

        // Continue for all sections...

        $component->appendChild($structuredBody);
        $root->appendChild($component);
    }

    // Helper methods

    private function createElement(string $name, ?string $text = null): DOMElement
    {
        $element = $this->doc->createElement($name);
        if ($text !== null) {
            $element->textContent = $text;
        }
        return $element;
    }

    private function appendTemplateId(DOMElement $parent, CcdaTemplateId $template): void
    {
        $extension = $template->extension($this->version);

        // With extension
        if ($extension !== null) {
            $templateId = $this->createElement('templateId');
            $templateId->setAttribute('root', $template->value);
            $templateId->setAttribute('extension', $extension);
            $parent->appendChild($templateId);
        }

        // Without extension (for backwards compatibility)
        $templateId = $this->createElement('templateId');
        $templateId->setAttribute('root', $template->value);
        $parent->appendChild($templateId);
    }

    private function renderIdentifier(Identifier $id, string $elementName = 'id'): DOMElement
    {
        $element = $this->createElement($elementName);

        if ($id->nullFlavor !== null) {
            $element->setAttribute('nullFlavor', $id->nullFlavor->value);
        } else {
            $element->setAttribute('root', $id->root);
            if ($id->extension !== '') {
                $element->setAttribute('extension', $id->extension);
            }
        }

        return $element;
    }

    private function renderCode(Code $code, string $elementName = 'code'): DOMElement
    {
        $element = $this->createElement($elementName);

        if ($code->nullFlavor !== null) {
            $element->setAttribute('nullFlavor', $code->nullFlavor->value);
        } else {
            $element->setAttribute('code', $code->code);
            $element->setAttribute('codeSystem', $code->codeSystem->value);
            $element->setAttribute('codeSystemName', $code->codeSystem->displayName());
            if ($code->displayName !== '') {
                $element->setAttribute('displayName', $code->displayName);
            }
        }

        if ($code->translation !== null) {
            $element->appendChild($this->renderCode($code->translation, 'translation'));
        }

        return $element;
    }

    private function renderEffectiveTime(EffectiveTime $time): DOMElement
    {
        $element = $this->createElement('effectiveTime');

        if ($time->nullFlavor !== null) {
            $element->setAttribute('nullFlavor', $time->nullFlavor->value);
            return $element;
        }

        if ($time->point !== null) {
            $element->setAttribute('value', $this->formatDateTime($time->point, $time->precision));
            return $element;
        }

        if ($time->low !== null) {
            $low = $this->createElement('low');
            $low->setAttribute('value', $this->formatDateTime($time->low, $time->precision));
            $element->appendChild($low);
        }

        if ($time->high !== null) {
            $high = $this->createElement('high');
            $high->setAttribute('value', $this->formatDateTime($time->high, $time->precision));
            $element->appendChild($high);
        }

        return $element;
    }

    private function renderEffectiveTimePoint(
        DateTimeImmutable $time,
        TimePrecision $precision = TimePrecision::DAY,
    ): DOMElement {
        $element = $this->createElement('effectiveTime');
        $element->setAttribute('value', $this->formatDateTime($time, $precision));
        return $element;
    }

    private function formatDateTime(DateTimeImmutable $time, TimePrecision $precision): string
    {
        return $time->format($precision->formatString());
    }

    // Section rendering methods will be added in subsequent tasks
}
```

---

### Task 4.2: Create Allergies Section Renderer

Add to `CdaRenderer.php`:

```php
private function renderAllergiesSection(AllergiesSection $section): DOMElement
{
    $component = $this->createElement('component');
    $sectionEl = $this->createElement('section');

    // Template IDs
    $this->appendTemplateId($sectionEl, CcdaTemplateId::ALLERGIES_SECTION);

    // Section code
    $sectionEl->appendChild($this->renderCode(new Code(
        code: '48765-2',
        codeSystem: CodeSystem::LOINC,
        displayName: 'Allergies and adverse reactions Document',
    )));

    // Title
    $sectionEl->appendChild($this->createElement('title', 'ALLERGIES AND ADVERSE REACTIONS'));

    // Narrative text
    $sectionEl->appendChild($this->renderAllergiesNarrative($section));

    // Entries
    if ($section->noKnownAllergies) {
        $sectionEl->appendChild($this->renderNoKnownAllergiesEntry());
    } else {
        foreach ($section->allergies as $allergy) {
            $sectionEl->appendChild($this->renderAllergyEntry($allergy));
        }
    }

    $component->appendChild($sectionEl);
    return $component;
}

private function renderAllergyEntry(AllergyObservation $allergy): DOMElement
{
    $entry = $this->createElement('entry');
    $entry->setAttribute('typeCode', 'DRIV');

    $act = $this->createElement('act');
    $act->setAttribute('classCode', 'ACT');
    $act->setAttribute('moodCode', 'EVN');

    // Template ID
    $this->appendTemplateId($act, CcdaTemplateId::ALLERGY_PROBLEM_ACT);

    // Identifiers
    $act->appendChild($this->renderIdentifier($allergy->id));

    // Code
    $act->appendChild($this->renderCode(new Code(
        code: 'CONC',
        codeSystem: CodeSystem::HL7_ACT_CODE,
        displayName: 'Concern',
    )));

    // Status
    $statusCode = $this->createElement('statusCode');
    $statusCode->setAttribute('code', 'active');
    $act->appendChild($statusCode);

    // Effective time
    $act->appendChild($this->renderEffectiveTime($allergy->effectiveTime));

    // Author
    if ($allergy->author !== null) {
        $act->appendChild($this->renderAuthorParticipation($allergy->author));
    }

    // Entry relationship with observation
    $entryRel = $this->createElement('entryRelationship');
    $entryRel->setAttribute('typeCode', 'SUBJ');
    $entryRel->appendChild($this->renderAllergyObservation($allergy));
    $act->appendChild($entryRel);

    $entry->appendChild($act);
    return $entry;
}

private function renderAllergyObservation(AllergyObservation $allergy): DOMElement
{
    $observation = $this->createElement('observation');
    $observation->setAttribute('classCode', 'OBS');
    $observation->setAttribute('moodCode', 'EVN');

    // Template ID
    $this->appendTemplateId($observation, CcdaTemplateId::ALLERGY_OBSERVATION);

    // ... continue with observation content
    // Include allergen, severity, status, reactions

    return $observation;
}

private function renderAllergiesNarrative(AllergiesSection $section): DOMElement
{
    $text = $this->createElement('text');

    if ($section->noKnownAllergies) {
        $text->textContent = 'No known allergies';
        return $text;
    }

    $table = $this->createElement('table');
    $table->setAttribute('border', '1');
    $table->setAttribute('width', '100%');

    // Header row
    $thead = $this->createElement('thead');
    $tr = $this->createElement('tr');
    foreach (['Substance', 'Reaction', 'Severity', 'Status'] as $header) {
        $th = $this->createElement('th', $header);
        $tr->appendChild($th);
    }
    $thead->appendChild($tr);
    $table->appendChild($thead);

    // Body rows
    $tbody = $this->createElement('tbody');
    foreach ($section->allergies as $allergy) {
        $tr = $this->createElement('tr');

        $tr->appendChild($this->createElement('td', $allergy->allergen->displayName));
        $tr->appendChild($this->createElement('td',
            $allergy->reactions[0]->code->displayName ?? ''
        ));
        $tr->appendChild($this->createElement('td',
            $allergy->severity?->displayName() ?? ''
        ));
        $tr->appendChild($this->createElement('td',
            $allergy->status->displayName()
        ));

        $tbody->appendChild($tr);
    }
    $table->appendChild($tbody);
    $text->appendChild($table);

    return $text;
}
```

---

### Task 4.3 - 4.18: Create Remaining Section Renderers

For each section, add methods to `CdaRenderer.php` following the pattern:
1. `render{Section}Section()` — wrapper with component/section structure
2. `render{Entry}Entry()` — individual entry rendering
3. `render{Section}Narrative()` — human-readable table

| Task | Section | Methods to Add |
|------|---------|----------------|
| 4.3 | Medications | `renderMedicationsSection`, `renderMedicationEntry`, `renderMedicationActivity` |
| 4.4 | Problems | `renderProblemsSection`, `renderProblemEntry`, `renderProblemObservation` |
| 4.5 | Procedures | `renderProceduresSection`, `renderProcedureEntry` |
| 4.6 | Immunizations | `renderImmunizationsSection`, `renderImmunizationEntry` |
| 4.7 | Vitals | `renderVitalSignsSection`, `renderVitalSignsOrganizer`, `renderVitalSignObservation` |
| 4.8 | Results | `renderResultsSection`, `renderResultOrganizer`, `renderResultObservation` |
| 4.9 | Encounters | `renderEncountersSection`, `renderEncounterEntry` |
| 4.10 | Social History | `renderSocialHistorySection`, `renderSocialHistoryObservation`, `renderSmokingStatus` |
| 4.11 | Care Team | `renderCareTeamSection`, `renderCareTeamOrganizer` |
| 4.12 | Payers | `renderPayersSection`, `renderCoverageActivity` |
| 4.13 | Medical Devices | `renderMedicalEquipmentSection`, `renderDeviceEntry` |
| 4.14 | Advance Directives | `renderAdvanceDirectivesSection`, `renderAdvanceDirectiveObservation` |
| 4.15 | Plan of Care | `renderPlanOfCareSection`, `renderPlannedActivity` |
| 4.16 | Functional Status | `renderFunctionalStatusSection`, `renderFunctionalStatusObservation` |
| 4.17 | Clinical Notes | `renderClinicalNotesSection`, `renderNoteActivity` |
| 4.18 | Referral | `renderReferralSection` |

Reference the Node.js `serveccda.js` `populate*` functions and `oe-blue-button-generate/lib/entryLevel/*.js` for exact structure.

---

## Phase 5: Data Provider Refactoring

### Task 5.1: Create CcdaDataProvider Interface

**File:** `src/Cda/Service/CcdaDataProviderInterface.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Service;

interface CcdaDataProviderInterface
{
    /**
     * Get all data needed for CCDA generation.
     *
     * @return array<string, mixed> Keyed by section name
     */
    public function getPatientData(int $patientId, ?int $encounterId = null): array;
}
```

---

### Task 5.2: Create CcdaDataProvider Implementation

**File:** `src/Cda/Service/CcdaDataProvider.php`

This class wraps `EncounterccdadispatchTable` and converts the XML string methods to return arrays.

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Service;

use Carecoordination\Model\EncounterccdadispatchTable;

final class CcdaDataProvider implements CcdaDataProviderInterface
{
    public function __construct(
        private readonly EncounterccdadispatchTable $dispatchTable,
    ) {}

    public function getPatientData(int $patientId, ?int $encounterId = null): array
    {
        // For each section, we need to either:
        // 1. Modify EncounterccdadispatchTable to return arrays (preferred)
        // 2. Or parse the XML strings back to arrays (temporary workaround)

        return [
            'patient' => $this->getPatientDemographics($patientId),
            'authors' => $this->getAuthors($patientId, $encounterId),
            'custodian' => $this->getCustodian($patientId, $encounterId),
            'allergies' => $this->getAllergies($patientId),
            'medications' => $this->getMedications($patientId),
            'problems' => $this->getProblems($patientId),
            'procedures' => $this->getProcedures($patientId, $encounterId),
            'immunizations' => $this->getImmunizations($patientId),
            'vitals' => $this->getVitals($patientId),
            'results' => $this->getResults($patientId, $encounterId),
            'encounters' => $this->getEncounters($patientId),
            'social_history' => $this->getSocialHistory($patientId),
            'care_team' => $this->getCareTeam($patientId, $encounterId),
            'payers' => $this->getPayers($patientId),
            'medical_devices' => $this->getMedicalDevices($patientId),
            'advance_directives' => $this->getAdvanceDirectives($patientId),
            'plan_of_care' => $this->getPlanOfCare($patientId, $encounterId),
            'functional_status' => $this->getFunctionalStatus($patientId),
            'clinical_notes' => $this->getClinicalNotes($patientId, $encounterId),
        ];
    }

    // Each method will either call refactored EncounterccdadispatchTable methods
    // or implement temporary XML parsing as a bridge
}
```

---

### Task 5.3: Refactor EncounterccdadispatchTable Methods

For each `get*()` method in `EncounterccdadispatchTable`, create a parallel method that returns array data instead of XML string.

**Example refactoring for `getAllergies()`:**

Original method returns: `"<allergies>...</allergies>"`

Create new method:

```php
/**
 * @return list<array{
 *   id: string,
 *   sha_id: string,
 *   title: string,
 *   rxnorm_code: string,
 *   rxnorm_code_text: string,
 *   snomed_code: string,
 *   snomed_code_text: string,
 *   status_code: string,
 *   status_table: string,
 *   outcome: string,
 *   outcome_code: string,
 *   begdate: string,
 *   enddate: string,
 *   reaction_text: string,
 *   reaction_code: string,
 *   author: array{...},
 * }>
 */
public function getAllergiesArray(int $pid): array
{
    // Same query logic as getAllergies()
    // But return array instead of building XML string
}
```

This is a significant refactoring task. For each of the ~40 `get*()` methods:
1. Create `get*Array()` variant
2. Extract query logic to shared private method
3. Original method calls shared method and formats as XML (for backwards compatibility during transition)
4. New method calls shared method and returns array

---

## Phase 6: Service Integration

### Task 6.1: Create CcdaService

**File:** `src/Cda/Service/CcdaService.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Service;

use OpenEMR\Cda\Hydrator\CcdaDocumentHydrator;
use OpenEMR\Cda\Renderer\CdaRenderer;

final class CcdaService
{
    public function __construct(
        private readonly CcdaDataProviderInterface $dataProvider,
        private readonly CcdaDocumentHydrator $hydrator,
        private readonly CdaRenderer $renderer,
    ) {}

    /**
     * Generate CCDA XML for a patient.
     */
    public function generateXml(int $patientId, ?int $encounterId = null): string
    {
        // 1. Fetch data
        $data = $this->dataProvider->getPatientData($patientId, $encounterId);

        // 2. Hydrate to model
        $document = $this->hydrator->hydrate($data);

        // 3. Render to XML
        $dom = $this->renderer->render($document);

        return $dom->saveXML();
    }

    /**
     * Generate CCDA as formatted HTML (for viewing).
     */
    public function generateHtml(int $patientId, ?int $encounterId = null): string
    {
        $xml = $this->generateXml($patientId, $encounterId);

        // Transform using XSL
        // (reuse existing XSL transformation logic from CDADocumentService)

        return $this->transformToHtml($xml);
    }
}
```

---

### Task 6.2: Create Service Factory

**File:** `src/Cda/Service/CcdaServiceFactory.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Cda\Service;

use Carecoordination\Model\EncounterccdadispatchTable;
use OpenEMR\Cda\Hydrator\AllergiesHydrator;
use OpenEMR\Cda\Hydrator\CcdaDocumentHydrator;
// ... all hydrators
use OpenEMR\Cda\Renderer\CdaRenderer;

final class CcdaServiceFactory
{
    public static function create(EncounterccdadispatchTable $dispatchTable): CcdaService
    {
        $dataProvider = new CcdaDataProvider($dispatchTable);

        $hydrator = new CcdaDocumentHydrator(
            patientRoleHydrator: new PatientRoleHydrator(),
            allergiesHydrator: new AllergiesHydrator(),
            medicationsHydrator: new MedicationsHydrator(),
            // ... all hydrators
        );

        $renderer = new CdaRenderer();

        return new CcdaService($dataProvider, $hydrator, $renderer);
    }
}
```

---

## Phase 7: Testing

### Task 7.1: Create Value Object Tests

**File:** `tests/Tests/Isolated/Cda/ValueObject/CodeTest.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\ValueObject;

use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\Enum\NullFlavor;
use OpenEMR\Cda\ValueObject\Code;
use PHPUnit\Framework\TestCase;

final class CodeTest extends TestCase
{
    public function testConstructWithAllFields(): void
    {
        $code = new Code(
            code: '12345',
            codeSystem: CodeSystem::SNOMED_CT,
            displayName: 'Test Code',
        );

        self::assertSame('12345', $code->code);
        self::assertSame(CodeSystem::SNOMED_CT, $code->codeSystem);
        self::assertSame('Test Code', $code->displayName);
        self::assertNull($code->nullFlavor);
    }

    public function testUnknownCreatesNullFlavorCode(): void
    {
        $code = Code::unknown();

        self::assertSame(NullFlavor::UNKNOWN, $code->nullFlavor);
    }

    public function testWithTranslationCreatesNewInstance(): void
    {
        $original = new Code('123', CodeSystem::SNOMED_CT, 'Original');
        $translation = new Code('456', CodeSystem::ICD10_CM, 'Translated');

        $withTranslation = $original->withTranslation($translation);

        self::assertNotSame($original, $withTranslation);
        self::assertSame($translation, $withTranslation->translation);
        self::assertNull($original->translation);
    }
}
```

Create similar tests for all value objects.

---

### Task 7.2: Create Enum Tests

**File:** `tests/Tests/Isolated/Cda/Enum/CodeSystemTest.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Enum;

use OpenEMR\Cda\Enum\CodeSystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CodeSystemTest extends TestCase
{
    #[DataProvider('displayNameProvider')]
    public function testDisplayName(CodeSystem $codeSystem, string $expected): void
    {
        self::assertSame($expected, $codeSystem->displayName());
    }

    /**
     * @return iterable<string, array{CodeSystem, string}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function displayNameProvider(): iterable
    {
        yield 'SNOMED CT' => [CodeSystem::SNOMED_CT, 'SNOMED CT'];
        yield 'RXNORM' => [CodeSystem::RXNORM, 'RxNorm'];
        yield 'LOINC' => [CodeSystem::LOINC, 'LOINC'];
    }

    #[DataProvider('fromStringProvider')]
    public function testFromString(string $input, ?CodeSystem $expected): void
    {
        self::assertSame($expected, CodeSystem::fromString($input));
    }

    /**
     * @return iterable<string, array{string, ?CodeSystem}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fromStringProvider(): iterable
    {
        yield 'SNOMED' => ['SNOMED', CodeSystem::SNOMED_CT];
        yield 'SNOMED-CT' => ['SNOMED-CT', CodeSystem::SNOMED_CT];
        yield 'snomed_ct lowercase' => ['snomed_ct', CodeSystem::SNOMED_CT];
        yield 'RXNORM' => ['RXNORM', CodeSystem::RXNORM];
        yield 'RXCUI' => ['RXCUI', CodeSystem::RXNORM];
        yield 'unknown' => ['UNKNOWN_SYSTEM', null];
    }
}
```

---

### Task 7.3: Create Hydrator Tests

**File:** `tests/Tests/Isolated/Cda/Hydrator/AllergiesHydratorTest.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Hydrator;

use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\Hydrator\AllergiesHydrator;
use OpenEMR\Cda\Model\Section\Allergy\AllergiesSection;
use OpenEMR\Cda\Model\Section\Allergy\AllergySeverity;
use OpenEMR\Cda\Model\Section\Allergy\AllergyStatus;
use PHPUnit\Framework\TestCase;

final class AllergiesHydratorTest extends TestCase
{
    private AllergiesHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new AllergiesHydrator();
    }

    public function testHydrateEmptyArrayReturnsNoKnownAllergies(): void
    {
        $result = $this->hydrator->hydrate([]);

        self::assertInstanceOf(AllergiesSection::class, $result);
        self::assertTrue($result->noKnownAllergies);
        self::assertSame([], $result->allergies);
    }

    public function testHydrateWithRxnormCode(): void
    {
        $data = [
            [
                'id' => '123',
                'sha_id' => 'test-sha-id',
                'title' => 'Penicillin',
                'rxnorm_code' => '7984',
                'rxnorm_code_text' => 'Penicillin V',
                'status_code' => '55561003',
                'outcome_code' => '24484000',
                'begdate' => '20240115',
                'enddate' => '',
            ],
        ];

        $result = $this->hydrator->hydrate($data);

        self::assertFalse($result->noKnownAllergies);
        self::assertCount(1, $result->allergies);

        $allergy = $result->allergies[0];
        self::assertSame('test-sha-id', $allergy->id->root);
        self::assertSame('123', $allergy->id->extension);
        self::assertSame('7984', $allergy->allergen->code);
        self::assertSame(CodeSystem::RXNORM, $allergy->allergen->codeSystem);
        self::assertSame('Penicillin V', $allergy->allergen->displayName);
        self::assertSame(AllergyStatus::ACTIVE, $allergy->status);
        self::assertSame(AllergySeverity::SEVERE, $allergy->severity);
    }

    public function testHydrateWithSnomedFallback(): void
    {
        $data = [
            [
                'id' => '456',
                'title' => 'Latex',
                'snomed_code' => '111088007',
                'snomed_code_text' => 'Latex allergy',
                'status_code' => '73425007',
                'begdate' => '20230601',
            ],
        ];

        $result = $this->hydrator->hydrate($data);

        $allergy = $result->allergies[0];
        self::assertSame('111088007', $allergy->allergen->code);
        self::assertSame(CodeSystem::SNOMED_CT, $allergy->allergen->codeSystem);
        self::assertSame(AllergyStatus::RESOLVED, $allergy->status);
    }
}
```

Create similar tests for all hydrators.

---

### Task 7.4: Create Renderer Tests

**File:** `tests/Tests/Isolated/Cda/Renderer/CdaRendererAllergiesTest.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Renderer;

use DateTimeImmutable;
use DOMDocument;
use DOMXPath;
use OpenEMR\Cda\Enum\CodeSystem;
use OpenEMR\Cda\Model\Section\Allergy\AllergiesSection;
use OpenEMR\Cda\Model\Section\Allergy\AllergyObservation;
use OpenEMR\Cda\Model\Section\Allergy\AllergySeverity;
use OpenEMR\Cda\Model\Section\Allergy\AllergyStatus;
use OpenEMR\Cda\Renderer\CdaRenderer;
use OpenEMR\Cda\ValueObject\Code;
use OpenEMR\Cda\ValueObject\EffectiveTime;
use OpenEMR\Cda\ValueObject\Identifier;
use PHPUnit\Framework\TestCase;

final class CdaRendererAllergiesTest extends TestCase
{
    private CdaRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new CdaRenderer();
    }

    public function testRenderAllergySection(): void
    {
        $allergy = new AllergyObservation(
            id: new Identifier('2.16.840.1.113883.19', 'allergy-1'),
            allergen: new Code('7984', CodeSystem::RXNORM, 'Penicillin V'),
            status: AllergyStatus::ACTIVE,
            effectiveTime: EffectiveTime::range(
                low: new DateTimeImmutable('2024-01-15'),
                high: null,
            ),
            severity: AllergySeverity::SEVERE,
        );

        $section = new AllergiesSection(allergies: [$allergy]);

        // Create minimal document for testing
        $document = $this->createTestDocument($section);
        $dom = $this->renderer->render($document);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('cda', 'urn:hl7-org:v3');

        // Verify section exists
        $sections = $xpath->query('//cda:section[cda:code[@code="48765-2"]]');
        self::assertSame(1, $sections->length, 'Expected one allergies section');

        // Verify entry exists
        $entries = $xpath->query('//cda:section/cda:entry/cda:act', $sections->item(0));
        self::assertSame(1, $entries->length, 'Expected one allergy entry');

        // Verify template ID
        $templateIds = $xpath->query('.//cda:templateId[@root="2.16.840.1.113883.10.20.22.4.30"]', $entries->item(0));
        self::assertGreaterThan(0, $templateIds->length, 'Expected allergy problem act template ID');
    }
}
```

---

### Task 7.5: Create Parity Tests

**File:** `tests/Tests/Services/Cda/CcdaParityTest.php`

This is the critical test that compares new PHP output to existing Node output.

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Cda;

use DOMDocument;
use DOMXPath;
use OpenEMR\Cda\Service\CcdaService;
use OpenEMR\Cda\Service\CcdaServiceFactory;
use Carecoordination\Model\CcdaServiceDocumentRequestor;
use Carecoordination\Model\EncounterccdadispatchTable;
use PHPUnit\Framework\TestCase;

/**
 * Compares output of new PHP CCDA generator against existing Node.js generator.
 * Both must produce identical output (after normalizing timestamps/IDs).
 */
final class CcdaParityTest extends TestCase
{
    private const EXAMPLE_INPUT = __DIR__ . '/../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/ccda-example-input1.xml';

    private CcdaService $phpService;
    private CcdaServiceDocumentRequestor $nodeService;

    protected function setUp(): void
    {
        // Initialize services
        // This requires the test database to be populated
    }

    public function testAllergyOutputMatchesNodeOutput(): void
    {
        $patientId = 1; // Test patient

        // Generate with PHP
        $phpOutput = $this->phpService->generateXml($patientId);

        // Generate with Node (existing method)
        $nodeOutput = $this->generateViaNode($patientId);

        // Normalize both
        $phpNormalized = $this->normalizeXml($phpOutput);
        $nodeNormalized = $this->normalizeXml($nodeOutput);

        // Compare
        self::assertXmlStringEqualsXmlString(
            $nodeNormalized,
            $phpNormalized,
            'PHP output must match Node output'
        );
    }

    private function normalizeXml(string $xml): string
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        // Replace timestamps with fixed value
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('cda', 'urn:hl7-org:v3');

        // Normalize effectiveTime values
        $timeNodes = $xpath->query('//*[@value]');
        foreach ($timeNodes as $node) {
            $value = $node->getAttribute('value');
            if (preg_match('/^\d{8,14}/', $value)) {
                $node->setAttribute('value', '20240101120000-0500');
            }
        }

        // Normalize generated IDs
        // ... similar to existing test normalization

        $dom->formatOutput = false;
        return $dom->C14N();
    }
}
```

---

### Task 7.6: Create Schema Validation Tests

**File:** `tests/Tests/Services/Cda/CcdaSchemaValidationTest.php`

```php
<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Cda;

use DOMDocument;
use OpenEMR\Cda\Service\CcdaService;
use PHPUnit\Framework\TestCase;

final class CcdaSchemaValidationTest extends TestCase
{
    private const CDA_SCHEMA_PATH = __DIR__ . '/../../data/schemas/CDA_SDTC.xsd';

    public function testGeneratedCcdaIsValidAgainstSchema(): void
    {
        if (!file_exists(self::CDA_SCHEMA_PATH)) {
            self::markTestSkipped('CDA schema not available. Download from HL7/CDA-core-2.0');
        }

        $service = $this->createService();
        $xml = $service->generateXml(patientId: 1);

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        libxml_use_internal_errors(true);
        $isValid = $dom->schemaValidate(self::CDA_SCHEMA_PATH);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        self::assertTrue($isValid, 'CCDA must validate against CDA schema. Errors: ' . $this->formatErrors($errors));
    }

    /**
     * @param list<\LibXMLError> $errors
     */
    private function formatErrors(array $errors): string
    {
        return implode("\n", array_map(
            fn(\LibXMLError $e) => sprintf('[%d:%d] %s', $e->line, $e->column, trim($e->message)),
            $errors
        ));
    }
}
```

---

### Task 7.7: Add CI Configuration

Update `.github/workflows/test.yml` to include CCDA tests:

```yaml
# Add to existing test job
- name: Run CCDA Parity Tests
  run: |
    docker compose exec -T openemr ./vendor/bin/phpunit \
      --configuration phpunit.xml \
      --testsuite ccda \
      --coverage-clover coverage-ccda.xml
```

Add testsuite to `phpunit.xml`:

```xml
<testsuite name="ccda">
    <directory>tests/Tests/Isolated/Cda</directory>
    <directory>tests/Tests/Services/Cda</directory>
</testsuite>
```

---

## Phase 8: Migration & Cleanup

### Task 8.1: Update CDADocumentService

Modify `src/Services/CDADocumentService.php` to use the new PHP service instead of calling Node.

```php
// Before (calls Node):
public function generateCCDXml($pid): string
{
    $dispatchTable = new EncounterccdadispatchTable();
    $ccdaGenerator = new CcdaGenerator($dispatchTable);
    $result = $ccdaGenerator->generate(...);
    return $result->getContent();
}

// After (pure PHP):
public function generateCCDXml($pid): string
{
    $service = CcdaServiceFactory::create($this->dispatchTable);
    return $service->generateXml((int) $pid);
}
```

---

### Task 8.2: Update ccda_gateway.php

Modify `ccdaservice/ccda_gateway.php` to use the new service.

Ensure all entry points (`dl`, `view`, `report_ccd_download`, `report_ccd_view`) work with the new implementation.

---

### Task 8.3: Remove Node.js Dependencies

Once all tests pass with the PHP implementation:

1. **Delete Node.js service files:**
   ```
   rm -rf ccdaservice/serveccda.js
   rm -rf ccdaservice/oe-blue-button-generate/
   rm -rf ccdaservice/oe-blue-button-meta/
   rm -rf ccdaservice/oe-blue-button-util/
   rm -rf ccdaservice/utils/
   rm -rf ccdaservice/data-stack/
   rm -rf ccdaservice/packages/
   rm -rf ccdaservice/node_modules/
   rm ccdaservice/package.json
   rm ccdaservice/package-lock.json
   ```

2. **Keep for documentation:**
   ```
   ccdaservice/README.md (update to note PHP migration)
   ```

3. **Remove socket communication code:**
   - Delete `CcdaServiceDocumentRequestor.php`
   - Remove socket-related code from `CcdaGenerator.php`

4. **Update CI workflows:**
   - Remove Node.js setup steps for CCDA
   - Remove CCDA service cache keys
   - Update test workflow to not start Node service

---

### Task 8.4: Remove Legacy XML Generation

Remove XML string generation from `EncounterccdadispatchTable`:

1. Delete the original `get*()` methods that return XML strings (after confirming no other code uses them)
2. Rename `get*Array()` methods to `get*()`
3. Delete `CcdaServiceRequestModelGenerator.php`

---

### Task 8.5: Update Documentation

1. **Update `ccdaservice/README.md`:**
   - Document the PHP-only architecture
   - Remove Node.js setup instructions
   - Note the migration date

2. **Update `CLAUDE.md`:**
   - Add section about `src/Cda/` architecture
   - Document the model → hydrator → renderer pattern

3. **Update `CONTRIBUTING.md`** if it references Node.js CCDA service

---

### Task 8.6: Final Verification

1. Run full test suite: `openemr-cmd clean-sweep-tests`
2. Run PHPStan: `composer phpstan`
3. Run PHPCS: `composer phpcs`
4. Generate CCDA for test patient and verify:
   - Opens correctly in CCDA viewer
   - Passes healthit.gov validator
5. Test import of generated CCDA (round-trip)

---

## Appendix A: Section Details

### A.1: Complete Field Mapping Reference

For each section, this maps:
- OpenEMR database fields
- Current Node.js field names
- New PHP model properties

*(This section would contain detailed field-by-field mappings for each of the 17 sections. Due to length, abbreviated here.)*

### A.2: Template ID Reference

Complete list of all CCDA template IDs used, their extensions for each version, and where they appear in the document structure.

**Authoritative Source:** https://www.hl7.org/ccdasearch/

### A.3: Code System OID Reference

Complete list of all code system OIDs used in OpenEMR CCDA generation.

**Authoritative Source:** https://www.hl7.org/oid/index.cfm

### A.4: Finding Code Values in Specifications

When implementing clinical code values (e.g., status codes, severity codes), use these resources:

| Resource | URL | Use Case |
|----------|-----|----------|
| SNOMED CT Browser | https://browser.ihtsdotools.org/ | Look up SNOMED codes by concept ID |
| VSAC (Value Set Authority Center) | https://vsac.nlm.nih.gov/ | Find C-CDA required value sets |
| LOINC Search | https://loinc.org/search/ | Look up LOINC codes |
| RxNav | https://mor.nlm.nih.gov/RxNav/ | Look up RxNorm drug codes |
| CVX Codes | https://www2.cdc.gov/vaccines/iis/iisstandards/vaccines.asp?rpt=cvx | Vaccine codes |

**Value Set OIDs in C-CDA:**

C-CDA specifies which value sets are required for each coded element. These are documented in the template definitions and can be looked up in VSAC. For example:

- Problem Severity: `2.16.840.1.113883.3.88.12.3221.6.8`
- Allergy/Adverse Event Type: `2.16.840.1.113883.3.88.12.3221.6.2`
- Medication Route: `2.16.840.1.113883.3.88.12.3221.8.7`

Always verify code values against the appropriate value set in VSAC before hardcoding them.

---

## Appendix B: Estimated Timeline

| Phase | Tasks | Estimated Effort |
|-------|-------|------------------|
| 1. Foundation | 1.1-1.8 | 2-3 days |
| 2. Section Models | 2.1-2.18 | 3-4 days |
| 3. Hydrators | 3.1-3.20 | 4-5 days |
| 4. Renderer | 4.1-4.18 | 5-7 days |
| 5. Data Provider | 5.1-5.3 | 3-4 days |
| 6. Integration | 6.1-6.2 | 1-2 days |
| 7. Testing | 7.1-7.7 | 4-5 days |
| 8. Migration | 8.1-8.6 | 2-3 days |

**Total: ~25-35 days**

---

## Appendix C: Risk Mitigation

1. **Output Parity Risk:** Run parity tests continuously during development
2. **Schema Compliance Risk:** Validate against XSD early and often
3. **Performance Risk:** Benchmark against Node implementation
4. **Data Mapping Risk:** Test with real patient data in staging environment
5. **Rollback Plan:** Keep Node service functional until PHP is fully validated
