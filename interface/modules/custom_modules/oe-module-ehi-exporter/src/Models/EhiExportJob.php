<?php

namespace OpenEMR\Modules\EhiExporter\Models;

use OpenEMR\Services\Utils\DateFormatterUtils;
use Ramsey\Uuid\Rfc4122\UuidV4;

class EhiExportJob
{
    /**
     * `ehi_export_job_id` int(11) NOT NULL AUTO_INCREMENT,
    `uuid` BINARY(16) DEFAULT NULL,
    `user_id` BIGINT(20) NOT NULL COMMENT 'FK to users.id - represents the user that started the export process',
    `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completion_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` VARCHAR(20) NOT NULL DEFAULT 'processing' COMMENT 'processing=export in progress, failed=error occurred in one or more tasks, completed=export completed without errors',
     */

    public function __construct()
    {
        $this->ehi_export_job_id = null;
        $this->user_id = $_SESSION['authUserID'];
        $this->status = "processing";
        $this->creation_date = date("Y-m-d H:i:s");
        $this->completion_date = date("Y-m-d H:i:s");
        $this->pids = [];
        $this->jobTasks = [];
        $this->include_patient_documents = true;
        // 500 * 1024 * 1024 = 500MB
        $this->document_limit_size = 524288000;
    }

    private ?int $ehi_export_job_id;

    public string $uuid;

    public int $user_id;
    /**
     * @var "processing"|"failed"|"completed"
     */
    private string $status;

    public string $creation_date;
    public string $completion_date;

    /**
     * @var int[]
     */
    private array $pids;

    public bool $include_patient_documents;

    /**
     * @var EhiExportJobTask[]
     */
    private array $jobTasks;

    /**
     * @var int The maximum size in bytes that a document zip file can be for an export.  The default is 500
     */
    private int $document_limit_size;

    public function getDocumentLimitSize()
    {
        return $this->document_limit_size;
    }

    public function setDocumentLimitSize(int $size)
    {
        $this->document_limit_size = $size;
    }

    public function setId(int $id)
    {
        $this->ehi_export_job_id = $id;
    }

    public function getId()
    {
        return $this->ehi_export_job_id;
    }

    public function isCompleted()
    {
        return $this->status == 'completed';
    }

    public function setStatus(string $status)
    {
        if (!in_array($status, ['processing', 'completed', 'failed'])) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getJobTasks(): array
    {
        return $this->jobTasks;
    }

    public function addJobTask(EhiExportJobTask $task)
    {
        $this->jobTasks[] = $task;
    }

    public function addPatientId($pid)
    {
        $this->pids[] = $pid;
    }
    public function addPatientIdList(array $pids)
    {
        $this->pids = array_map('intval', $pids); // make sure we don't get invalid pids here
    }
    public function getPatientIds()
    {
        return $this->pids;
    }

    public function hasPatientIds()
    {
        return !empty($this->pids);
    }
}
