<?php

/**
 * FhirServiceRequestRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\Serialization\FhirServiceRequestSerializer;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\Response;

class FhirServiceRequestRestController
{
    private FhirServiceRequestService $fhirServiceRequestService;
    private FhirResourcesService $fhirService;
    private FhirValidationService $fhirValidationService;

    public function __construct()
    {
        $this->fhirServiceRequestService = new FhirServiceRequestService();
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidationService = new FhirValidationService();
    }

    public function setSystemLogger(SystemLogger $systemLogger): void
    {
        $this->fhirServiceRequestService->setSystemLogger($systemLogger);
    }

    /**
     * Queries for a single FHIR ServiceRequest resource by FHIR id
     *
     * @param string $fhirId The FHIR ServiceRequest resource id (uuid)
     * @param string|null $puuidBind Optional variable to only allow visibility of the patient with this puuid.
     * @return Response
     */
    public function getOne(string $fhirId, ?string $puuidBind = null): Response
    {
        $processingResult = $this->fhirServiceRequestService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR ServiceRequest resources using various search parameters.
     *
     * @param array<string, mixed> $searchParams The search parameters
     * @param string|null $puuidBind Optional variable to only allow visibility of the patient with this puuid.
     * @return Response
     */
    public function getAll(array $searchParams, ?string $puuidBind = null): Response
    {
        $processingResult = $this->fhirServiceRequestService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        /** @var FHIRServiceRequest $searchResult */
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' => $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('ServiceRequest', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }

    /**
     * Creates a new FHIR ServiceRequest resource.
     *
     * @param array<string, mixed> $fhirJson The FHIR ServiceRequest resource as JSON array
     * @return Response 201 if the resource is created, 400 if the resource is invalid
     */
    public function post(array $fhirJson): Response
    {
        $fhirValidationResult = $this->fhirValidationService->validate($fhirJson);
        if ($fhirValidationResult !== null && $fhirValidationResult !== []) {
            return RestControllerHelper::responseHandler($fhirValidationResult, null, 400);
        }

        $serviceRequest = FhirServiceRequestSerializer::deserialize($fhirJson);
        $processingResult = $this->fhirServiceRequestService->insert($serviceRequest);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR ServiceRequest resource.
     *
     * @param string $fhirId The FHIR ServiceRequest resource id (uuid)
     * @param array<string, mixed> $fhirJson The updated FHIR ServiceRequest resource
     * @return Response 200 if the resource is updated, 400 if the resource is invalid
     */
    public function patch(string $fhirId, array $fhirJson): Response
    {
        $fhirValidationResult = $this->fhirValidationService->validate($fhirJson);
        if ($fhirValidationResult !== null && $fhirValidationResult !== []) {
            return RestControllerHelper::responseHandler($fhirValidationResult, null, 400);
        }

        $serviceRequest = FhirServiceRequestSerializer::deserialize($fhirJson);
        $processingResult = $this->fhirServiceRequestService->update($fhirId, $serviceRequest);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
