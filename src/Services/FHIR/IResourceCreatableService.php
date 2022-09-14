<?php

/**
 * IResourceCreatableService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Validators\ProcessingResult;

interface IResourceCreatableService
{
    /**
     * Inserts a FHIR resource into the system.
     * @param $fhirResource The FHIR resource
     * @return The OpenEMR Service Result
     */
    public function insert(FHIRDomainResource $fhirResource): ProcessingResult;
}
