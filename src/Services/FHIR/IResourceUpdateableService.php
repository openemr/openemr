<?php

/**
 * IResourceUpdateableService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Validators\ProcessingResult;

interface IResourceUpdateableService
{
    /**
     * Inserts a FHIR resource into the system.
     * @param $fhirResourceId The FHIR Resource ID used to lookup the existing FHIR resource/OpenEMR record
     * @param $fhirResource The FHIR resource.
     * @return The OpenEMR Service Result
     */
    public function update($fhirResourceId, FHIRDomainResource $fhirResource): ProcessingResult;
}
