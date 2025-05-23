<?php

/**
 * Export table definition for the onsite_messages table.  Handles the custom query for exporting
 * this table since the table does not have a direct foreign key to the patient_data table.  The table
 * has a denormalized relationship using the recip_ids for the intended recipients of the messages.
 * This class handles the database queries and data sanitizations (since there could be other potential patient message
 * recipients that are NOT included in the export that must be filtered out).
 *
 * The class retrieves messages where the patient is either the recipient, or the patient is the sender of the message.
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

class ExportOnsiteMessagesTableDefinition extends ExportTableDefinition
{
    const TABLE_NAME = 'onsite_messages';

    public function getRecords()
    {
        $selectQuery = $this->getSelectClause(); // make sure we only grab the clauses we allow

        $patientPids = $this->getHashmapForKey('pid');

        // first we will grab all the recipient ids.... because its all denormalized we have to use like clauses which is
        // an absolute mess... so we'll need to double check to make sure the pids are correct
        // make a hashmap of the pids so we can cleanup the records in the messages... what a pain
        $likeClause = str_repeat("recip_id LIKE ? OR ", count($patientPids) - 1) . "recip_id LIKE ?"; // create the like clause

        // recip_id is a json array of pids if the message originates from a user in the users table
        // recip_id is a josn array of usernames if the message originates from a patient in the patient_data table
        $query = "SELECT $selectQuery FROM onsite_messages WHERE $likeClause";
        $bindParams = array_map(function ($pid) {
            return "%\"$pid\"%";
        }, $patientPids);
        $records = QueryUtils::fetchRecords($query, $bindParams);
        $resultRecords = [];
        if (!empty($records)) {
            $patientPidsHash = array_combine($patientPids, $patientPids);
            foreach ($records as $record) {
                $recipIdDecoded = json_decode($record['recip_id'], true);
                if (is_array($recipIdDecoded)) {
                    // don't think I need the string piece here.
//                    $recipIdDecoded = array_map(function($pid) {
//                        return (string) $pid;
//                    }, $recipIdDecoded);
                    $recipIdDecoded = array_combine($recipIdDecoded, $recipIdDecoded);
                    $recipIdDecoded = array_intersect_key($recipIdDecoded, $patientPidsHash);
                    $recipIdDecoded = array_values($recipIdDecoded);
                    $record['recip_id'] = json_encode($recipIdDecoded);
                    $resultRecords[] = $record;
                }
            }
        }

        // if the patient is the sender... the recipient_ids will then be user_ids...
        // note if its a provider sending the message, the sender_id is the username
        $senderSql = "SELECT $selectQuery FROM onsite_messages WHERE sender_id IN (" . str_repeat('?,', count($patientPids) - 1) . "?)";
        $senderRecords = QueryUtils::fetchRecords($senderSql, $patientPids);
        $resultRecords = array_merge($resultRecords, $senderRecords);

        // TODO: @adunsulag if for some reason the user ends up not being collected in the export somewhere else, we will need to
        // make sure they get added in a special way to the keys.

        return $resultRecords;
    }
}
