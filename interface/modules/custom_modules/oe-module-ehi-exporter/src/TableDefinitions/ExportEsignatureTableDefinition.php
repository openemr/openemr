<?php

/**
 * Export table definition for the esign_signatures table.  Handles the custom query for exporting
 * this table since the table does not have a direct foreign key to the patient_data table. Instead
 * the table must be mapped through the forms table and the form_encounter table.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\TableDefinitions;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportTableDefinition;

class ExportEsignatureTableDefinition extends ExportTableDefinition
{
    const TABLE_NAME = 'esign_signatures';

    public function getRecords()
    {
        $selectQuery = $this->getSelectClause(); // make sure we only grab the clauses we allow

        $patientPids = $this->getHashmapForKey('pid');
        $patientIdsCount = count($patientPids);
        $sql = "SELECT $selectQuery FROM `" . self::TABLE_NAME . "` WHERE `table`='forms' AND `tid` IN (select `id` FROM `forms` WHERE `pid` IN ( "
            . str_repeat("?, ", $patientIdsCount - 1) . "? ) )";
        $records = QueryUtils::fetchRecords($sql, $patientPids);

        $encounterSql = "SELECT $selectQuery FROM `" . self::TABLE_NAME . "` WHERE `table`='form_encounter' AND `tid` IN (select `encounter` FROM `form_encounter` WHERE `pid` IN ( "
            . str_repeat("?, ", $patientIdsCount - 1) . "? ) )";
        $encounterRecords = QueryUtils::fetchRecords($encounterSql, $patientPids);

        $combinedRecords = array_merge($records, $encounterRecords);
        return $combinedRecords;
    }
}
