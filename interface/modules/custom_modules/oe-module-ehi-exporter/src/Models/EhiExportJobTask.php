<?php

/**
 * Handles the representation of a foreign key definition for a table.  This is used to
 * handle the retrieval of the foreign key values for a table as well as the local key values.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\Models;

use OpenEMR\Modules\EhiExporter\Models\ExportResult;

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

    public function getJSON()
    {
        $data = [
            'status' => $this->status
            , 'taskId' => $this->ehi_task_id
            , 'includePatientDocuments' => false
        ];
        if (isset($this->ehiExportJob)) {
            $data['includePatientDocuments'] = $this->ehiExportJob->include_patient_documents;
        }
        if (isset($this->exportedResult)) {
            // so we can update progress on the client side
            $data['exportedResult'] = $this->exportedResult;
        }
        if ($this->status == 'completed') {
            $data['hashAlgoTitle'] = $this->document->get_hash_algo_title();
            $data['hash'] = $this->document->get_hash();
            $data['downloadLink'] = $this->exportedResult->downloadLink;
            $data['downloadName'] = $this->document->get_name();
        } else if ($this->status == 'failed') {
            $data['errorMessage'] = $this->error_message;
        }
        return $data;
    }
}
