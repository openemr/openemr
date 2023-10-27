<?php

namespace OpenEMR\Modules\EhiExporter\Models;

use OpenEMR\Modules\EhiExporter\ExportResult;

class EhiExportJobTask
{
    public ?EhiExportJob $ehiExportJob;

    public ?\Document $document;

    public ?int $ehi_task_id;
    public ?int $ehi_export_job_id;
    public string $creation_date;
    public ?string $completion_date;

    /**
     * @var "pending"|"processing"|"failed"|"completed"
     */
    private $status;

    public ?int $export_document_id;
    public ?string $error_message;
    private array $pids;

    public ?ExportResult $exportedResult;


    public function __construct()
    {
        $this->creation_date = date("Y-m-d H:i:s");
        $this->setStatus('pending');
        $this->export_document_id = null;
        $this->document = null;
        $this->error_message = null;
        $this->pids = [];
        $this->status = "pending";
        $this->ehiExportJob = null;
        $this->exportedResult = null;
    }

    public function setId(int $id)
    {
        $this->ehi_task_id = $id;
    }

    public function getId()
    {
        return $this->ehi_task_id;
    }

    public function isCompleted()
    {
        return $this->status == 'completed';
    }

    public function setStatus(string $status)
    {
        if (!in_array($status, ['pending', 'processing', 'completed', 'failed'])) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
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
