<?php

/**
 * Handles the saving, retriving, and updating of ehi_export_job records.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJob;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJobTask;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class EhiExportJobService extends BaseService
{
    const TABLE_NAME = "ehi_export_job";
    const TABLE_NAME_PATIENT_JOIN_TABLE = "ehi_export_job_patients";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function insert(EhiExportJob $job)
    {
        $sql = "INSERT INTO " . self::TABLE_NAME . " (`uuid`, `user_id`,`status`,`include_patient_documents`, `document_limit_size`) "
        . " VALUES (?,?,?,?, ?) ";
        $bind = [
            $job->uuid
            ,$job->user_id
            ,$job->getStatus()
            ,$job->include_patient_documents ? 1 : 0
            ,$job->getDocumentLimitSize()
        ];
        QueryUtils::startTransaction();
        try {
            $insertId = QueryUtils::sqlInsert($sql, $bind);
            $job->setId($insertId);

            if ($job->hasPatientIds()) {
                $patientIds = $job->getPatientIds();
                $patientJoinSql = "INSERT INTO " . self::TABLE_NAME_PATIENT_JOIN_TABLE . " (`ehi_export_job_id`, `pid`) "
                    . " SELECT ehi_export_job_id,pid FROM " . self::TABLE_NAME . " CROSS JOIN (SELECT pid FROM patient_data WHERE pid IN ( "
                    . str_repeat("?, ", count($patientIds) - 1) . "? ) ) pids WHERE ehi_export_job_id = ? ";
                $joinBind = array_merge($patientIds, [$insertId]);
                QueryUtils::sqlInsert($patientJoinSql, $joinBind);
                QueryUtils::commitTransaction();
            }
        } catch (\Exception $exception) {
            QueryUtils::rollbackTransaction();
            // roll it up
            throw $exception;
        }
        return $job;
    }

    public function update(EhiExportJob $job)
    {
        $sql = "UPDATE " . self::TABLE_NAME . " SET `status`= ?" . ($job->isCompleted() ? ",`completion_date`= NOW() " : "");
        $sql .= " WHERE `ehi_export_job_id` = ? ";
        $bind = [
            $job->getStatus()
            ,$job->getId()
        ];
        QueryUtils::startTransaction();
        try {
            QueryUtils::sqlStatementThrowException($sql, $bind);
            QueryUtils::commitTransaction();
        } catch (\Exception $exception) {
            QueryUtils::rollbackTransaction();
            // roll it up
            throw $exception;
        }
        return $job;
    }

    public function getJobById(?int $ehi_export_job_id, $loadPatients = false): ?EhiExportJob
    {
        $ehiExportJob = null;
        $processingResult = $this->search(['ehi_export_job_id' => $ehi_export_job_id]);
        if ($processingResult->hasData()) {
            $record = ProcessingResult::extractDataArray($processingResult)[0];
            $ehiExportJob = new EhiExportJob();
            $ehiExportJob->setId($record['ehi_export_job_id']);
            $ehiExportJob->setStatus($record['status']);
            $ehiExportJob->setDocumentLimitSize($record['document_limit_size']);
            $ehiExportJob->include_patient_documents = $record['include_patient_documents'] == 1;
            $ehiExportJob->user_id = $record['user_id'];
            $ehiExportJob->uuid = $record['uuid'];

            // now we need to grab all of the patient ids here
            if ($loadPatients) {
                $patientPids = $this->getPatientPidsForJobId($ehiExportJob->getId());
                $ehiExportJob->addPatientIdList($patientPids);
            }
        }
        return $ehiExportJob;
    }

    private function getPatientPidsForJobId(?int $jobId)
    {
        return QueryUtils::fetchTableColumn("SELECT pid FROM " . self::TABLE_NAME_PATIENT_JOIN_TABLE
            . " WHERE ehi_export_job_id = ?", 'pid', [$jobId]);
    }
}
