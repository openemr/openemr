<?php

/**
 * FhirExportRestController.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * FhirExportRestControllertroller.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\R4\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue;
use OpenEMR\FHIR\SMART\ExportJob;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\FHIR\FhirExportServiceLocator;
use OpenEMR\Services\FHIR\IFhirExportableResourceService;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class InvalidExportHeaderException Represents an invalid export header per Bulk FHIR Spect
 * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#headers
 * @package OpenEMR\RestControllers\FHIR
 */
class InvalidExportHeaderException extends \Exception
{
}

class FhirExportRestController
{
    /**
     * Only allowed header format for the operation outcome
     * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#headers
     */
    const ACCEPT_HEADER_OPERATION_OUTCOME = 'application/fhir+json';
    const PREFER_HEADER = 'respond-async';

    // TODO: @adunsulag is there another place in the system that has our standard datetime constants?
    /**
     * The date format to use for our DateTime values
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * The folder name that export documents are stored in.
     */
    const FHIR_DOCUMENT_FOLDER = 'system-fhir-export';

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

    public function __construct(HttpRestRequest $request)
    {
        $this->request = $request;
        $this->logger = new SystemLogger();
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
        $outputFormat = $exportParams['_outputFormat'] ?? ExportJob::OUTPUT_FORMAT_FHIR_NDJSON;
        $since = $exportParams['_since'] ?? new \DateTime(date("Y-m-d H:i:s", 0)); // since epoch time
        $type = $exportParams['type'] ?? '';
        $resources = !empty($resources) ? explode(",", $type) : [];

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
            $job->setResourceIncludeTime($since);
            $job->setClientId($this->request->getClientId());
            $job->setResources($resources);
            $job->setUserId($this->request->getRequestUserUUIDString());
            $job->setAccessTokenId($this->request->getAccessTokenId());
            $job->setRequestURI($this->request->getRequestURI());
            $job->setApiBaseUrl($this->request->getApiBaseFullUrl());

            $job = $this->createJobRequest($job);
            $completedJob = $this->processResourceExportForJob($job);
            $response = $response->withAddedHeader("Content-Location", $completedJob->getStatusReportURL());

            // go through and run each of our FHIR resource controllers...
            return $response;
        } catch (InvalidExportHeaderException $header) {
            $response = $this->createResponseForCode(StatusCode::BAD_REQUEST);
            // "'Accept' " . xlt("header invalid")
            $operationOutcome = $this->createOperationOutcomeError($header->getMessage());
            $response->getBody()->write(json_encode($operationOutcome));
            return $response;
        } catch (\Exception $exception) {
            $this->logger->error(
                "FhirExportRestController->processExport() failed to process job",
                ['exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            $response = $this->createResponseForCode(StatusCode::INTERNAL_SERVER_ERROR);
            $operationOutcome = $this->createOperationOutcomeError(xlt("An internal server error occurred"));
            $response->getBody()->write(json_encode($operationOutcome));
            return $response;
        }
    }

    /**
     * Returns an HTTP response with the body including the status of the export.  If the export has not been completed
     * it will return an HTTP status code of StatusCode::ACCEPTED with no body.  If the export is completed it will
     * return the output and errors in the response body.  The output and errors results will include links for the
     * calling Agent to go and download the results from.
     *
     * @param $jobId The unique id of the job to retrieve the status report for
     * @return ResponseInterface
     */
    public function processExportStatusRequestForJob($jobId)
    {
        // simulate async process
        // if job is still going we would return a 202
        // return's 202 that we are starting the process
        try {
            $status = StatusCode::ACCEPTED;

            $job = $this->getJobForId($jobId, $this->request->getClientId(), $this->request->getRequestUserUUIDString());
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
                ['jobId' => $jobId, 'status' => $job->getStatus(), 'request' => $job->getRequestURI()]
            );
            return $response;
        } catch (\InvalidArgumentException $exception) {
            $this->logger->error(
                "FhirExportRestController->processExport() invalid request",
                ['jobId' => $jobId, 'exception' => $exception->getMessage()]
            );
            $response = $this->createResponseForCode(StatusCode::BAD_REQUEST);
            $operationOutcome = $this->createOperationOutcomeError(xlt("The job id you submitted was invalid"));
            $response->getBody()->write(json_encode($operationOutcome));
            return $response;
        } catch (\Exception $exception) {
            $this->logger->error(
                "FhirExportRestController->processExport() failed to process job",
                ['jobId' => $jobId, 'exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
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
     * @param $jobId The unique id of the job.
     * @return ResponseInterface
     */
    public function processDeleteExportForJob($jobId)
    {
        // simulate async process
        // return's 202 that we are starting the process
        $response = (new Psr17Factory())->createResponse(StatusCode::ACCEPTED);
        // we could return an OperationOutcome here, but the spec says its optional, so we'll skip it

        // need to grab all of the resource files created and then delete them



        return $response;
    }

    /**
     * Given a job that contains a list of resources to export, process each of those resources and save their results
     * into Documents stored inside OpenEMR in the ndjson format.  There will be one file per resource and the resources
     * are saved in the FHIR_DOCUMENT_FOLDER under the file name of <resource>-<jobId>.ndjson with the ndjson
     * mimetype.  The results and any errors of the export are saved back into the ExportJob.  The ExportJob's status
     * is marked as completed upon completion.
     * @see ExportJob::OUTPUT_FORMAT_FHIR_NDJSON
     * @see FhirExportRestController::FHIR_DOCUMENT_FOLDER
     * @param ExportJob $job  The job to start processing the resource exports for.
     * @return ExportJob
     */
    public function processResourceExportForJob(ExportJob $job)
    {

        $processedJob = clone $job;
        // we will generate a bunch of documents here and then save them out to our folder....
        $document = new \Document();
        $folder = self::FHIR_DOCUMENT_FOLDER;
        $docService = new DocumentService();
        $categoryId = null;
        $ouputResult = [];
        $errorResult = [];

        foreach ($job->getResources() as $resource) {
            if (!$this->isValidResource($resource)) {
                $errorResult[] = $this->createOperationOutcomeError("Resource does not support export operation");
                continue;
            }

            $fileName = $resource . "-" . $job->getId() . ".ndjson";
            $fullPath = $job->getId() . DIRECTORY_SEPARATOR . $fileName;

            // TODO: @adunsulag this might work for small exports... but we may need to change this if we start dealing
            // with a ton of data...
            $lines = [];
            for ($i = 0; $i < 5; $i++) {
                // for now we are just testing with dummy data until we actually run through each resource.
                $patient = new FHIRPatient();
                $fhirId = new FHIRId();
                $fhirId->setValue(Uuid::uuid4());
                $patient->setId($fhirId);
                $lines[] = json_encode($patient);
            }
            $data = implode($lines, '\n');

            $mimeType = "application/fhir+ndjson";
            $data = "{}";
            $higherLevelPath = "";
            $pathDepth = 1;
            $owner = 0;  // userID
            $result = $document->createDocument($folder, $categoryId, $fullPath, $mimeType, $data, $higherLevelPath, $pathDepth, $owner);
            if ($result === '') {
                $ouputResult[] = [
                    'url' => $this->request->getApiBaseFullUrl() . '/Document/' . $fileName . '/Binary'
                    ,"type" => $resource
                ];
            }
        }

        $processedJob->setOutput(json_encode($ouputResult));
        $processedJob->setErrors(json_encode($errorResult));
        $processedJob->setStatus(ExportJob::STATUS_COMPLETED);

        return $this->updateJob($processedJob);
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
     * @return bool true if the resource can be exported, false otherwise.
     */
    private function isValidResource($resource)
    {
        $resourceRegistry = $this->getExportServiceRegistry();
        $resources = array_keys($resourceRegistry);
        return array_search($resource, $resources) !== false;
    }

    /**
     * Return's the list of resources to be exported for the given request.  If the initial resources are empty it
     * returns all the resources possible for the system.
     * @param array $resources
     * @return array
     */
    private function getResourcesForRequest($resources = array())
    {
        $registry = $this->getExportServiceRegistry();
        $validResources = array_keys($registry);
        if (empty($resources)) {
            return $validResources;
        }
        return $resources;
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
        $serviceLocator = new FhirExportServiceLocator($this->request->getRestConfig());
        $this->resourceRegistry = $serviceLocator->findExportServices();
        return $this->resourceRegistry;
    }

    /**
     * Return the fully populated export job for the given client and user.
     * @param $jobId The unique identifier for the job
     * @param $clientId The api client the job belongs to
     * @param $userId The user that created the job request
     * @return ExportJob
     * @throws \InvalidArgumentException if the $jobId, $clientId, or $userId is invalid
     */
    private function getJobForId($jobId, $clientId, $userId)
    {
        // TODO: @adunsulag when we have the client tied to a system user for Credentials grant we will attach it here.
        $sql = "SELECT `uuid`, `start_time`, `resource_include_time`, `output_format`, `resources`, "
            . "`client_id`, `user_id`, `access_token_id`, `status`, `request_uri`, `output`, `errors` "
            . "FROM `export_job` WHERE `uuid` = ? AND `client_id`=? AND `user_id` = ? ";

        $params = [$jobId, $clientId, $userId];
        $ret = sqlQueryNoLog($sql, $params);

        if (empty($ret)) {
            $this->logger->error(
                "FhirExportRestController->getJobForId() failed to find job",
                ['jobId' => $jobId, 'sql' => $sql, 'params' => $params]
            );
            throw new \InvalidArgumentException("Export Job with jobId '" . $jobId . "' does not exist");
        }

        $this->logger->debug("FhirExportRestController->getJobForId() ", ['jobId' => $jobId, 'dbResult' => $ret]);

        $job = new ExportJob();
        $job->setId($jobId);
        $job->setStartTime(\DateTime::createFromFormat(self::DATETIME_FORMAT, $ret['start_time']));
        $job->setResourceIncludeTime(\DateTime::createFromFormat(self::DATETIME_FORMAT, $ret['resource_include_time']));
        $job->setOutputFormat($ret['output_format']);
        $job->setResources($ret['resources']);
        $job->setClientId($ret['client_id']);
        $job->setUserId($ret['user_id']);
        $job->setAccessTokenId($ret['access_token_id']);
        $job->setStatus($ret['status']);
        $job->setRequestURI($ret['request_uri']);
        $job->setOutput($ret['output']);
        $job->setErrors($ret['errors']);
        return $job;
    }

    /**
     * Given an export job, save it to the database
     * @param ExportJob $job The job to save
     * @return ExportJob the saved job
     * @throws \RuntimeException if the job fails to save
     */
    private function createJobRequest(ExportJob $job)
    {
        // we will generate a UUID here, if we ever want the db to do that we would accomplish that here...
        $job->setId(Uuid::uuid4());

        $sql = "INSERT INTO `export_job`(`uuid`, `start_time`, `resource_include_time`, `output_format`, `resources`, "
            . "`client_id`, `user_id`, `access_token_id`, `status`, `request_uri`) "
            . " VALUES (?,?,?,?,?,?,?,?,?,?)";

        $startTime = $job->getStartTime()->format(self::DATETIME_FORMAT);
        if ($job->getResourceIncludeTime() instanceof \DateTime) {
            $resourceIncludeTime = $job->getResourceIncludeTime()->format(self::DATETIME_FORMAT);
        } else {
            $resourceIncludeTime = null;
        }
        $params = [$job->getId(), $startTime, $resourceIncludeTime
            , $job->getOutputFormat(), $job->getResourcesString(), $job->getClientId(), $job->getUserId()
            , $job->getAccessTokenId(), $job->getStatus(), $job->getRequestURI()];
        $ret = sqlQueryNoLog($sql, $params);
        if (!empty($ret)) {
            $this->logger->error("Failed to save ExportJob", ['ret' => $ret, 'sql' => $sql, 'params' => $params, 'sqlError' => getSqlLastError()]);
            throw new \RuntimeException("Failed to save ExportJob");
        }
        return $job;
    }

    /**
     * Given an export job save its updated properties to the database and return the updated job.
     * @param ExportJob $job the job to save
     * @return ExportJob the updated job
     * @throws \RuntimeException if the job fails to save
     */
    private function updateJob(ExportJob $job)
    {
        $sql = "UPDATE export_job SET `output`=?, `errors`=?, `status`=? WHERE uuid = ?";
        $params = [$job->getOutput(), $job->getErrors(), $job->getStatus(), $job->getId()];
        $ret = sqlQueryNoLog($sql, $params);
        if (!empty($ret)) {
            $this->logger->error("Failed to save ExportJob", ['sql' => $sql, 'params' => $params, 'sqlError' => getSqlLastError()]);
            throw new \RuntimeException("Failed to save ExportJob with updated output,errors, & status");
        }
        return $job;
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
}
