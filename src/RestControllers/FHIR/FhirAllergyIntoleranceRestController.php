<?php

/**
 * FhirAllergyIntoleranceRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirAllergyIntoleranceRestController
{
    private $fhirAllergyIntoleranceService;
    private $fhirService;

    public function __construct(HttpRestRequest $request)
    {
        $this->fhirAllergyIntoleranceService = new FhirAllergyIntoleranceService($request->getApiBaseFullUrl());
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR allergyIntolerance resource by FHIR id
     * @param $fhirId The FHIR allergyIntolerance resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->fhirAllergyIntoleranceService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR allergyIntolerance resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams, $puuidBind = null)
    {
        $processingResult = $this->fhirAllergyIntoleranceService->getAll($searchParams, $puuidBind);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('AllergyIntolerance', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
