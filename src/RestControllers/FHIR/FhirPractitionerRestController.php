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

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\FHIR\Serialization\FhirPractitionerSerializer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Supports REST interactions with the FHIR practitioner resource
 */
class FhirPractitionerRestController
{
    use SystemLoggerAwareTrait;

    private FhirPractitionerService $fhirPractitionerService;
    private FhirResourcesService $fhirService;
    private FhirValidationService $fhirValidate;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirPractitionerService = new FhirPractitionerService();
        $this->fhirValidate = new FhirValidationService();
    }

    public function setSystemLogger(SystemLogger $systemLogger): void
    {
        $this->fhirPractitionerService->setSystemLogger($systemLogger);
        $this->systemLogger = $systemLogger;
    }

    /**
     * Creates a new FHIR practitioner resource
     * @param array $fhirJson The FHIR practitioner resource
     * @returns Response 201 if the resource is created, 400 if the resource is invalid
     */
    public function post($fhirJson): Response
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::handleFhirProcessingResult($fhirValidate, 400);
        }

        $object = FhirPractitionerSerializer::deserialize($fhirJson);

        $processingResult = $this->fhirPractitionerService->insert($object);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR practitioner resource
     * @param string $fhirId The FHIR practitioner resource id (uuid)
     * @param array $fhirJson The updated FHIR practitioner resource (complete resource)
     * @returns ResponseInterface 200 if the resource is created, 400 if the resource is invalid
     */
    public function patch($fhirId, $fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }

        $object = FhirPractitionerSerializer::deserialize($fhirJson);

        $processingResult = $this->fhirPractitionerService->update($fhirId, $object);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for a single FHIR practitioner resource by FHIR id
     * @param string $fhirId The FHIR practitioner resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirPractitionerService->getOne($fhirId);
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
     * @return Response FHIR bundle with query results, if found
     */
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirPractitionerService->getAll($searchParams);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
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
