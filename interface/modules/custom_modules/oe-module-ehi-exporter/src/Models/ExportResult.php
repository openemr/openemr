<?php

/**
 * Represents a cumulative result of an export operation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\Models;

use OpenEMR\Modules\EhiExporter\Models\ExportTableResult;

class ExportResult
{
    public $downloadLink;
    /**
     * @var ExportTableResult[]
     */
    public $exportedTables;

    public $exportedDocumentCount = 0;
}
