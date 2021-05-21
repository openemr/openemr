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

class FhirCodeSystemUris
{
    const SNOMED_CT = "http://snomed.info/sct";
    const NUCC_PROVIDER = "http://nucc.org/provider-taxonomy";
    const DATA_ABSENT_REASON = "http://terminology.hl7.org/CodeSystem/data-absent-reason";

    // @see http://hl7.org/fhir/R4/valueset-immunization-status-reason.html
    const IMMUNIZATION_STATUS_REASON = "http://hl7.org/fhir/ValueSet/immunization-status-reason";

    const IMMUNIZATION_OBJECTION_REASON = "http://terminology.hl7.org/CodeSystem/v3-ActReason";
    const IMMUNIZATION_UNIT_AMOUNT = "http://unitsofmeasure.org";
}
