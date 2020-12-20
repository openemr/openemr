<?php

/**
 * FhirPractitionerRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Validators\ProcessingResult;

require_once(__DIR__ . '/../../../_rest_config.php');

/**
 * Supports REST interactions with the FHIR practitioner resource
 */
class FhirPractitionerRestController
{
    private $fhirPractitionerService;
    private $fhirService;
    private $fhirValidate;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirPractitionerService = new FhirPractitionerService();
        $this->fhirValidate = new FhirValidationService();
    }

    /**
     * Creates a new FHIR practitioner resource
     * @param $fhirJson The FHIR practitioner resource
     * @returns 201 if the resource is created, 400 if the resource is invalid
     */
    public function post($fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }

        $processingResult = $this->fhirPractitionerService->insert($fhirJson);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR practitioner resource
     * @param $fhirId The FHIR practitioner resource id (uuid)
     * @param $fhirJson The updated FHIR practitioner resource (complete resource)
     * @returns 200 if the resource is created, 400 if the resource is invalid
     */
    public function patch($fhirId, $fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }

        $processingResult = $this->fhirPractitionerService->update($fhirId, $fhirJson);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for a single FHIR practitioner resource by FHIR id
     * @param $fhirId The FHIR practitioner resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirPractitionerService->getOne($fhirId, true);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR practitioner resources using various search parameters.
     * Search parameters include:
     * - active (active)
     * - address (street, zip, city, or state)
     * - address-city
     * - address-postalcode
     * - address-state
     * - email
     * - family
     * - given (first name or middle name)
     * - name (title, first name, middle name, last name)
     * - phone (phone, work, cell)
     * - telecom (email, phone)
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirPractitionerService->getAll($searchParams);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Practitioner', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
