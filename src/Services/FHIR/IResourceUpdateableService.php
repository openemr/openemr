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
     * Updates an existing FHIR resource in the system.
     */
    public function update($fhirResourceId, FHIRDomainResource $fhirResource): ProcessingResult;
}
