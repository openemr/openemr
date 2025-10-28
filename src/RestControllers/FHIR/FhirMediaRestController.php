<?php

/**
 * FhirLocationRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FHIR\FhirLocationService;
use OpenEMR\Services\FHIR\FhirMediaService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\Response;

class FhirMediaRestController
{
    /**
     * @var FhirMediaService
     */
    private readonly FhirMediaService $fhirMediaService;


    public function __construct(HttpRestRequest $request)
    {
        $this->fhirMediaService = new FhirMediaService();
        $this->fhirMediaService->setSession($request->getSession());
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param $fhirId The FHIR location resource id (uuid)
     * @returns Response 200 if the operation completes successfully
     */
    public function getOne($fhirId, $patientUuid): Response
    {
        $processingResult = $this->fhirMediaService->getOne($fhirId, $patientUuid);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
