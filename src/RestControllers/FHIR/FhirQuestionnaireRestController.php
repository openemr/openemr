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
use OpenEMR\Common\Logging\SystemLogger;
use Psr\Log\LoggerInterface;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirQuestionnaireService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use Psr\Http\Message\ResponseInterface;

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
        path: "/fhir/Questionnaire",
        description: "Returns a list of Questionnaire resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The id for the Questionnaire resource. ",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "json object",
                                description: "FHIR Json object.",
                                type: "object"
                            ),
                        ],
                        example: [
                            "meta" => [
                                "lastUpdated" => "2021-09-14T09:13:51",
                            ],
                            "resourceType" => "Bundle",
                            "type" => "collection",
                            "total" => 0,
                            "link" => [
                                [
                                    "relation" => "self",
                                    "url" => "https://localhost:9300/apis/default/fhir/Questionnaire",
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
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
    public function one(HttpRestRequest $request, string $id): ResponseInterface
    {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $processingResult = $this->questionnaireResourceService->getOne($request->getPatientUUIDString());
        } else {
            $processingResult = $this->questionnaireResourceService->getOne($id);
        }
        return RestControllerHelper::getResponseForProcessingResult($processingResult);
    }

    public function create(HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::getEmptyResponse();
    }

    public function update(HttpRestRequest $request, string $id): ResponseInterface
    {
        return RestControllerHelper::getEmptyResponse();
    }

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
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            $bundleEntries[] = $fhirBundleEntry;
        }
        return $this->fhirService->createBundle('Questionnaire', $bundleEntries, false);
    }
}
