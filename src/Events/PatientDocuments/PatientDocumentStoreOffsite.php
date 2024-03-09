<?php

/**
 * PatientDocumentEvents
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2024 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDocuments;

use Symfony\Contracts\EventDispatcher\Event;

class PatientDocumentStoreOffsite extends Event
{
    const REMOTE_STORAGE_LOCATION = 'documents.remote.storage.location';
    private mixed $data;
    private string $remoteFileName;
    private string $mimeType;
    private string $category;
    private string $patientId;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function getData()
    {
        return $this->data;
    }

    public function setRemoteFileName(string $filename): void
    {
        $this->remoteFileName = $filename;
    }

    public function getRemoteFileName(): string
    {
        return $this->remoteFileName;
    }

    public function setRemoteMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getRemoteMimeType(): string
    {
        return $this->mimeType;
    }
    public function setRemoteCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getRemoteCategory(): string
    {
        return $this->category;
    }

    public function setPatientId(string $patientId): void
    {
        $this->patientId = $patientId;
    }
    public function getPatientId(): string
    {
        return $this->patientId;
    }

}
