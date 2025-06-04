<?php

/**
 * Handles the interactions with the patient_history table.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;

class PatientNameHistoryService extends BaseService
{
    public const TABLE_NAME = "patient_history";
    public function __construct()
    {
        // note the constant for this table is PRIVATE and should either be protected or PUBLIC
        parent::__construct(self::TABLE_NAME);
    }

    public function deletePatientNameHistoryById($id)
    {
        $sql = "DELETE FROM patient_history WHERE id = ?";
        return sqlQuery($sql, array($id));
    }

    /**
     * Create a previous patient name history
     * Updates not allowed for this history feature.
     *
     * @param string $pid patient internal id
     * @param array $record array values to insert
     * @return int | false new id or false if name already exist
     */
    public function createPatientNameHistory($pid, $record)
    {
        // we should never be null here but for legacy reasons we are going to default to this
        $createdBy = $_SESSION['authUserID'] ?? null; // we don't let anyone else but the current user be the createdBy
        if ($pid <= 0) {
            return false;
        }
        $insertData = [
            'pid' => $pid,
            'history_type_key' => 'name_history',
            'previous_name_prefix' => $record['previous_name_prefix'],
            'previous_name_first' => $record['previous_name_first'],
            'previous_name_middle' => $record['previous_name_middle'],
            'previous_name_last' => $record['previous_name_last'],
            'previous_name_suffix' => $record['previous_name_suffix'],
            'previous_name_enddate' => $record['previous_name_enddate']
        ];
        $sql = "SELECT pid FROM " . self::TABLE_NAME . " WHERE
            pid = ? AND
            history_type_key = ? AND
            previous_name_prefix = ? AND
            previous_name_first = ? AND
            previous_name_middle = ? AND
            previous_name_last = ? AND
            previous_name_suffix = ? AND
            previous_name_enddate = ?
        ";
        $go_flag = QueryUtils::fetchSingleValue($sql, 'pid', $insertData);
        // return false which calling routine should understand as existing name record
        if (!empty($go_flag)) {
            return false;
        }
        // finish up the insert
        $insertData['created_by'] = $createdBy;
        $insertData['uuid'] = UuidRegistry::getRegistryForTable(self::TABLE_NAME)->createUuid();
        $insert = $this->buildInsertColumns($insertData);
        $sql = "INSERT INTO " . self::TABLE_NAME . " SET " . $insert['set'];

        return QueryUtils::sqlInsert($sql, $insert['bind']);
    }


    public static function formatPreviousName($item)
    {
        if (
            $item['previous_name_enddate'] === '0000-00-00'
            || $item['previous_name_enddate'] === '00/00/0000'
        ) {
            $item['previous_name_enddate'] = '';
        }
        $item['previous_name_enddate'] = oeFormatShortDate($item['previous_name_enddate']);
        $name = ($item['previous_name_prefix'] ? $item['previous_name_prefix'] . " " : "") .
            $item['previous_name_first'] .
            ($item['previous_name_middle'] ? " " . $item['previous_name_middle'] . " " : " ") .
            $item['previous_name_last'] .
            ($item['previous_name_suffix'] ? " " . $item['previous_name_suffix'] : "") .
            ($item['previous_name_enddate'] ? " " . $item['previous_name_enddate'] : "");

        return text($name);
    }


    public function getPatientNameHistory($pid)
    {
        $sql = "SELECT pid,
            id,
            previous_name_prefix,
            previous_name_first,
            previous_name_middle,
            previous_name_last,
            previous_name_suffix,
            previous_name_enddate
            FROM patient_history
            WHERE pid = ? AND history_type_key = ?";
        $results =  QueryUtils::sqlStatementThrowException($sql, array($pid, 'name_history'));
        $rows = [];
        while ($row = sqlFetchArray($results)) {
            $row['formatted_name'] = $this->formatPreviousName($row);
            $rows[] = $row;
        }

        return $rows;
    }


    public function getPatientNameHistoryById($pid, $id)
    {
        $sql = "SELECT pid,
            id,
            previous_name_prefix,
            previous_name_first,
            previous_name_middle,
            previous_name_last,
            previous_name_suffix,
            previous_name_enddate
            FROM patient_history
            WHERE pid = ? AND id = ? AND history_type_key = ?";
        $result =  sqlQuery($sql, array($pid, $id, 'name_history'));
        $result['formatted_name'] = $this->formatPreviousName($result);

        return $result;
    }
}
