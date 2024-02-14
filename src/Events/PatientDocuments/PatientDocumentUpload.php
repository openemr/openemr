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

class PatientDocumentUpload extends Event
{
    const REMOTE_STORAGE_LOCATION = 'documents.remote.storage.location';
    private mixed $data;
    private string $remoteFileName;

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
}
