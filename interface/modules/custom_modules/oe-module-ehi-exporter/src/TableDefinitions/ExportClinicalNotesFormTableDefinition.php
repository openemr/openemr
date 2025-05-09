<?php

/**
 * Export table definition for the form_clinic_note table.  Handles the custom query for exporting
 * this table since the table does not have a foreign key to the patient_data table like the other form
 * tables do and instead must be mapped through the forms table.
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

class ExportClinicalNotesFormTableDefinition extends ExportTableDefinition
{
    const TABLE_NAME = 'form_clinic_note';
    const FORM_DIR = "clinic_note";

    public function getRecords()
    {
        $selectQuery = $this->getSelectClause(); // make sure we only grab the clauses we allow

        $patientPids = $this->getHashmapForKey('pid');
        $patientIdsCount = count($patientPids);
        $sql = "SELECT $selectQuery FROM `" . self::TABLE_NAME . "` WHERE `" . self::TABLE_NAME
            . "`.`id` IN ( SELECT `forms`.`form_id` FROM `forms` WHERE `formdir`='" . self::FORM_DIR . "' AND `forms`.`pid` IN ("
            . str_repeat("?, ", $patientIdsCount - 1) . "? ) )";
        $records = QueryUtils::fetchRecords($sql, $patientPids);
        return $records;
    }
}
