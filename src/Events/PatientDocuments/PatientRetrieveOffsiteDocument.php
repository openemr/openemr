<?php

namespace OpenEMR\Events\PatientDocuments;

use Symfony\Contracts\EventDispatcher\Event;
class PatientRetrieveOffsiteDocument extends Event
{
    const REMOTE_DOCUMENT_LOCATION = 'remote.document.retrieve.location';
    public string $url;
    public function __construct($url)
    {
        $this->url = $url;
    }
}
