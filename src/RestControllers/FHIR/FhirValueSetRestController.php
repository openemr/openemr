<?php

namespace OpenEMR\RestControllers\FHIR;

use CategoryTree;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCodeSystem;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRValueSet;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemConcept;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemProperty1;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetCompose;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetConcept;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetFilter;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\CodeSystem\FhirCodeSystemService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirValueSetService;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Validators\ProcessingResult;

class FhirValueSetRestController
{
    /**
     * @var FhirResourcesService
     */
    private $fhirService;

    /**
     * @var HttpRestRequest
     */
    private $request;

    /**
     * @var FhirCodeSystemService
     */
    private $codeSystemService;

    /**
     * @var FhirValueSetService;
     */
    private $service;

    public function __construct(HttpRestRequest $request)
    {
        $this->fhirService = new FhirResourcesService();
        $this->service = new FhirValueSetService($request->getApiBaseFullUrl());
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
        $bundleSearchResult = $this->fhirService->createBundle('ValueSet', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
