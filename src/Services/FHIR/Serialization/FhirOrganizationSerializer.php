<?php

/**
 * FhirOrganizationSerializer.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Serialization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;

class FhirOrganizationSerializer
{
    public static function serialize(FHIROrganization $organization)
    {
        return $organization->jsonSerialize();
    }

    // TODO: @adunsulag this is very painful to hydrate our objects.  It would be better if we used something like
    // the symfony serializer @see https://symfony.com/doc/current/components/serializer.html#deserializing-in-an-existing-object
    // If we want to start type safing things a LOT more and working with objects instead of arrays we could include that
    // library, but for this one off... perhaps we don't need it so much.  Once we start dealing with POST/PUT its going
    // to become a much bigger deal to go from JSON to type safed objects especially in terms of error elimination...
    /**
     * Takes a fhir json representing an organization and returns the populated FHIROrganization resource
     * @param $fhirJson
     * @return FHIROrganization
     */
    public static function deserialize($fhirJson)
    {
        $telecom = $fhirJson['telecom'] ?? [];
        $address = $fhirJson['address'] ?? [];
        $identifiers = $fhirJson['identifier'] ?? [];
        unset($fhirJson['telecom']);
        unset($fhirJson['address']);
        unset($fhirJson['identifier']);

        $organization = new FHIROrganization($fhirJson);
        foreach ($telecom as $item) {
            $obj = new FHIRContactPoint($item);
            if (!empty($item['period'])) {
                $obj->setPeriod(new FHIRPeriod($item['period']));
            }
            $organization->addTelecom($obj);
        }
        foreach ($address as $item) {
            $obj = new FHIRAddress($item);
            if (!empty($item['period'])) {
                $obj->setPeriod(new FHIRPeriod($item['period']));
            }
            $organization->addAddress($obj);
        }
        foreach ($identifiers as $item) {
            $obj = new FHIRIdentifier($item);
            $organization->addIdentifier($obj);
        }
        return $organization;
    }
}
