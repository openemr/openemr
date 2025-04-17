<?php

/**
 * Represents a custom FHIR resource that is used for example purposes only.  It is NOT in the FHIR specification
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2022 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\HipaaiChat;

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;

class CustomSkeletonFHIRResource extends FHIRDomainResource
{
    /**
     * The patient or group present at the encounter.
     * @var FHIRReference
     */
    public $patient = null;

    /**
     * @return FHIRReference
     */
    public function getPatient(): FHIRReference
    {
        return $this->patient;
    }

    /**
     * @param FHIRReference $patient
     * @return CustomSkeletonFHIRResource
     */
    public function setPatient(FHIRReference $patient): CustomSkeletonFHIRResource
    {
        $this->patient = $patient;
        return $this;
    }

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = 'CustomSkeletonResource';
        $json['patient'] = $this->patient;
        return $json;
    }
}
