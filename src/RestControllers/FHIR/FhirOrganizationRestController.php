<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\FHIR\Serialization\FhirOrganizationSerializer;
use Symfony\Component\HttpFoundation\Response;

/**
 * FHIR Organization Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirOrganizationRestController
{
    use SystemLoggerAwareTrait;

    /**
     * @var FhirOrganizationService
     */
    private FhirOrganizationService $fhirOrganizationService;
    private FhirResourcesService $fhirService;
    private FhirValidationService $fhirValidationService;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirOrganizationService = new FhirOrganizationService();
        $this->fhirValidationService = new FhirValidationService();
    }

    public function setSystemLogger(SystemLogger $systemLogger): void
    {
        $this->fhirOrganizationService->setSystemLogger($systemLogger);
        $this->systemLogger = $systemLogger;
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
     * @return Response The http response object containing the FHIR bundle with query results, if found
     */
    public function getAll($searchParams): Response
    {
        $processingResult = $this->fhirOrganizationService->getAll($searchParams);
        $bundleEntries = [];
        // TODO: adunsulag why isn't this work done in the fhirService->createBundle?
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            $bundleEntries[] = $fhirBundleEntry;
        }
        $bundleSearchResult = $this->fhirService->createBundle('Organization', $bundleEntries, false);
        return RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
    }


    /**
     * Queries for a single FHIR organization resource by FHIR id
     * @param $fhirId string The FHIR organization resource id (uuid)
     * @param $puuidBind string|null Optional to restrict visibility of the organization to the one with this puuid.
     * @returns Response 200 if the operation completes successfully
     */
    public function getOne(string $fhirId, ?string $puuidBind = null): Response
    {
        $processingResult = $this->fhirOrganizationService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Creates a new FHIR organization resource
     * @param $fhirJson array The FHIR organization resource
     * @returns Response 201 if the resource is created, 400 if the resource is invalid
     */
    public function post(array $fhirJson): Response
    {
        $fhirValidationService = $this->fhirValidationService->validate($fhirJson);
        if (!empty($fhirValidationService)) {
            return RestControllerHelper::responseHandler($fhirValidationService, null, 400);
        }

        $organization = $this->createOrganizationFromJSON($fhirJson);
        $processingResult = $this->fhirOrganizationService->insert($organization);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR organization resource
     * @param $fhirId string The FHIR organization resource id (uuid)
     * @param $fhirJson array The updated FHIR organization resource (complete resource)
     * @returns Response 200 if the resource is created, 400 if the resource is invalid
     */
    public function patch(string $fhirId, array $fhirJson): Response
    {
        $fhirValidationService = $this->fhirValidationService->validate($fhirJson);
        if (!empty($fhirValidationService)) {
            return RestControllerHelper::responseHandler($fhirValidationService, null, 400);
        }

        $organization = $this->createOrganizationFromJSON($fhirJson);
        $processingResult = $this->fhirOrganizationService->update($fhirId, $organization);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    private function createOrganizationFromJSON($fhirJson): FHIROrganization
    {
        return FhirOrganizationSerializer::deserialize($fhirJson);
    }
}
