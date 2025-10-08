<?php

/**
 * PatientRetrieveOffsiteDocument
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2024 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDocuments;

use Symfony\Contracts\EventDispatcher\Event;
use Document;

class PatientRetrieveOffsiteDocument extends Event
{
    const REMOTE_DOCUMENT_LOCATION = 'remote.document.retrieve.location';
    private $offsiteUrl;
    public function __construct(private string $url, private ?Document $document = null)
    {
    }

    /**
     * Returns the OpenEMR document class that represents this document in the file system
     * @return Document|null
     */
    public function getOpenEMRDocument(): ?Document
    {
        return $this->document;
    }

    public function getOpenEMRDocumentUrl(): string
    {
        return $this->url;
    }

    public function setOffsiteUrl(string $offsiteUrl): void
    {
        $this->offsiteUrl = $offsiteUrl;
    }

    public function getOffsiteUrl()
    {
        return $this->offsiteUrl;
    }
}
