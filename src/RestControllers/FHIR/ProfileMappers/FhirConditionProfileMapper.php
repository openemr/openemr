<?php

/*
 * FhirConditionProfileMapper.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR\ProfileMappers;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\Services\FHIR\UtilsService;

// TODO: @adunsulag after playing around with the profiling logic, it seems like this class is not needed
class FhirConditionProfileMapper
{
    public function profileResource(FHIRCondition $unProfiledResource, string $fhirProfile): FHIRCondition
    {
        // This is a placeholder for the actual profiling logic.
        // The logic would typically involve modifying the $unProfiledResource
        // to conform to the specified $fhirProfile.

        // For now, we will just return the unprofiled resource as is.
        return match ($fhirProfile) {
            'http://hl7.org/fhir/StructureDefinition/Condition|7.0' => $this->getUsCore7ProfiledResource($unProfiledResource),
            'http://hl7.org/fhir/StructureDefinition/Condition|7.0' => $this->getUsCore7ProfiledResource($unProfiledResource),
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition' => $this->getUsCore3ProfiledResource($unProfiledResource),
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition|3.1.1' => $this->getUsCore3ProfiledResource($unProfiledResource),
            default => $unProfiledResource,
        };
        return $unProfiledResource;
    }

    private function getUsCore3ProfiledResource(FHIRCondition $unProfiledResource)
    {
        if ($unProfiledResource->getEncounter()) {
            $unProfiledResource->getMeta()->addProfile(new FHIRCanonical("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-problems-health-concerns|3.0.0"));
            $unProfiledResource->addCategory(UtilsService::createCodeableConcept([
                'us-core' => [
                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                    'code' => 'encounter-diagnosis',
                    'display' => xl('Encounter Diagnosis')
                ]
            ]));
        } else {
            $unProfiledResource->addCategory(UtilsService::createCodeableConcept([
                'us-core' => [
                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                    'code' => 'problem-list-item',
                    'display' => xl('Problem List Item')
                ]
            ]));
        }

        return $unProfiledResource;
    }

    private function getUsCore7ProfiledResource(FHIRCondition $unProfiledResource)
    {
        $resource = $this->getUsCore3ProfiledResource($unProfiledResource);
        $resource->getMeta()->addProfile(new FHIRCanonical("http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-problems-health-concerns|7.0.0"));
        return $resource;
    }
}
