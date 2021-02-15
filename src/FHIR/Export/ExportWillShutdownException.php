<?php

/**
 * ExportWillShutdownException thrown when the system exporter is about to shut down and can no longer append any more
 * FHIR resources.  The shutdown exception will contain the last known resource that was written so that exporting can
 * resume if needed.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\Export;

class ExportWillShutdownException extends ExportException
{
}
