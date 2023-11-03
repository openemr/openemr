<?php

/**
 * Export table definition for the openemr_postcalendar_events table that handles the custom
 * therapy groups calendar events.
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

class ExportOpenEmrPostCalendarEventsTableDefinition extends ExportTableDefinition
{
    const TABLE_NAME = 'openemr_postcalendar_events';

    public function getRecords()
    {
        $records = parent::getRecords();

        // now export the calendar events that are linked to the groups
        $selectQuery = $this->getSelectClause();
        $patientPids = $this->getHashmapForKey('pc_pid');
        $patientIdsCount = count($patientPids);
        if ($patientIdsCount > 0) {
            $sql = "SELECT $selectQuery FROM `" . self::TABLE_NAME . "` WHERE `pc_gid` IN (select DISTINCT `group_id` "
                . " FROM `therapy_groups_participants` WHERE `pid` IN ( "
                . str_repeat("?, ", $patientIdsCount - 1) . "? ) )";
            $groupRecords = QueryUtils::fetchRecords($sql, $patientPids);
            $records = array_merge($records, $groupRecords);
        }
        return $records;
    }
}
