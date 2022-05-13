<?php

/**
 * FhirOperationDocRefRestController handles the creation / retrieve of Clinical Summary of Care (CCD) documents for a patient.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR\Operations;

use Google\Service\Iam\Status;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\FHIR\R4\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirDocRefService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\Search\SearchFieldException;
use Ramsey\Uuid\Uuid;

class FhirOperationDocRefRestController
{
    const OPERATION_OUTCOME_ISSUE_TYPE_PROCESSING = "processing";
    const OPERATION_OUTCOME_ISSUE_TYPE_NOT_SUPPORTED = "not-supported";

    public function __construct(HttpRestRequest $request)
    {
        $this->fhirDocRefService = new FhirDocRefService($request->getApiBaseFullUrl());
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for FHIR location resources using various search parameters.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams, $puuidBind = null)
    {
        try {
            $processingResult = $this->fhirDocRefService->getAll($searchParams, $puuidBind);
            $bundleEntries = array();
            foreach ($processingResult->getData() as $index => $searchResult) {
                // we actually need to truncate off the operation
                $bundleEntry = [
                    'fullUrl' => $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                    'resource' => $searchResult
                ];
                $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
                array_push($bundleEntries, $fhirBundleEntry);
            }
            $bundleSearchResult = $this->fhirService->createBundle('DocumentReference', $bundleEntries, false);
            $response = $this->createResponseForCode(StatusCode::OK);
            $response->getBody()->write(json_encode($bundleSearchResult));
        } catch (SearchFieldException $exception) {
            $systemLogger = new SystemLogger();
            $systemLogger->error(get_class($this) . "->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField(), 'trace' => $exception->getTraceAsString()]);
            // put our exception information here
            $operationOutcome = $this->createOperationOutcomeError($exception->getMessage(), self::OPERATION_OUTCOME_ISSUE_TYPE_PROCESSING);
            $response = $this->createResponseForCode(StatusCode::BAD_REQUEST);
            $response->getBody()->write(json_encode($operationOutcome));
        } catch (\Exception $exception) {
            $response = $this->createResponseForCode(StatusCode::BAD_REQUEST);
            $operationOutcome = $this->createOperationOutcomeError($exception->getMessage(), self::OPERATION_OUTCOME_ISSUE_TYPE_PROCESSING);
            $response->getBody()->write(json_encode($operationOutcome));
        }

        return $response;
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

    /**
     * Given an error outcome text create a Fhir Outcome issue for the error and return it.
     * @param $text
     * @return FHIROperationOutcome
     */
    private function createOperationOutcomeError($text, $type)
    {
        $issue = new FHIROperationOutcomeIssue();
        $issueType = new FHIRIssueType();
        $issueType->setValue("processing");
        $issueSeverity = new FHIRIssueSeverity();
        $issueSeverity->setValue("error");
        $issue->setSeverity($issueSeverity);
        $issue->setCode($issueType);
        $details = new FHIRCodeableConcept();
        $details->setText($text);
        $issue->setDetails($details);
        $operationOutcome = new FHIROperationOutcome();
        $operationOutcome->setId(Uuid::uuid4()); // not sure we care about storing these
        $operationOutcome->addIssue($issue);
        return $operationOutcome;
    }
}
