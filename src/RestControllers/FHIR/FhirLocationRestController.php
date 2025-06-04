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

use OpenEMR\Services\FHIR\FhirLocationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirLocationRestController
{
    /**
     * @var FhirLocationService
     */
    private $fhirLocationService;

    /**
     * @var FhirResourcesService
     */
    private $fhirService;

    public function __construct()
    {
        $this->fhirLocationService = new FhirLocationService();
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param $fhirId The FHIR location resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId, $patientUuid)
    {
        $processingResult = $this->fhirLocationService->getOne($fhirId, $patientUuid);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR location resources using various search parameters.
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams, $patientUuid)
    {
        $processingResult = $this->fhirLocationService->getAll($searchParams, $patientUuid);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Location', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
