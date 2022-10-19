<?php

namespace OpenEMR\RestControllers\FHIR;

use CategoryTree;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirCodeSystemService;
use OpenEMR\Services\FHIR\FhirResourcesService;

class FhirCodeSystemRestController
{
    /**
     * @var FhirResourcesService
     */
    private $fhirService;

    /**
     * @var FhirCodeSystemService;
     */
    private $service;

    /**
     * @var HttpRestRequest
     */
    private $request;

    public function __construct(HttpRestRequest $request)
    {
        $this->fhirService = new FhirResourcesService();
        $this->service = new FhirCodeSystemService($request->getApiBaseFullUrl());
        $this->request = $request;
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param $fhirId The FHIR location resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->service->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR location resources using various search parameters.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams, $puuidBind = null)
    {
        $processingResult = $this->service->getAll($searchParams, $puuidBind);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('CodeSystem', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }

    public function getCurrentVersion()
    {
        return $this->service->getDefaultCodeSystemVersion();
    }

    public function getDefaultSystemUri()
    {
        return $this->service->getCodeSystemUri();
    }
}
