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

class PatientRetrieveOffsiteDocument extends Event
{
    const REMOTE_DOCUMENT_LOCATION = 'remote.document.retrieve.location';
    private string $url;
    private $offsiteurl;
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function setOffsiteUrl(string $offsitedUrl): void
    {
        $this->offsiteurl = $offsiteUrl;
    }

    public function getOffsiteUrl()
    {
        return $this->offsiteurl;
    }
}
