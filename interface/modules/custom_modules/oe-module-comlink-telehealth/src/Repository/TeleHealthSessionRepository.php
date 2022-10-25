<?php

/**
 * Retrieves, creates, and updates database telehealth session records that track a provider/patient session relationship
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Repository;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;

class TeleHealthSessionRepository
{
    const TABLE_NAME = "comlink_telehealth_appointment_session";
    public function createSession($pc_eid, $user_id, $encounter, $pid)
    {
        $sql = "INSERT INTO " . self::TABLE_NAME . " (pc_eid, user_id, encounter, pid) VALUES (?,?,?,?)";
        (new SystemLogger())->debug("Attempting to create session ", ['pc_eid' => $pc_eid, 'user_id' => $user_id, 'encounter' => $encounter, 'pid' => $pid]);
        $sessionId = QueryUtils::sqlInsert($sql, [$pc_eid, $user_id, $encounter, $pid]);
        // now return the record
        return $this->getSessionByEncounter($encounter);
    }
    public function updateStartTimestamp($pc_eid, $isProvider = true)
    {
        $columnPrefix = $isProvider ? "provider" : "patient";

        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $columnPrefix . "_start_time = NOW() WHERE pc_eid = ?";
        QueryUtils::sqlStatementThrowException($sql, [$pc_eid]);
    }
    public function updateLastSeenTimestamp($pc_eid, $isProvider = true)
    {
        $columnPrefix = $isProvider ? "provider" : "patient";

        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $columnPrefix . "_last_update = NOW() WHERE pc_eid = ?";
//        (new SystemLogger())->debug("updating last seen timestamp", ['pc_eid' => $pc_eid, 'sql' => $sql, 'isProvider' => $isProvider]);
        QueryUtils::sqlStatementThrowException($sql, [$pc_eid]);
    }

    public function getSessionByEncounter($eid): ?array
    {
        $records = QueryUtils::fetchRecords("Select * from " . self::TABLE_NAME . " WHERE encounter = ? ", [$eid]);
        if (!empty($records)) {
            return $records[0];
        }
        return null;
    }
    public function getSessionByAppointmentId($pc_eid, $user_id = null): ?array
    {
        $sql = "select * from " . self::TABLE_NAME . " WHERE pc_eid = ? ";
        $binds = [$pc_eid];
        if (!empty($user_id)) {
            $sql .= " AND user_id = ? ";
            $binds[] = $user_id;
        }
        $records = QueryUtils::fetchRecords($sql, $binds);
        if (!empty($records)) {
            return $records[0];
        }
        return null;
    }
}
