<?php

/**
 * FhirConditionRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirConditionRestController
{
    private readonly FhirConditionService $fhirConditionService;
    private readonly FhirResourcesService $fhirService;

    public function __construct()
    {
        $this->fhirConditionService = new FhirConditionService();
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR condition resource by FHIR id
     * @param string $fhirId The FHIR condition resource id (uuid)
     * @param string $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns Response 200 if the operation completes successfully
     */
    public function getOne($fhirId, $puuidBind = null): Response
    {
        $processingResult = $this->fhirConditionService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR condition resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * @param array $searchParams
     * @param string $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return JsonResponse|Response FHIR bundle with query results, if found
     */
    public function getAll($searchParams, $puuidBind = null): JsonResponse|Response
    {
        $processingResult = $this->fhirConditionService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Condition', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
