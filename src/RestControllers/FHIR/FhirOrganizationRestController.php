<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Validators\ProcessingResult;

require_once(__DIR__ . '/../../../_rest_config.php');
/**
 * FHIR Organization Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirOrganizationService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirOrganizationRestController
{
    /**
     * @var FhirOrganizationService
     */
    private $fhirOrganizationService;
    private $fhirService;
    private $fhirValidationService;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirOrganizationService = new FhirOrganizationService();
        $this->fhirValidationService = new FhirValidationService();
    }

    /**
     * Queries for FHIR organization resources using various search parameters.
     * Search parameters include:
     * - address (street, postal_code, city, country_code or state)
     * - address-city
     * - address-postalcode
     * - address-state
     * - email
     * - name
     * - phone (work)
     * - telecom (email, phone)
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirOrganizationService->getAll($searchParams);
        $bundleEntries = array();
        // TODO: adunsulag why isn't this work done in the fhirService->createBundle?
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Organization', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }


    /**
     * Queries for a single FHIR organization resource by FHIR id
     * @param $fhirId The FHIR organization resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->fhirOrganizationService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Creates a new FHIR organization resource
     * @param $fhirJson The FHIR organization resource
     * @returns 201 if the resource is created, 400 if the resource is invalid
     */
    public function post($fhirJson)
    {
        $fhirValidationService = $this->fhirValidationService->validate($fhirJson);
        if (!empty($fhirValidationService)) {
            return RestControllerHelper::responseHandler($fhirValidationService, null, 400);
        }

        $processingResult = $this->fhirOrganizationService->insert($fhirJson);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR organization resource
     * @param $fhirId The FHIR organization resource id (uuid)
     * @param $fhirJson The updated FHIR organization resource (complete resource)
     * @returns 200 if the resource is created, 400 if the resource is invalid
     */
    public function patch($fhirId, $fhirJson)
    {
        $fhirValidationService = $this->fhirValidationService->validate($fhirJson);
        if (!empty($fhirValidationService)) {
            return RestControllerHelper::responseHandler($fhirValidationService, null, 400);
        }

        $processingResult = $this->fhirOrganizationService->update($fhirId, $fhirJson);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
