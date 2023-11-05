<?php

/**
 * Handles the saving, retriving, and updating of ehi_export_job_tasks records.
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
use OpenEMR\Modules\EhiExporter\Models\EhiExportJobTask;
use OpenEMR\Modules\EhiExporter\Models\ExportResult;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class EhiExportJobTaskService extends BaseService
{
    const TABLE_NAME = "ehi_export_job_tasks";
    const TABLE_NAME_PATIENT_JOIN_TABLE = "ehi_export_job_task_patients";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getTaskFromId(int $taskId, bool $loadPatients = true): ?EhiExportJobTask
    {
        $ehiExportJobTask = null;
        $processingResult = $this->search(['ehi_task_id' => $taskId]);
        if ($processingResult->hasData()) {
            $taskRecord = ProcessingResult::extractDataArray($processingResult)[0];
            $ehiExportJobTask = new EhiExportJobTask();
            $ehiExportJobTask->ehi_task_id = $taskRecord['ehi_task_id'];
            $ehiExportJobTask->ehi_export_job_id = $taskRecord['ehi_export_job_id'];
            $ehiExportJobTask->export_document_id = $taskRecord['export_document_id'];
            $ehiExportJobTask->error_message = $taskRecord['error_message'];
            $ehiExportJobTask->setStatus($taskRecord['status']);
            if (isset($ehiExportJobTask->export_document_id)) {
                $ehiExportJobTask->document = new \Document($ehiExportJobTask->export_document_id);
            }

            if (isset($taskRecord['exported_result'])) {
                $exportedResult = json_decode($taskRecord['exported_result'], true);
                $exportResult = new ExportResult();
                $exportResult->fromJSON($exportedResult);
                $ehiExportJobTask->exportedResult = $exportResult;
            }
            if ($loadPatients) {
                $patientPids = $this->getPatientPidsForJobTaskId($ehiExportJobTask->getId());
                $ehiExportJobTask->addPatientIdList($patientPids);
            }
        }
        return $ehiExportJobTask;
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
        if (isset($task->exportedResult)) {
            $sql .= ",exported_result= ? ";
            $bind[] = json_encode($task->exportedResult);
        } else {
            $sql .= ",exported_result= NULL ";
        }

        $sql .= " WHERE ehi_task_id = ? ";
        $bind[] = $task->getId();

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

    private function getPatientPidsForJobTaskId(?int $taskId)
    {
        return QueryUtils::fetchTableColumn("SELECT pid FROM " . self::TABLE_NAME_PATIENT_JOIN_TABLE
            . " WHERE ehi_task_id = ?", 'pid', [$taskId]);
    }
}
