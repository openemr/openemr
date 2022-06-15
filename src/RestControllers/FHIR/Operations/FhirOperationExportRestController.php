<?php

namespace OpenEMR\RestControllers\FHIR\Operations;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportMemoryStreamWriter;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGroup;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\R4\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue;
use OpenEMR\RestControllers\FHIR\Operations\InvalidExportHeaderException;
use OpenEMR\Services\FHIR\FhirExportJobService;
use OpenEMR\Services\FHIR\FhirExportServiceLocator;
use OpenEMR\Services\FHIR\FhirGroupService;
use OpenEMR\Services\FHIR\IFhirExportableResourceService;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use OpenEMR\Services\FHIR\UtilsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class FhirOperationExportRestController
{
    /**
     * The DateInterval format that is the maximum time an export process can execute before
     * a shutdown exception is thrown.
     */
    const MAX_EXPORT_TIME_INTERVAL = "PT30S";

    /**
     * The DateInterval format that is the maximum time from our Job's start time that a generated export document can
     * be accessed in the system.
     */
    const MAX_DOCUMENT_ACCESS_TIME = "PT1H";

    /**
     * Only allowed header format for the operation outcome
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#headers
     */
    const ACCEPT_HEADER_OPERATION_OUTCOME = 'application/fhir+json';
    const PREFER_HEADER = 'respond-async';

    /**
     * The folder name that export documents are stored in.
     */
    const FHIR_DOCUMENT_FOLDER = 'system-fhir-export';

    /**
     * Name of the category we stick our exports in for ACL controls
     */
    const FHIR_DOCUMENT_CATEGORY = 'FHIR Export Document';

    /**
     * @var HttpRestRequest The current http request object
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IFhirExportableResourceService[] hashmap of resources to service classes that can be exported
     */
    private $resourceRegistry;

    /**
     * @var
     */
    private $isExportDisabled;

    public function __construct(HttpRestRequest $request)
    {
        $this->request = $request;
        $this->logger = new SystemLogger();
        $this->fhirExportJobService = new FhirExportJobService();
        $this->isExportDisabled = !($this->request->getRestConfig()::areSystemScopesEnabled());
    }

    /**
     * Takes an HTTP $export operation and processes the operation and returns an HTTP response.  If the request is
     * invalid the response will include an OperationOutcome for the error.  If the request is valid it will return
     * an empty body with the 'Content-Location' set to the URL that the calling agent can retrieve status updates on
     * the FHIR export.
     * @param $exportParams  The valid format's that the export can be in.  _outputFormat, _since, and type.
     * @param $exportType  The type of operation this is, Group, System, or Patient.
     * @param $acceptHeader  The 'Accept' http header that any body response format should be returned in.
     * @param $preferHeader The 'Prefer' header which must be set to 'respond-async' for SMART FHIR exports.
     * @return ResponseInterface
     */
    public function processExport($exportParams, $exportType, $acceptHeader, $preferHeader)
    {
        if ($this->isExportDisabled) {
            return (new Psr17Factory())->createResponse(StatusCode::NOT_IMPLEMENTED);
        }

        $outputFormat = $exportParams['_outputFormat'] ?? ExportJob::OUTPUT_FORMAT_FHIR_NDJSON;
        $since = $exportParams['_since'] ?? new \DateTime(date("Y-m-d H:i:s", 0)); // since epoch time
        $type = $exportParams['type'] ?? '';
        $groupId = $exportParams['groupId'] ?? null;
        $resources = !empty($type) ? explode(",", $type) : [];

        $this->logger->debug("FhirExportRestController->processExport() Patient export call made", [
            '_outputFormat' => $outputFormat,
            '_since' => $since,
            '_type' => $type,
            'resources' => $resources,
            'acceptHeader' => $acceptHeader,
            'preferHeader' => $preferHeader,
            'userUUID' => $this->request->getRequestUserUUIDString()
        ]);
        try {
            $this->validateHeaders($acceptHeader, $preferHeader);
            $resources = $this->getResourcesForRequest($resources);
            $response = $this->createResponseForCode(StatusCode::ACCEPTED);

            $job = new ExportJob();
            $job->setOutputFormat($outputFormat);
            $job->setExportType($exportType);
            $job->setGroupId($groupId);
            $job->setResourceIncludeTime($since);
            $job->setClientId($this->request->getClientId());
            $job->setResources($resources);
            $job->setUserId($this->request->getRequestUserUUIDString());
            $job->setAccessTokenId($this->request->getAccessTokenId());
            $job->setRequestURI($this->request->getRequestURI());
            $job->setApiBaseUrl($this->request->getApiBaseFullUrl());

            $job = $this->fhirExportJobService->createJobRequest($job);

            // TODO: make sure the patient ids are returned
            // need to add patient ids to the export job
            // need to modify fhir bulk export domain resource trait to use the patient ids and verify that is all working.
            if ($exportType == ExportJob::EXPORT_OPERATION_GROUP) {
                $job->setPatientUuidsToExport($this->getPatientUuidsForGroup($groupId));
            }

            $completedJob = $this->processResourceExportForJob($job);
            $response = $response->withAddedHeader("Content-Location", $completedJob->getStatusReportURL());
        } catch (AccessDeniedException $exception) {
            $response = $this->createResponseForCode(StatusCode::BAD_REQUEST);
            $operationOutcome = $this->createOperationOutcomeError($exception->getMessage());
            $response->getBody()->write(json_encode($operationOutcome));
        } catch (InvalidExportHeaderException $header) {
            $response = $this->createResponseForCode(StatusCode::BAD_REQUEST);
            $operationOutcome = $this->createOperationOutcomeError($header->getMessage());
            $response->getBody()->write(json_encode($operationOutcome));
        } catch (\Exception $exception) {
            $this->logger->error(
                "FhirExportRestController->processExport() failed to process job",
                ['exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            $response = $this->createResponseForCode(StatusCode::INTERNAL_SERVER_ERROR);
            $operationOutcome = $this->createOperationOutcomeError(xlt("An internal server error occurred"));
            $response->getBody()->write(json_encode($operationOutcome));
        }
        return $response;
    }

    /**
     * Returns an HTTP response with the body including the status of the export.  If the export has not been completed
     * it will return an HTTP status code of StatusCode::ACCEPTED with no body.  If the export is completed it will
     * return the output and errors in the response body.  The output and errors results will include links for the
     * calling Agent to go and download the results from.
     *
     * @param $jobUuidString The unique id of the job to retrieve the status report for
     * @return ResponseInterface
     */
    public function processExportStatusRequestForJob($jobUuidString)
    {
        if ($this->isExportDisabled) {
            return (new Psr17Factory())->createResponse(StatusCode::NOT_IMPLEMENTED);
        }

        // simulate async process
        // if job is still going we would return a 202
        // return's 202 that we are starting the process
        try {
            $status = StatusCode::ACCEPTED;

            $job = $this->fhirExportJobService->getJobForUuid($jobUuidString, $this->request->getClientId(), $this->request->getRequestUserUUIDString());
            if ($job->isComplete()) {
                $status = StatusCode::OK;
                // need to construct our FHIR complete object here
                $jobOutput = json_decode($job->getOutput());
                $jobError = json_decode($job->getErrors());
                $result = [
                    "transactionTime" => $job->getStartTime(),
                    "request" => $job->getRequestURI(),
                    "requiresAccessToken" => true,
                    "output" => $jobOutput,
                    "error" => $jobError
                ];
                $response = $this->createResponseForCode($status);
                $response->getBody()->write(json_encode($result));
            } else {
                $response = (new Psr17Factory())->createResponse($status);
            }
            $this->logger->debug(
                "FhirExportRestController->processExportStatusRequestForJob() status request exit",
                ['jobUuid' => $jobUuidString, 'status' => $job->getStatus(), 'request' => $job->getRequestURI()]
            );
            return $response;
        } catch (\InvalidArgumentException $exception) {
            $this->logger->error(
                "FhirExportRestController->processExport() invalid request",
                ['jobUuid' => $jobUuidString, 'exception' => $exception->getMessage()]
            );
            $response = $this->createResponseForCode(StatusCode::BAD_REQUEST);
            $operationOutcome = $this->createOperationOutcomeError(xlt("The job id you submitted was invalid"));
            $response->getBody()->write(json_encode($operationOutcome));
            return $response;
        } catch (\Exception $exception) {
            $this->logger->error(
                "FhirExportRestController->processExport() failed to process job",
                ['jobUuid' => $jobUuidString, 'exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            $response = $this->createResponseForCode(StatusCode::INTERNAL_SERVER_ERROR);
            $operationOutcome = $this->createOperationOutcomeError(xlt("An internal server error occurred"));
            $response->getBody()->write(json_encode($operationOutcome));
            return $response;
        }
    }

    /**
     * Return's an HTTP response with the result of the delete operation.  The delete response returns a status
     * code of StatusCode::ACCEPTED.  Subsequent calls to the DELETE will return a StatusCode::BAD_REQUEST as the
     * export job has been deleted or marked for deleted.
     * @param $jobUuidString The unique id of the job.
     * @return ResponseInterface
     */
    public function processDeleteExportForJob($jobUuidString)
    {
        if ($this->isExportDisabled) {
            return (new Psr17Factory())->createResponse(StatusCode::NOT_IMPLEMENTED);
        }
        // if a request is completed do we want to allow the operation to be deleted ie remove the trace of the export?

        try {
            $job = $this->fhirExportJobService->getJobForUuid($jobUuidString, $this->request->getClientId(), $this->request->getRequestUserUUIDString());

            $documents = \Document::getDocumentsForForeignReferenceId(ExportJob::TABLE_NAME, $job->getId());
            if (!empty($documents)) {
                foreach ($documents as $document) {
                    $this->logger->debug(
                        "FhirExportRestController->processDeleteExportForJob deleting document",
                        ['job' => $jobUuidString, $document->get_id()]
                    );
                    // we are deleting the export job so we unlink the document
                    $document->set_foreign_reference_table(null);
                    $document->set_foreign_reference_id(null);
                    $document->process_deleted();
                }
            }
            $this->fhirExportJobService->deleteJob($job);
            $response = (new Psr17Factory())->createResponse(StatusCode::ACCEPTED);
        } catch (\InvalidArgumentException $ex) {
            $this->logger->error(
                "FhirExportRestController->processDeleteExportForJob failed to delete job for nonexistant job id",
                ['job' => $jobUuidString]
            );
            return (new Psr17Factory())->createResponse(StatusCode::NOT_FOUND);
        } catch (\Exception $ex) {
            $this->logger->error(
                "FhirExportRestController->processDeleteExportForJob failed to delete job and documents",
                ['job' => $jobUuidString, 'exception' => $ex->getMessage(), 'trace' => $ex->getTraceAsString()]
            );
            return (new Psr17Factory())->createResponse(StatusCode::NOT_FOUND);
        }

        return $response;
    }

    /**
     * Given a job that contains a list of resources to export, process each of those resources and save their results
     * into Documents stored inside OpenEMR in the ndjson format.  There will be one file per resource and the resources
     * are saved in the FHIR_DOCUMENT_FOLDER under the file name of <resource>-<jobUuidString>.ndjson with the ndjson
     * mimetype.  The results and any errors of the export are saved back into the ExportJob.  The ExportJob's status
     * is marked as completed upon completion.
     * @param ExportJob $job The job to start processing the resource exports for.
     * @return ExportJob
     * @see ExportJob::OUTPUT_FORMAT_FHIR_NDJSON
     * @see FhirOperationExportRestController::FHIR_DOCUMENT_FOLDER
     */
    public function processResourceExportForJob(ExportJob $job)
    {

        $processedJob = clone $job;
        // we will generate a bunch of documents here and then save them out to our folder....
        $categoryId = null;
        $ouputResult = [];
        $errorResult = [];
        $shutdownTime = new \DateTime();
        // don't allow the export to take longer than 2 minutes
        $shutdownTime->add(new \DateInterval("PT2M"));

        $shutdownImminent = false;

        foreach ($job->getResources() as $resource) {
            // since we send job into a bunch of services that we have no control over, we copy it in order to make sure
            // one service doesn't mess with the export object.
            $jobForResource = clone $job;
            if (!$this->isValidResource($resource, $jobForResource->getExportType())) {
                $errorResult[] = $this->createOperationOutcomeError("Resource does not support this export operation");
                continue;
            }
            // if we've reached our shutdown point, every subsequent resource we just fail immediately
            if ($shutdownImminent) {
                $this->errorResult[] = $this->getExportTimeoutExportError($resource);
                continue;
            }

            $output = null;
            $error = null;
            // if we had an async process we would be able to resume off of the last id that was exported for this
            // resource
            $lastResourceIdExported = null;
            try {
                $service = $this->getExportServiceForResource($resource);
                // this could be a file pointer, or whatever else we wanted to be able to handle this
                // for now we assume that OpenEMR data can all fit inside memory per resource.... if that changes
                // we should be able to rewrite just a little bit of this to be more efficient.
                $exportWriter = new ExportMemoryStreamWriter($shutdownTime);
                $service->export($exportWriter, $jobForResource, $lastResourceIdExported);

                // we are grabbing the contents to write out to our document
                $output = $this->createOutputResultForData($jobForResource, $resource, $exportWriter->getContents());
                $this->logger->debug("FhirExportRestController->processResourceExportForJob() resource outputted", [
                    'resource' => $resource, 'recordsExported' => $exportWriter->getRecordsWritten()
                ]);
            } catch (ExportWillShutdownException $exception) {
                // we ran out of time and need to mark everything as failed
                $shutdownImminent = true;
                $errorOutcome = $this->getExportTimeoutExportError($resource);
                $error = $this->createErrorResultForOutcomeOperation($job, $errorOutcome);
                $this->logger->error("FhirExportRestController->processResourceExportForJob() Export reached "
                    . "maximum execution time.", [
                    'exception' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(), 'job' => $job->getUuidString(), 'resource' => $resource]);
            } catch (\Exception $exception) {
                $errorMessage = xlt("An unknown system error occurred during the export for resource") . ' ' . $resource;
                $errorOutcome = $this->createOperationOutcomeError($errorMessage);
                $error = $this->createErrorResultForOutcomeOperation($job, $errorOutcome);
                $this->logger->error("FhirExportRestController->processResourceExportForJob() Unknown system error"
                    . " occurred during export", ['exception' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(), 'job' => $job->getUuidString(), 'resource' => $resource]);
            } finally {
                $this->logger->debug("FhirExportRestController->processResourceExportForJob() closing resource", [
                    'resource' => $resource, 'recordsExported' => $exportWriter->getRecordsWritten()
                ]);
                $exportWriter->close();
            }

            if (!empty($output)) {
                $outputResult[] = $output;
            } else {
                $errorResult[] = $error;
            }
        }

        $processedJob->setOutput(json_encode($outputResult));
        $processedJob->setErrors(json_encode($errorResult));
        $processedJob->setStatus(ExportJob::STATUS_COMPLETED);

        return $this->fhirExportJobService->updateJob($processedJob);
    }

    private function createErrorResultForOutcomeOperation(ExportJob $job, FHIROperationOutcome $errorOutcome)
    {
        $jobUuidString = $job->getUuidString();
        $resource = $errorOutcome->get_fhirElementName();
        $fileName = $resource . "-" . $jobUuidString . "-" . $errorOutcome->getId() . ".ndjson";
        $data = json_encode($errorOutcome);
        $document = $this->createExportJobFile($job, $fileName, $data);
        return $this->getResultForResourceDocument($resource, $document);
    }

    private function createOutputResultForData(ExportJob $job, $resource, &$data)
    {
        $jobUuidString = $job->getUuidString();
        $fileName = $resource . "-" . $jobUuidString . ".ndjson";
        $document = $this->createExportJobFile($job, $fileName, $data);
        return $this->getResultForResourceDocument($resource, $document);
    }

    private function getResultForResourceDocument($resource, \Document $document)
    {
        return [
            'url' => $this->request->getApiBaseFullUrl() . '/fhir/Document/' . $document->get_id() . '/Binary'
            , "type" => $resource
        ];
    }

    private function createExportJobFile(ExportJob $job, $fileName, &$data): \Document
    {
        $document = new \Document();
        $folder = self::FHIR_DOCUMENT_FOLDER;
        $categoryId = sqlQuery('Select `id` FROM categories WHERE name=?', [self::FHIR_DOCUMENT_CATEGORY]);
        if ($categoryId === false) {
            throw new ExportException("document category id does not exist in system");
        }

        $mimeType = "application/fhir+ndjson";
        $higherLevelPath = "";
        $pathDepth = 1;
        $owner = 0;  // userID
        $tmpFile = null;

        $expirationDate = $job->getStartTime()
            ->add(new \DateInterval(self::MAX_DOCUMENT_ACCESS_TIME))
            ->format("Y-m-d H:i:s");

        // set the foreign key so we can track documents connected to a specific export
        $result = $document->createDocument(
            $folder,
            $categoryId,
            $fileName,
            $mimeType,
            $data,
            $higherLevelPath,
            $pathDepth,
            $owner,
            $tmpFile,
            $expirationDate,
            $job->getId(),
            'ExportJob'
        );
        if (!empty($result)) {
            throw new \RuntimeException("Failed to save document for job. Message: " . $result);
        }
        return $document;
    }

    /**
     * check's that the export request headers(Accept,Prefer) matches with the SMART Bulk FHIR requirements.
     * @param $acceptHeader The request 'Accept' header
     * @param $preferHeader The request 'Prefer' header
     * @throws InvalidExportHeaderException if the header is invalid.
     */
    private function validateHeaders($acceptHeader, $preferHeader)
    {
        if ($acceptHeader !== self::ACCEPT_HEADER_OPERATION_OUTCOME) {
            throw new InvalidExportHeaderException("'Accept' " . xlt("header invalid"));
        }
        if ($preferHeader !== self::PREFER_HEADER) {
            throw new InvalidExportHeaderException("'Prefer' " . xlt("header invalid"));
        }
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
     * Checks if the passed in resource is valid and can be exported as part of this request.
     * @param $resource The name of the resource to check
     * @param $exportType string The export operation type that is being requested.
     * @return bool true if the resource can be exported, false otherwise.
     */
    private function isValidResource($resource, $exportType)
    {
        $this->request->getRestConfig()::scope_check('system', $resource, 'read');
        $resourceRegistry = $this->getExportServiceRegistry();
        $service = $resourceRegistry[$resource] ?? null;
        if (isset($service)) {
            switch ($exportType) {
                case ExportJob::EXPORT_OPERATION_SYSTEM:
                    return $service->supportsSystemExport();
                    break;
                case ExportJob::EXPORT_OPERATION_GROUP:
                    return $service->supportsGroupExport();
                    break;
                case ExportJob::EXPORT_OPERATION_PATIENT:
                    return $service->supportsPatientExport();
                    break;
            }
        }
        return false;
    }

    /**
     * Return's the list of resources to be exported for the given request.  If the initial resources are empty it
     * returns all the resources possible for the system.
     * @param array $resources
     * @return array
     */
    private function getResourcesForRequest($resources = array())
    {
        // TODO: if we start adding a bunch more FHIR resources and need to filter for just the patient compartment we could do that here
        $approvedResources = [];
        $registry = $this->getExportServiceRegistry();
        $validResources = array_keys($registry);
        // if no resources are sent we are supposed to return the resources that the client has access to in their
        // access token
        if (empty($resources)) {
            foreach ($validResources as $resource) {
                if ($this->hasAccessToResource($resource)) {
                    $approvedResources[] = $resource;
                }
            }
        } else {
            $approvedResources = $resources;
            // if they requested specifically a resource they do not have access to we will deny the request
            foreach ($resources as $resource) {
                if (!$this->hasAccessToResource($resource)) {
                    throw new AccessDeniedException('system', $resource . '.read', 'AccessToken does not grant access to resource ' . $resource);
                }
            }
        }
        if (empty($approvedResources)) {
            throw new AccessDeniedException('system', $resource . '.read', 'AccessToken does grant access to any supported system resources');
        }
        return $approvedResources;
    }

    /**
     * Checks if the current user agent has access to the resource.
     * @param $resource The resource being checked
     * @return bool true if the user agent has access, false otherwise
     */
    private function hasAccessToResource($resource)
    {

        $permission = 'system/' . $resource . '.read';
        $hasAccess = \in_array($permission, $this->request->getAccessTokenScopes());
        $this->logger->debug(
            "FhirExportRestController->hasAccessToResource() Checking resource access",
            ['permission' => $permission, 'hasAccess' => $hasAccess]
        );
        return $hasAccess;
    }

    /**
     * Return the array hashmap of resources to Fhir resource services that can export resources
     * @return IFhirExportableResourceService[]
     */
    private function getExportServiceRegistry()
    {
        if (!empty($this->resourceRegistry)) {
            return $this->resourceRegistry;
        }
        $restConfig = $this->request->getRestConfig();
        $serviceLocator = new FhirExportServiceLocator($restConfig);
        $this->resourceRegistry = $serviceLocator->findExportServices();
        // TODO: @adunsulag is there a better way to handle this... because Provenance uses its own service locator and we need the rest config...
        if (isset($this->resourceRegistry['Provenance'])) {
            $this->resourceRegistry['Provenance']->setServiceLocator(new FhirServiceLocator($restConfig));
        }
        return $this->resourceRegistry;
    }

    private function getExportServiceForResource($resource): IFhirExportableResourceService
    {
        $resourceRegistry = $this->getExportServiceRegistry();
        $service = $resourceRegistry[$resource] ?? null;
        if (!isset($service)) {
            throw new \LogicException("Method should not be called with invalid service resource");
        }
        return $service;
    }

    private function getExportTimeoutExportError()
    {
        return $this->createOperationOutcomeError(xlt("Export process timed out"));
    }

    /**
     * Given an error outcome text create a Fhir Outcome issue for the error and return it.
     * @param $text
     * @return FHIROperationOutcome
     */
    private function createOperationOutcomeError($text)
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

    private function getPatientUuidsForGroup($groupId)
    {
        $patientUuids = [];
        $fhirGroupService = new FhirGroupService();
        $result = $fhirGroupService->getOne($groupId);
        if ($result->hasData()) {
            foreach ($result->getData() as $group) {
                if ($group instanceof FHIRGroup && !empty($group->getMember())) {
                    foreach ($group->getMember() as $member) {
                        if (!empty($member->getEntity()) && !empty($member->getEntity()->getReference())) {
                            $uuid = UtilsService::getUuidFromReference($member->getEntity());
                            if (!empty($uuid)) {
                                $patientUuids[] = $uuid;
                            }
                        }
                    }
                }
            }
        }
        return $patientUuids;
    }
}
