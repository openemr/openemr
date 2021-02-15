<?php

/**
 * ExportException thrown when the system experiences an error during the export operations.  It tracks the last resource
 * identifier that was successfully exported so the system can attempt to retry or resume operation at a later point
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\Export;

use Throwable;

class ExportException extends \Exception
{
    /**
     * @var string The last FHIR resource id that was exported by the system.
     */
    private $lastExportedId;

    public function __construct($message = "", $code = 0, $lastExportedId = null, Throwable $previous = null)
    {
        $this->lastExportedId = $lastExportedId;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string The last FHIR resource id that was exported by the system.
     */
    public function getLastExportedId(): string
    {
        return $this->lastExportedId;
    }
}
