<?php

/**
 * ExportCannotEncodeException thrown when the system exporter cannot convert a resource into a format that can be
 * converted.  Current encoding at this time is ndjson format.  So if the system cannot convert a fhir resource into
 * that encoding, this exception is thrown.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\Export;

class ExportCannotEncodeException extends ExportException
{
}
