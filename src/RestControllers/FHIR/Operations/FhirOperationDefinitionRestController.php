<?php

namespace OpenEMR\RestControllers\FHIR\Operations;

use OpenApi\Attributes as OA;
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
    #[OA\Get(
        path: '/fhir/OperationDefinition',
        description: 'Returns a list of the OperationDefinition resources that are specific to this OpenEMR installation',
        tags: ['fhir'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Return list of OperationDefinition resources'
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/fhir/OperationDefinition/{operation}',
        description: 'Returns a single OperationDefinition resource that is specific to this OpenEMR installation',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'operation',
                in: 'path',
                description: 'The name of the operation to query. For example $bulkdata-status',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'json object',
                                description: 'FHIR Json object.',
                                type: 'object'
                            ),
                        ],
                        example: [
                            'resourceType' => 'OperationDefinition',
                            'name' => '$bulkdata-status',
                            'status' => 'active',
                            'kind' => 'operation',
                            'parameter' => [
                                [
                                    'name' => 'job',
                                    'use' => 'in',
                                    'min' => 1,
                                    'max' => 1,
                                    'type' => [
                                        'system' => 'http://hl7.org/fhir/data-types',
                                        'code' => 'string',
                                        'display' => 'string',
                                    ],
                                    'searchType' => [
                                        'system' => 'http://hl7.org/fhir/ValueSet/search-param-type',
                                        'code' => 'string',
                                        'display' => 'string',
                                    ],
                                ],
                            ],
                        ]
                    )
                )
            ),
        ]
    )]
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
