<?php

/**
 * FhirBundleSerializer.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, M.D. <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 Luis M. Santos, M.D. <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 MedicalMasses L.L.C. <contact@medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Serialization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBundleType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRSignature;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;

class FhirBundleSerializer
{
    public static function serialize(FHIRBundle $object)
    {
        return $object->jsonSerialize();
    }

    // TODO: Update the FHIR types with the new php-fhir generated types since they come with full serialization and deserialization
    // Such a change has a high risk of breaking things. At least, type names may have changed for some items.
    // As a prototype, I added the manual deserialization similar to what is done elsewhere.
    /**
     * Takes a fhir json representing an organization and returns the populated the resource
     * @param $fhirJson
     * @return FHIRBundle
     */
    public static function deserialize($fhirJson): FHIRBundle
    {
        $identifier = $fhirJson['identifier'] ?? null;
        $type = $fhirJson['type'] ?? null;
        $timestamp = $fhirJson['timestamp'] ?? null;
        $total = $fhirJson['total'] ?? null;
        $entries = $fhirJson['entry'] ?? [];
        $signature = $fhirJson['signature'] ?? null;

        $bundle = new FHIRBundle($fhirJson);
        foreach ($entries as $entry) {
            $resource = $entry['resource'] ?? null;

            $isResource = true;
            $deserializedResource = null;
            switch ($resource['resourceType']) {
                case 'Patient':
                    $deserializedResource = new FHIRPatient($resource);
                    break;
                case 'Practitioner':
                    $deserializedResource = new FHIRPractitioner($resource);
                    break;
                case 'Organization':
                    $deserializedResource = new FHIROrganization($resource);
                    break;
                default:
                    $isResource = false;
                    break;
            }

            if ($isResource) {
                $deserializedEntry = new FHIRBundleEntry($entry);
                $deserializedEntry->resource = $deserializedResource;

                $bundle->addEntry($deserializedEntry);
            }
        }

        $bundle->setIdentifier(new FHIRIdentifier($identifier));
        $bundle->setType(new FHIRBundleType($type));
        $bundle->setTimestamp(new FHIRInstant($timestamp));
        $bundle->setTotal(new FHIRUnsignedInt($total));
        $bundle->setSignature(new FHIRSignature($signature));

        return $bundle;
    }
}
