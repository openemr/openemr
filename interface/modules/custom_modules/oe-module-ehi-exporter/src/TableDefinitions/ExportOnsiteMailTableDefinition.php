<?php

/**
 * Export table definition for the onsite_mail table.  Handles the custom query for exporting
 * this table since the table does not have a direct foreign key to the patient_data table since it works through
 * the patient_access_onsite table via the portal_username which is pulled using the patient pids for the
 * export.
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

class ExportOnsiteMailTableDefinition extends ExportTableDefinition
{
    const TABLE_NAME = 'onsite_mail';
    public function getRecords()
    {
        $selectQuery = $this->getSelectClause(); // make sure we only grab the clauses we allow

        $patientPids = $this->getHashmapForKey('pid');
        // if its a patient originated message the owner and sender_id will be the patient's portal_username
        // from patient_access_onsite.  If it's a provider to patient message, the owner will be the provider's and the
        // recipient_id will be the patient's portal_username
        $query = "SELECT $selectQuery FROM onsite_mail
             WHERE (
                 owner IN (
                    SELECT portal_username FROM patient_access_onsite WHERE pid IN ("
                        . str_repeat('?,', count($patientPids) - 1) . "?
                    )
                 ) AND owner=sender_id
             ) OR (
                 recipient_id IN (
                    SELECT portal_username FROM patient_access_onsite WHERE pid IN ("
                        . str_repeat('?,', count($patientPids) - 1) . "?
                    )
                 ) AND owner=sender_id
            )";
        $resultRecords = QueryUtils::fetchRecords($query, array_merge($patientPids, $patientPids));

        // TODO: @adunsulag if for some reason the user ends up not being collected in the export somewhere else, we will need to
        // make sure the sender_id/recipient_id that reference the users.username table get added.

        return $resultRecords;
    }
}
