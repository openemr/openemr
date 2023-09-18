<?php

/**
 * FhirPractitionerSerializer.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Serialization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;

class FhirMedicationRequestSerializer
{
    public static function serialize(FHIRMedicationRequest $object)
    {
        return $object->jsonSerialize();
    }

    // TODO: @adunsulag this is very painful to hydrate our objects.  It would be better if we used something like
    // the symfony serializer @see https://symfony.com/doc/current/components/serializer.html#deserializing-in-an-existing-object
    // If we want to start type safing things a LOT more and working with objects instead of arrays we could include that
    // library, but for this one off... perhaps we don't need it so much.  Once we start dealing with POST/PUT its going
    // to become a much bigger deal to go from JSON to type safed objects especially in terms of error elimination...
    /**
     * Takes a fhir json representing an organization and returns the populated the resource
     * @param $fhirJson
     * @return FHIRMedicationRequest
     */
    public static function deserialize($fhirJson)
    {
        return new FHIRMedicationRequest($fhirJson);
    }
}
