<?php

namespace OpenEMR\Modules\EhiExporter\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJobTask;
use OpenEMR\Services\BaseService;

class EhiExportJobTaskService extends BaseService
{
    const TABLE_NAME = "ehi_export_job_tasks";
    const TABLE_NAME_PATIENT_JOIN_TABLE = "ehi_export_job_task_patients";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function insert(EhiExportJobTask $task)
    {
        $sql = "INSERT INTO " . self::TABLE_NAME . " (`ehi_export_job_id`, `status`) "
            . " VALUES (?,?) ";
        $bind = [
            $task->ehi_export_job_id
            , $task->getStatus()
        ];
        QueryUtils::startTransaction();
        try {
            $insertId = QueryUtils::sqlInsert($sql, $bind);
            $task->setId($insertId);

            if ($task->hasPatientIds()) {
                $patientIds = $task->getPatientIds();
                $patientJoinSql = "INSERT INTO " . self::TABLE_NAME_PATIENT_JOIN_TABLE . " (`ehi_task_id`, `pid`) "
                    . " SELECT ehi_task_id,pid FROM " . self::TABLE_NAME . " CROSS JOIN (SELECT pid FROM patient_data WHERE pid IN ( "
                    . str_repeat("?, ", count($patientIds) - 1) . "? ) ) pids WHERE ehi_task_id = ? ";
                $joinBind = array_merge($patientIds, [$insertId]);
                QueryUtils::sqlInsert($patientJoinSql, $joinBind);
                QueryUtils::commitTransaction();
            }
        } catch (\Exception $exception) {
            QueryUtils::rollbackTransaction();
            // roll it up
            throw $exception;
        }
        return $task;
    }

    public function update(EhiExportJobTask $task)
    {
        $sql = "UPDATE " . self::TABLE_NAME . " SET `status`= ?" . ($task->isCompleted() ? ",`completion_date`= NOW() " : "");
        $bind = [
            $task->getStatus()
        ];
        if (isset($task->export_document_id)) {
            $sql .= ",export_document_id= ? ";
            $bind[] = $task->export_document_id;
        } else {
            $sql .= ",export_document_id= NULL ";
        }
        if ($task->getStatus() == "completed") {
            $sql .= ",completion_date= NOW() ";
        }
        if (isset($task->error_message)) {
            $sql .= ",error_message= ? ";
            $bind[] = $task->error_message;
        } else {
            $sql .= ",error_message= NULL ";
        }

        QueryUtils::startTransaction();
        try {
            QueryUtils::sqlStatementThrowException($sql, $bind);
            QueryUtils::commitTransaction();
        } catch (\Exception $exception) {
            QueryUtils::rollbackTransaction();
            // roll it up
            throw $exception;
        }
        return $task;
    }
}
