<?php

/**
 * FhirPersonRestController.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson;
use OpenEMR\Services\FHIR\FhirPersonService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

require_once(__DIR__ . '/../../../_rest_config.php');

/**
 * Supports REST interactions with the FHIR practitioner resource
 */
class FhirPersonRestController
{
    /**
     * @var FhirPersonService
     */
    private $fhirPersonService;

    private $fhirService;
    private $fhirValidate;
    private $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->fhirService = new FhirResourcesService();
        $this->fhirPersonService = new FhirPersonService();
        $this->fhirValidate = new FhirValidationService();
    }

    /**
     * Queries for a single FHIR person resource by FHIR id
     * @param $fhirId The FHIR person resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId)
    {
        $this->logger->debug("FhirPersonRestController->getOne(fhirId)", ["fhirId" => $fhirId]);
        $processingResult = $this->fhirPersonService->getOne($fhirId, true);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR person resources using various search parameters.
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
        $processingResult = $this->fhirPersonService->getAll($searchParams);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle(FhirPersonService::RESOURCE_NAME, $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
