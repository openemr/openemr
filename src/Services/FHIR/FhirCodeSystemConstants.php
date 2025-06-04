<?php

/**
 * FhirCodeSystemUris.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

class FhirCodeSystemConstants
{
    const SNOMED_CT = "http://snomed.info/sct";
    const NUCC_PROVIDER = "http://nucc.org/provider-taxonomy";
    const DATA_ABSENT_REASON_EXTENSION = "http://hl7.org/fhir/StructureDefinition/data-absent-reason";
    const DATA_ABSENT_REASON_CODE_SYSTEM = "http://terminology.hl7.org/CodeSystem/data-absent-reason";

    // @see http://hl7.org/fhir/R4/valueset-immunization-status-reason.html
    const IMMUNIZATION_STATUS_REASON = "http://hl7.org/fhir/ValueSet/immunization-status-reason";

    const IMMUNIZATION_OBJECTION_REASON = "http://terminology.hl7.org/CodeSystem/v3-ActReason";
    const UNITS_OF_MEASURE = "http://unitsofmeasure.org";

    const PROVIDER_NPI = "http://hl7.org/fhir/sid/us-npi";

    const HL7_SYSTEM_CAREPLAN_CATEGORY = "http://hl7.org/fhir/us/core/CodeSystem/careplan-category";

    const LOINC = "http://loinc.org";

    const HL7_OBSERVATION_CATEGORY = "http://terminology.hl7.org/CodeSystem/observation-category";

    // @see https://www.hl7.org/fhir/us/core/ValueSet-us-core-documentreference-category.html
    const DOCUMENT_REFERENCE_CATEGORY = "http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category";

    // @see https://terminology.hl7.org/1.0.0//CodeSystem-v3-NullFlavor.html
    const HL7_NULL_FLAVOR = "http://terminology.hl7.org/CodeSystem/v3-NullFlavor";

    // @see http://hl7.org/fhir/R4/valueset-formatcodes.html
    // @see https://profiles.ihe.net/fhir/ihe.formatcode.fhir/background.html
    const IHE_FORMATCODE_CODESYSTEM = "http://ihe.net/fhir/ValueSet/IHE.FormatCode.codesystem";

    const DIAGNOSTIC_SERVICE_SECTION_ID = "http://terminology.hl7.org/CodeSystem/v2-0074";

    // @see http://oid-info.com/get/2.16.840.1.113883.4.7
    const OID_CLINICAL_LABORATORY_IMPROVEMENT_ACT_NUMBER = "urn:oid:2.16.840.1.113883.4.7";

    const HL7_IDENTIFIER_TYPE_TABLE = "http://hl7.org/fhir/v2/0203";

    const HL7_ORGANIZATION_TYPE = "http://terminology.hl7.org/CodeSystem/organization-type";

    // @see http://hl7.org/fhir/R4/valueset-observation-interpretation.html
    const HL7_V3_OBSERVATION_INTERPRETATION = "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation";

    const HL7_ICD10 = "http://hl7.org/fhir/sid/icd-10";

    public const HL7_V3_ACT_CODE = "http://terminology.hl7.org/CodeSystem/v3-ActCode";

    public const HL7_PARTICIPATION_TYPE = "http://terminology.hl7.org/CodeSystem/v3-ParticipationType";

    public const RFC_3986 = "urn:ietf:rfc:3986";

    const HL7_DISCHARGE_DISPOSITION = "http://terminology.hl7.org/CodeSystem/discharge-disposition";

    const RXNORM = "http://www.nlm.nih.gov/research/umls/rxnorm";

    const HL7_MEDICATION_REQUEST_CATEGORY = "http://terminology.hl7.org/CodeSystem/medicationrequest-category";
    public const NCIMETA_NCI_NIH = "http://ncimeta.nci.nih.gov";

    const OID_RACE_AND_ETHNICITY = "urn:oid:2.16.840.1.113883.6.238";

    const HL7_US_CORE_RACE = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-race";

    const LANGUAGE_BCP_47 = "urn:ietf:bcp:47";

    /**
     * Required for US Core CareTeam Role.  Requires UMLS subscription to view valueset
     * available here: https://vsac.nlm.nih.gov/valueset/2.16.840.1.113762.1.4.1099.27/expansion/Latest
     */
    const CARE_TEAM_MEMBER_FUNCTION_SNOMEDCT = "2.16.840.1.113762.1.4.1099.27";

    /**
     * Required for Structured Data Collection (SDC) Task implementations
     * @see https://build.fhir.org/ig/HL7/sdc/ValueSet-task-code.html
     */
    const HL7_SDC_TASK_TEMP = "https://build.fhir.org/ig/HL7/sdc/CodeSystem-temp.html";

    const HL7_SDC_TASK_SERVICE_REQUEST = "http://hl7.org/fhir/CodeSystem/task-code";
}
