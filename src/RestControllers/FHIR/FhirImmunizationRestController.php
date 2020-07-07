<?php

/**
 * FhirImmunizationRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirImmunizationService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

require_once(__DIR__ . '/../../../_rest_config.php');

/**
 * Supports REST interactions with the FHIR immunization resource
 */
class FhirImmunizationRestController
{
    private $fhirImmunizationService;
    private $fhirService;
    private $fhirValidate;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirImmunizationService = new FhirImmunizationService();
        $this->fhirValidate = new FhirValidationService();
    }

    /**
     * Creates a new FHIR immunization resource
     * @param $fhirJson The FHIR immunization resource
     * @returns 201 if the resource is created, 400 if the resource is invalid
     */
    // public function post($fhirJson)
    // {
    //     $fhirValidate = $this->fhirValidate->validate($fhirJson);
    //     if (!empty($fhirValidate)) {
    //         return RestControllerHelper::responseHandler($fhirValidate, null, 400);
    //     }

    //     $processingResult = $this->fhirImmunizationService->insert($fhirJson);
    //     return RestControllerHelper::handleProcessingResult($processingResult, 201);
    // }

    /**
     * Updates an existing FHIR immunization resource
     * @param $fhirId The FHIR immunization resource id (uuid)
     * @param $fhirJson The updated FHIR immunization resource (complete resource)
     * @returns 200 if the resource is created, 400 if the resource is invalid
     */
    // public function patch($fhirId, $fhirJson)
    // {
    //     $fhirValidate = $this->fhirValidate->validate($fhirJson);
    //     if (!empty($fhirValidate)) {
    //         return RestControllerHelper::responseHandler($fhirValidate, null, 400);
    //     }

    //     $processingResult = $this->fhirImmunizationService->update($fhirId, $fhirJson);
    //     return RestControllerHelper::handleProcessingResult($processingResult, 200);
    // }

    /**
     * Queries for a single FHIR immunization resource by FHIR id
     * @param $fhirId The FHIR immunization resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirImmunizationService->getOne($fhirId, true);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR immunization resources using various search parameters.
     * Search parameters include:
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirImmunizationService->getAll($searchParams);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  \RestConfig::$REST_FULL_URL . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Immunization', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
