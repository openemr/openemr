<?php

/**
 * FHIR Resource Controller example for handling and responding to
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2022 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirQuestionnaireService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class FhirQuestionnaireRestController
{
    /**
     * @var FhirResourcesService
     */
    private readonly FhirResourcesService $fhirService;

    public function __construct(private readonly LoggerInterface $logger, private readonly FhirQuestionnaireService $questionnaireResourceService)
    {
        $this->fhirService = new FhirResourcesService();
    }

    public function getSystemLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getFhirQuestionnaireService(): FhirQuestionnaireService
    {
        return $this->questionnaireResourceService;
    }


    /**
     * Handles the response to the API request GET /fhir/Questionnaire and returns the FHIRBundle resource
     * that was found for the given request.  Any query search parameters are processed by this method.  If the method
     * is run in the patient context (as a logged in patient) it restricts the search to just that patient.
     *
     * @param  HttpRestRequest $request
     * @return ResponseInterface
     */
    #[OA\Get(
        path: '/fhir/Questionnaire',
        description: 'Returns a list of Questionnaire resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The id for the Questionnaire resource. ',
                required: false,
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
                            'meta' => [
                                'lastUpdated' => '2021-09-14T09:13:51',
                            ],
                            'resourceType' => 'Bundle',
                            'type' => 'collection',
                            'total' => 0,
                            'link' => [
                                [
                                    'relation' => 'self',
                                    'url' => 'https://localhost:9300/apis/default/fhir/Questionnaire',
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function list(HttpRestRequest $request): ResponseInterface
    {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $result = $this->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            /**
             * If you need to check the API against any kind of ACL the RestConfig object will do an authorization check
             * and handle the API result back to the HTTP client
             */
            // RestConfig::authorization_check("patients", "med");
            $result = $this->getAll($request->getQueryParams());
        }
        return RestControllerHelper::returnSingleObjectResponse($result);
    }

    /**
     * Retrieves a single api resource.  Handles the response to the API request GET /fhir/Questionnaire/:fhirId
     * The $fhirId is populated from the API request by the rest route dispatcher.
     *
     * @see    HttpRestRouteHandler::dispatch to see how this parsing is done.
     * @param  string          $id      The unique id of the resource to be returned.
     * @param  HttpRestRequest $request
     * @return ResponseInterface
     */
    #[OA\Get(
        path: '/fhir/Questionnaire/{uuid}',
        description: 'Returns a single Questionnaire resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the Questionnaire resource.',
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
                            'id' => '95e8d830-3068-48cf-930a-2fefb18c2bcf',
                            'meta' => ['versionId' => '1', 'lastUpdated' => '2021-09-14T09:13:51'],
                            'resourceType' => 'Questionnaire',
                            'status' => 'active',
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', ref: '#/components/responses/uuidnotfound'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function one(HttpRestRequest $request, string $id): ResponseInterface
    {
        // Questionnaire is definitional, not patient data -- the service implements
        // INonPatientCompartmentResourceService. Binding the lookup to the request's patient
        // uuid would search for a Questionnaire whose id happens to equal that patient's, which
        // never matches; there is nothing to scope here.
        $processingResult = $this->questionnaireResourceService->getOne($id);

        return RestControllerHelper::getResponseForProcessingResult($processingResult);
    }

    /**
     * Creates a new FHIR Questionnaire resource.
     * Routed via FhirGenericRestController::post(). This method exists only
     * to provide OpenAPI documentation via attributes.
     *
     * @param array<string, mixed> $fhirJson
     */
    // @codeCoverageIgnoreStart
    #[OA\Post(
        path: '/fhir/Questionnaire',
        description: 'Creates a new Questionnaire resource.',
        tags: ['fhir'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(type: 'object')
            )
        ),
        responses: [
            new OA\Response(response: '201', description: 'Questionnaire resource created'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post(array $fhirJson): void
    {
        // Implementation lives in FhirGenericRestController::post()
    }

    /**
     * Updates an existing FHIR Questionnaire resource.
     * Routed via FhirGenericRestController::put(). This method exists only
     * to provide OpenAPI documentation via attributes.
     *
     * @param array<string, mixed> $fhirJson
     */
    #[OA\Put(
        path: '/fhir/Questionnaire/{uuid}',
        description: 'Modifies a Questionnaire resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the Questionnaire resource.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(type: 'object')
            )
        ),
        responses: [
            new OA\Response(response: '200', description: 'Questionnaire resource updated'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function put(string $fhirId, array $fhirJson): void
    {
        // Implementation lives in FhirGenericRestController::put()
    }
    // @codeCoverageIgnoreEnd

    /**
     * Queries for FHIR encounter resources using various search parameters.
     * Search parameters include:
     * - _id (euuid)
     * - patient (puuid)
     * - date {gt|lt|ge|le}
     *
     * @param  array   $searchParams
     * @param  ?string $puuidBind    - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIRBundle FHIR bundle with query results, if found
     */
    private function getAll(array $searchParams, ?string $puuidBind = null): FHIRBundle
    {
        $processingResult = $this->questionnaireResourceService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  OEGlobalsBag::getInstance()->get('site_addr_oath') . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            $bundleEntries[] = $fhirBundleEntry;
        }
        return $this->fhirService->createBundle('Questionnaire', $bundleEntries, false);
    }
}
