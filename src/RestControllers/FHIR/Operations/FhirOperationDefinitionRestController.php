<?php

namespace OpenEMR\RestControllers\FHIR\Operations;

use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationDefinition;
use OpenEMR\FHIR\R4\FHIRElement\FHIROperationKind;
use OpenEMR\FHIR\R4\FHIRElement\FHIROperationParameterUse;
use OpenEMR\FHIR\R4\FHIRResource\FHIROperationDefinition\FHIROperationDefinitionParameter;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Validators\ProcessingResult;

class FhirOperationDefinitionRestController
{
    /**
     * @var FhirResourcesService
     */
    private $fhirService;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for FHIR OperationDefinition resources using various search parameters.
     * @param @searchParams
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams)
    {
        // the only resource we have right now is the Export operation
        $resources = [
            $this->getBulkDataStatusDefinition()
        ];

        $bundleSearchResult = $this->fhirService->createBundle('OperationDefinition', $resources, false);
        $response = $this->createResponseForCode(StatusCode::OK);
        $response->getBody()->write(json_encode($bundleSearchResult));
        return $response;
    }

    public function getOne($operationId)
    {
        $processingResult = new ProcessingResult();
        $statusCode = 200;
        if ($operationId == '$bulkdata-status') {
            $processingResult->addData($this->getBulkDataStatusDefinition());
        }
        return RestControllerHelper::handleFhirProcessingResult($processingResult, $statusCode);
    }
    /**
     * Create a response object for the given status code with our default set of headers.
     * @param $statusCode
     * @return ResponseInterface
     */
    private function createResponseForCode($statusCode)
    {
        $response = (new Psr17Factory())->createResponse($statusCode);
        return $response->withAddedHeader('Content-Type', 'application/json');
    }

    private function getBulkDataStatusDefinition()
    {
        $opDef = new FHIROperationDefinition();
        $opDef->setName('$bulkdata-status');
        $opDef->setStatus("active");

        $opDefKind = new FHIROperationKind();
        $opDefParameter = new FHIROperationDefinitionParameter();
        $opDefParameter->setName("job");
        $opDefParameterUse = new FHIROperationParameterUse();
        $opDefParameterUse->setValue('in');
        $opDefParameter->setUse($opDefParameterUse);
        $opDefParameter->setMin(1);
        $opDefParameter->setMax(1);
        $opDefParameter->setType(UtilsService::createCoding("string", "string", "http://hl7.org/fhir/data-types"));
        $opDefKind->setValue("operation");
        $opDefParameter->setSearchType(UtilsService::createCoding("string", "string", "http://hl7.org/fhir/ValueSet/search-param-type"));
        $opDef->setKind($opDefKind);
        $opDef->addParameter($opDefParameter);
        return $opDef;
    }
}
