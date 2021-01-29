<?php

/**
 * FhirExportRestController.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * FhirExportRestControllertroller.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

class FhirExportRestController
{
    public function sendExportHeaders()
    {
        // @see https://hl7.org/fhir/uv/bulkdata/export/index.html#headers
        header("Accept: application/fhir+json");
        header("Prefer: respond-async");
    }

    public function processExport($exportParams, $exportType)
    {
        $outputFormat = $_GET['_outputFormat'] ?? 'ndjson';
        $since = $_GET['_since'] ?? time(0); // since epoch time
        $type = $_GET['type'] ?? '';

        (new \OpenEMR\Common\Logging\SystemLogger())->debug("Patient export call made", [
            '_outputFormat' => $outputFormat,
            '_since' => $since,
            '_type' => $type
        ]);
    }
}
