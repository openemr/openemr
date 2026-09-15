<?php

/**
 * FHIR Resource Controller example for handling and responding to QuestionnaireResponse
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
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
use OpenEMR\Services\FHIR\FhirQuestionnaireResponseService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use Psr\Http\Message\ResponseInterface;

class FhirQuestionnaireResponseRestController
{
    /**
     * @var FhirResourcesService
     */
    private readonly FhirResourcesService $fhirService;

    /**
     * @param ?FhirQuestionnaireResponseService $resourceService
     */
    public function __construct(private readonly ?FhirQuestionnaireResponseService $resourceService = null)
    {
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * @return FhirQuestionnaireResponseService
     */
    public function getFhirQuestionnaireResponseService(): FhirQuestionnaireResponseService
    {
        return $this->resourceService;
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
        path: '/fhir/QuestionnaireResponse',
        description: 'Returns a list of QuestionnaireResponse resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The id for the QuestionnaireResponse resource. ',
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
                                    'url' => 'https://localhost:9300/apis/default/fhir/QuestionnaireResponse',
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
            // Non-patient ACL enforcement (`patients/med`) lives at the FHIR
            // route callback for GET /fhir/QuestionnaireResponse so it runs
            // uniformly regardless of whether a caller invokes the controller
            // directly. Do not add a second check here — the route is the
            // single source of truth.
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
        path: '/fhir/QuestionnaireResponse/{uuid}',
        description: 'Returns a single QuestionnaireResponse resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The id for the QuestionnaireResponse resource. Format is \\<resource name\\>:\\<uuid\\> (Example: AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145).',
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
                            'meta' => [
                                'lastUpdated' => '2021-09-14T09:13:51',
                            ],
                            'resourceType' => 'Bundle',
                            'type' => 'collection',
                            'total' => 0,
                            'link' => [
                                [
                                    'relation' => 'self',
                                    'url' => 'https://localhost:9300/apis/default/fhir/QuestionnaireResponse',
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
    public function one(HttpRestRequest $request, string $id): ResponseInterface
    {
        $processingResult = $this->resourceService->getOne($id, $request->getPatientUUIDString());
        return RestControllerHelper::getResponseForProcessingResult($processingResult);
    }

    /**
     * Queries for FHIR encounter resources using various search parameters.
     * Search parameters include:
     * - _id (euuid)
     * - patient (puuid)
     * - date {gt|lt|ge|le}
     *
     * @param  $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return false|FHIRBundle FHIR bundle with query results, if found
     */
    private function getAll($searchParams, $puuidBind = null): FHIRBundle|false
    {
        $processingResult = $this->resourceService->getAll($searchParams, $puuidBind);
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

    // TODO: @adunsulag create is defined in the private assessment module but depends on the symfony object deserializer...
    // before we can bring this into core we need to check w/ admin team on adding dependency

    /**
     * Creates a new FHIR QuestionnaireResponse resource.
     * Routed via FhirGenericRestController::post(). This method exists only
     * to provide OpenAPI documentation via attributes.
     *
     * @param array<string, mixed> $fhirJson
     */
    // @codeCoverageIgnoreStart
    #[OA\Post(
        path: '/fhir/QuestionnaireResponse',
        description: 'Creates a new QuestionnaireResponse resource.',
        tags: ['fhir'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(type: 'object')
            )
        ),
        responses: [
            new OA\Response(response: '201', description: 'QuestionnaireResponse resource created'),
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
     * Updates an existing FHIR QuestionnaireResponse resource.
     * Routed via FhirGenericRestController::put(). This method exists only
     * to provide OpenAPI documentation via attributes.
     *
     * @param array<string, mixed> $fhirJson
     */
    #[OA\Put(
        path: '/fhir/QuestionnaireResponse/{uuid}',
        description: 'Modifies a QuestionnaireResponse resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the QuestionnaireResponse resource.',
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
            new OA\Response(response: '200', description: 'QuestionnaireResponse resource updated'),
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
}
