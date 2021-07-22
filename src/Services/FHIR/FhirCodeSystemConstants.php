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

    public const HL7_SYSTEM_CAREPLAN_CATEGORY = "http://hl7.org/fhir/us/core/CodeSystem/careplan-category";

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
}
