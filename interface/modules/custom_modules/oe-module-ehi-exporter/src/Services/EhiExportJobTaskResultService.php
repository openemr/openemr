<?php

namespace OpenEMR\Modules\EhiExporter\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJobTask;
use OpenEMR\Services\BaseService;

class EhiExportJobTaskResultService extends BaseService
{
    const TABLE_NAME = "ehi_export_job_task_result";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function clearResultsForJob($jobTask)
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE ehi_export_task_id = ? ";
        $bind = [$jobTask->ehi_task_id];
        QueryUtils::sqlStatementThrowException($sql, $bind);
    }

    public function getResultDataForJob(EhiExportJobTask $task, string $tableName)
    {

        return QueryUtils::fetchSingleValue(
            "SELECT result_data FROM " . self::TABLE_NAME
            . " WHERE ehi_export_task_id = ? AND table_name = ? ",
            'result_data',
            [$task->getId(), $tableName]
        );
    }

    public function insertResult(EhiExportJobTask $jobTask, string $tableName, string &$dataContents)
    {
        $sql = "INSERT INTO " . self::TABLE_NAME . " (`ehi_export_task_id`,`table_name`,`result_data`)"
        . " VALUES (?,?,?) ";
        $bind = [
            $jobTask->ehi_task_id
            ,$tableName
            ,$dataContents
        ];
        QueryUtils::startTransaction();
        try {
            $insertId = QueryUtils::sqlInsert($sql, $bind);
            QueryUtils::commitTransaction();
        } catch (\Exception $exception) {
            QueryUtils::rollbackTransaction();
            // roll it up
            throw $exception;
        }
        return $jobTask;
    }
}
