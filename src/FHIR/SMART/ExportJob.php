<?php

/**
 * ExportJob represents a FHIR export job as part of the bulk on fhir specification.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

use http\Exception\InvalidArgumentException;

class ExportJob
{
    const STATUS_REPORT_PREFIX = '/fhir/$bulkdata-status?job=';

    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    const ALLOWED_STATII = [self::STATUS_PROCESSING, self::STATUS_COMPLETED];

    const OUTPUT_FORMAT_FHIR_NDJSON = "application/fhir+ndjson";
    const OUTPUT_FORMAT_APPLICATION_NDJSON = "application/ndjson";
    const OUTPUT_FORMAT_NDJSON = "application/ndjson";
    const ALLOWED_OUTPUT_FORMATS = [self::OUTPUT_FORMAT_FHIR_NDJSON, self::OUTPUT_FORMAT_APPLICATION_NDJSON, self::OUTPUT_FORMAT_NDJSON];

    /**
     * @var string The unique id of the export job.
     */
    private $id;

    /**
     * @var \DateTime The time that the export job was created.
     */
    private $startTime;

    /**
     * Resources will be included in the export if their state has changed after the supplied time
     * (e.g. if Resource.meta.lastUpdated is later than the supplied
     * @var \DateTime
     */
    private $resourceIncludeTime;

    /**
     * The format to send the resources out in
     * @var string
     */
    private $outputFormat;

    /**
     * @var array[string] List of resources that will be exported as part of this job
     */
    private $resources;

    /**
     * @var string The id of the client starting the export job
     */
    private $clientId;

    /**
     * @var string The id of the user starting the export job
     */
    private $userId;

    /**
     * @var string The original request URI that started this export
     */
    private $requestURI;

    /**
     * @var string The job output response to be sent back on a export status check
     */
    private $output;

    /**
     * @var string Holds any errors for resources that failed to generate to be sent back on a status check
     */
    private $errors;

    /**
     * @var string At what stage the export is currently in, either processing or completed.
     */
    private $status;

    /**
     * @var string The access token of the original request that started this export job
     */
    private $accessTokenId;

    /**
     * @var string The base url of the api server
     */
    private $apiBaseUrl;

    public function __construct()
    {
        $this->setStatus(self::STATUS_PROCESSING);
        $this->setStartTime(new \DateTime());
        $this->setOutputFormat(self::OUTPUT_FORMAT_FHIR_NDJSON);
        $this->resources = [];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    /**
     * @param \DateTime $startTime
     */
    public function setStartTime(\DateTime $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return \DateTime
     */
    public function getResourceIncludeTime(): \DateTime
    {
        return $this->resourceIncludeTime;
    }

    /**
     * @param \DateTime $resourceIncludeTime
     */
    public function setResourceIncludeTime(\DateTime $resourceIncludeTime): void
    {
        $this->resourceIncludeTime = $resourceIncludeTime;
    }

    /**
     * @return string
     */
    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    /**
     * @param string $outputFormat
     */
    public function setOutputFormat(string $outputFormat): void
    {
        if (array_search($outputFormat, self::ALLOWED_OUTPUT_FORMATS) === false) {
            throw new \InvalidArgumentException("outputFormat is invalid");
        }
        $this->outputFormat = $outputFormat;
    }

    /**
     * @return array
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @param array $resources
     */
    public function setResources($resources): void
    {
        if (\is_string($resources)) {
            $this->resources = explode(",", $resources);
        } else if (\is_array($resources)) {
            $this->resources = $resources;
        } else {
            throw new InvalidArgumentException("Resources must be a valid string or array");
        }
    }

    public function getResourcesString()
    {
        return implode(",", $this->resources);
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getRequestURI(): string
    {
        return $this->requestURI;
    }

    /**
     * @param string $requestURI
     */
    public function setRequestURI(string $requestURI): void
    {
        $this->requestURI = $requestURI;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getErrors(): string
    {
        return $this->errors;
    }

    /**
     * @param string $errors
     */
    public function setErrors(string $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        if (array_search($status, self::ALLOWED_STATII) === false) {
            throw new \InvalidArgumentException("status is invalid");
        }
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getAccessTokenId(): string
    {
        return $this->accessTokenId;
    }

    /**
     * @param string $accessTokenId
     */
    public function setAccessTokenId(string $accessTokenId): void
    {
        $this->accessTokenId = $accessTokenId;
    }

    public function setApiBaseUrl($baseUrl)
    {
        $this->apiBaseUrl = $baseUrl;
    }

    public function getApiBaseUrl()
    {
        return $this->apiBaseUrl;
    }

    /**
     * @return string
     */
    public function getStatusReportURL()
    {
        $baseUrl = $this->getApiBaseUrl() ?? "";
        return $baseUrl . self::STATUS_REPORT_PREFIX . $this->getId();
    }

    public function isComplete()
    {
        return $this->getStatus() === self::STATUS_COMPLETED;
    }
}
