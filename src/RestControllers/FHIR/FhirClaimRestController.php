<?php

/**
 * FhirClaimRestController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Nilesh Hake <nilesh.hake@nbhhealthsoft.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirClaimService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaim;

class FhirClaimRestController
{
    private $fhirClaimService;

    public function __construct()
    {
        $this->fhirClaimService = new FhirClaimService();
    }

    public function post($data)
    {
        if (empty($data) || !is_array($data)) {
            return RestControllerHelper::responseHandler("Invalid data", null, 400);
        }

        // Standard FHIR post data is an array from json_decode
        $fhirObject = new FHIRClaim($data);

        $result = $this->fhirClaimService->insert($fhirObject);

        return RestControllerHelper::handleFhirProcessingResult($result, 201);
    }
}