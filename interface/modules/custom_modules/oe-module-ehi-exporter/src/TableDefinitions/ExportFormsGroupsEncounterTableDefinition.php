<?php

/**
 * Export table definition for the form_groups_encounter table.  Handles the custom query for exporting
 * this table since the table does not have a direct foreign key to the patient_data table since it works through
 * the therapy group's feature mechanism.  Instead the table must be mapped through the therapy_groups_participants
 * table using the patient pids.
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

class ExportFormsGroupsEncounterTableDefinition extends ExportTableDefinition
{
    const TABLE_NAME = 'form_groups_encounter';

    public function getRecords()
    {
        $selectQuery = $this->getSelectClause(); // make sure we only grab the clauses we allow

        $patientPids = $this->getHashmapForKey('pid');
        $patientIdsCount = count($patientPids);
        $sql = "SELECT $selectQuery FROM `" . self::TABLE_NAME . "` WHERE `group_id` IN (select DISTINCT `group_id` "
        . " FROM `therapy_groups_participants` WHERE `pid` IN ( "
            . str_repeat("?, ", $patientIdsCount - 1) . "? ) )";
        $records = QueryUtils::fetchRecords($sql, $patientPids);
        return $records;
    }
}
