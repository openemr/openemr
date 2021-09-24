<?php

/**
 * ExportJob.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * ExportJob represents a FHIR export job as part of the bulk on fhir specification.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\Export;

use http\Exception\InvalidArgumentException;
use OpenEMR\Common\Uuid\UuidRegistry;

class ExportJob
{
    /**
     * The name of the OpenEMR table record this record belongs to.
     */
    const TABLE_NAME = "ExportJob";

    const STATUS_REPORT_PREFIX = '/fhir/$bulkdata-status?job=';

    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    const ALLOWED_STATII = [self::STATUS_PROCESSING, self::STATUS_COMPLETED];

    const OUTPUT_FORMAT_FHIR_NDJSON = "application/fhir+ndjson";
    const OUTPUT_FORMAT_APPLICATION_NDJSON = "application/ndjson";
    const OUTPUT_FORMAT_NDJSON = "application/ndjson";
    const ALLOWED_OUTPUT_FORMATS = [self::OUTPUT_FORMAT_FHIR_NDJSON, self::OUTPUT_FORMAT_APPLICATION_NDJSON, self::OUTPUT_FORMAT_NDJSON];

    const EXPORT_OPERATION_SYSTEM = 'System';
    const EXPORT_OPERATION_GROUP = 'Group';
    const EXPORT_OPERATION_PATIENT = 'Patient';
    const ALLOWED_EXPORT_OPERATIONS = [self::EXPORT_OPERATION_PATIENT, self::EXPORT_OPERATION_GROUP, self::EXPORT_OPERATION_SYSTEM];

    /**
     * @var int The database id of the export job
     */
    private $id;

    /**
     * @var string The unique id of the export job (binary).  Use $this->getUuidString() to get the string formatted as
     * a uuid4 string that is human readable.
     */
    private $uuid;

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

    /**
     * @var string The Group resource identifier for the system operation we are exporting.
     */
    private $groupId;

    /**
     * @var string the operation type that this export is.  Allowed types are in self::ALLOWED_EXPORT_OPERATIONS
     */
    private $exportType;

    /**
     * @var string[] The specific patient uuids to export
     */
    private $patientUuidsToExport;

    public function __construct()
    {
        $this->setStatus(self::STATUS_PROCESSING);
        $this->setStartTime(new \DateTime());
        $this->setOutputFormat(self::OUTPUT_FORMAT_FHIR_NDJSON);
        $this->resources = [];
        $this->setExportType(self::EXPORT_OPERATION_GROUP);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string binary uuid value
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getUuidString(): string
    {
        $uuid = $this->getUuid();
        if (!empty($uuid)) {
            return UuidRegistry::uuidToString($uuid);
        }
        return $uuid;
    }

    /**
     * @param string $id
     */
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
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
        return $baseUrl . self::STATUS_REPORT_PREFIX . $this->getUuidString();
    }

    public function isComplete()
    {
        return $this->getStatus() === self::STATUS_COMPLETED;
    }

    /**
     * @return string
     */
    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     */
    public function setGroupId(?string $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return string
     */
    public function getExportType(): string
    {
        return $this->exportType;
    }

    /**
     * @param string $exportType
     */
    public function setExportType(string $exportType): void
    {
        if (array_search($exportType, self::ALLOWED_EXPORT_OPERATIONS) === false) {
            throw new \InvalidArgumentException("exportType is invalid");
        }
        $this->exportType = $exportType;
    }

    /**
     * @return string[]
     */
    public function getPatientUuidsToExport(): array
    {
        return $this->patientUuidsToExport;
    }

    /**
     * @param string[] $patientUuidsToExport
     */
    public function setPatientUuidsToExport(?array $patientUuidsToExport): void
    {
        $this->patientUuidsToExport = $patientUuidsToExport;
    }
}
