<?php

/**
 * Represents a specific table definition result as to how many records were exported for a given table.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\Models;

class ExportTableResult
{
    public $count;
    public $tableName;

    public function fromJSON(array $exportedTable)
    {
        $this->tableName = $exportedTable['tableName'] ?? '';
        $this->count = $exportedTable['count'] ?? 0;
    }
}
