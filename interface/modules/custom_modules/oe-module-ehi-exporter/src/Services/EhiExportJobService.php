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
        $bind = [
            $job->getStatus()
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
}
