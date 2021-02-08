<?php

/**
 * FhirExportJobService handles the database create, read, update, and delete database operations for an ExportJob
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\Export\ExportJob;
use Psr\Log\LoggerInterface;

class FhirExportJobService
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new SystemLogger();
    }
    // TODO: @adunsulag is there another place in the system that has our standard datetime constants?
    /**
     * The date format to use for our DateTime values
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Return the fully populated export job for the given client and user.
     * @param $jobUuidString The unique identifier for the job in string format
     * @param $clientId The api client the job belongs to
     * @param $userId The user that created the job request
     * @return ExportJob
     * @throws \InvalidArgumentException if the $jobId, $clientId, or $userId is invalid
     */
    public function getJobForUuid($jobUuidString, $clientId, $userId)
    {
        $sql = "SELECT `id`, `uuid`, `start_time`, `resource_include_time`, `output_format`, `resources`, "
            . "`client_id`, `user_id`, `access_token_id`, `status`, `request_uri`, `output`, `errors` "
            . "FROM `export_job` WHERE `uuid` = ? AND `client_id`=? AND `user_id` = ? ";

        $jobUuid = UuidRegistry::uuidToBytes($jobUuidString);
        $params = [$jobUuid, $clientId, $userId];
        $ret = sqlQueryNoLog($sql, $params);

        if (empty($ret)) {
            $this->logger->error(
                "FhirExportRestController->getJobForId() failed to find job",
                ['jobUuid' => $jobUuidString, 'sql' => $sql, 'params' => $params]
            );
            throw new \InvalidArgumentException("Export Job with jobId '" . $jobUuidString . "' does not exist");
        }

        $this->logger->debug("FhirExportRestController->getJobForId() ", ['jobId' => $jobUuidString, 'dbResult' => $ret]);

        $job = new ExportJob();
        $job->setId($ret['id']);
        $job->setUuid($jobUuid);
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
     * @throws \RuntimeException if the job id fails to retrieve
     * @throws SqlQueryException If there is a sql error saving the request
     */
    public function createJobRequest(ExportJob $job)
    {
        // we will generate a UUID here, if we ever want the db to do that we would accomplish that here...
        $binaryUuid = (new UuidRegistry(['table_name' => 'export_job']))->createUuid();
        $job->setUuid($binaryUuid);

        $sql = "INSERT INTO `export_job`(`uuid`, `start_time`, `resource_include_time`, `output_format`, `resources`, "
            . "`client_id`, `user_id`, `access_token_id`, `status`, `request_uri`) "
            . " VALUES (?,?,?,?,?,?,?,?,?,?)";

        $startTime = $job->getStartTime()->format(self::DATETIME_FORMAT);
        if ($job->getResourceIncludeTime() instanceof \DateTime) {
            $resourceIncludeTime = $job->getResourceIncludeTime()->format(self::DATETIME_FORMAT);
        } else {
            $resourceIncludeTime = null;
        }
        $params = [$job->getUuid(), $startTime, $resourceIncludeTime
            , $job->getOutputFormat(), $job->getResourcesString(), $job->getClientId(), $job->getUserId()
            , $job->getAccessTokenId(), $job->getStatus(), $job->getRequestURI()];

        sqlStatementThrowException($sql, $params);
        $id = sqlGetLastInsertId();
        if (!is_int($id)) {
            $params[0] = $job->getUuidString(); // so we don't spit out the binary value
            $this->logger->error("Failed to save ExportJob", ['ret' => $id, 'sql' => $sql, 'params' => $params, 'sqlError' => getSqlLastError()]);
            throw new \RuntimeException("Failed to save ExportJob");
        } else {
            $job->setId($id);
        }
        return $job;
    }

    /**
     * Given an export job, delete the job from the database
     * @param ExportJob $job The job to save
     * @return ExportJob the saved job
     * @throws \RuntimeException if the job fails to save
     */
    public function deleteJob(ExportJob $job)
    {

        $sql = "DELETE FROM `export_job` WHERE `uuid` = ? AND `client_id` = ? AND `user_id` = ?";
        $params = [$job->getUuid(), $job->getClientId(), $job->getUserId()];

        $ret = sqlStatement($sql, $params);
        if ($ret === false) {
            $params[0] = $job->getUuidString(); // so we don't spit out the binary value
            $this->logger->error("Failed to delete ExportJob", ['ret' => $ret, 'sql' => $sql, 'params' => $params, 'sqlError' => getSqlLastError()]);
            throw new \RuntimeException("Failed to delete ExportJob");
        }
        return $ret !== false;
    }

    /**
     * Given an export job save the updated status,output, and errors property to the database and return the updated job.
     * @param ExportJob $job the job to save
     * @return ExportJob the updated job
     * @throws \RuntimeException if the job fails to save
     */
    public function updateJob(ExportJob $job)
    {
        $sql = "UPDATE export_job SET `output`=?, `errors`=?, `status`=? WHERE uuid = ?";
        $params = [$job->getOutput(), $job->getErrors(), $job->getStatus(), $job->getUuid()];
        $ret = sqlQueryNoLog($sql, $params);
        if (!empty($ret)) {
            // replace our UUID param so we don't spit out binary
            $params[2] = $job->getUuidString();
            $this->logger->error("Failed to save ExportJob", ['sql' => $sql, 'params' => $params, 'sqlError' => getSqlLastError()]);
            throw new \RuntimeException("Failed to save ExportJob with updated output,errors, & status");
        }
        return $job;
    }
}
