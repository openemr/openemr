<?php

/**
 * FHIR Resource Controller example for handling and responding to
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2022 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Logging\SystemLogger;
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

    public function __construct(private readonly SystemLogger $logger, private readonly FhirQuestionnaireService $questionnaireResourceService)
    {
        $this->fhirService = new FhirResourcesService();
    }

    public function getSystemLogger(): SystemLogger
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
