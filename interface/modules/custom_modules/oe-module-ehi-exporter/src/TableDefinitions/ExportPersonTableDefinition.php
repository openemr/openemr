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

class ExportPersonTableDefinition extends ExportTableDefinition
{
    const TABLE_NAME = 'person';

    public function getRecords()
    {
        $selectQuery = $this->getSelectClause(); // make sure we only grab the clauses we allow

        // grab the person records linked to the contact records for the contacts
        $contactIds = $this->getContactIds();
        $idsCount = count($contactIds);
        if ($idsCount <= 0) {
            return [];
        }
        $parameters = str_repeat("?, ", $idsCount - 1) . "? ";
        $sql = "SELECT $selectQuery FROM `" . self::TABLE_NAME . "` JOIN contact_relation cr ON " . self::TABLE_NAME
            . ".id = cr.target_id AND cr.target_table='person' WHERE cr.contact_id IN ( "
            . $parameters . " )";
        $records = QueryUtils::fetchRecords($sql, $contactIds);
        return $records;
    }

    private function getContactIds() {
        // instead of hunting down to figure where this is at, for now will just leverage
        //$this->getHashmapForKey('contact_id');
        $patientPids = $this->getHashmapForKey('pid');
        $patientIdsCount = count($patientPids);
        $patientIdParameters = str_repeat("?, ", $patientIdsCount - 1) . "? ";
        $sql = "SELECT id FROM `contact` WHERE `foreign_table_name`='patient_data' AND `foreign_id` IN ( "
            . $patientIdParameters . " )";
        $records = QueryUtils::fetchTableColumn($sql, 'id', $patientPids);

        // grab the contacts linked to the person records for the patients
        // this requires a subquery to get the person ids from the contact_relation table

        $personContactIdsSql = "SELECT id  FROM contact WHERE foreign_table_name='person' AND foreign_id IN ( "
            . " SELECT person.id FROM person JOIN contact_relation cr ON cr.target_table='person' AND cr.target_id = person.id "
            . " WHERE cr.contact_id IN (select contact.id FROM contact WHERE foreign_table_name='patient_data' AND foreign_id IN ( "
            . $patientIdParameters . ") ) ) ";
        $personContactIds = QueryUtils::fetchTableColumn($personContactIdsSql, 'id', $patientPids);

        $combinedRecords = array_merge($records, $personContactIds);
        return $combinedRecords;
    }
}
