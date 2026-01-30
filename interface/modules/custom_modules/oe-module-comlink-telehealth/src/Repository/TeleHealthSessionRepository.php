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
use OpenEMR\Services\PatientService;
use InvalidArgumentException;

class TeleHealthSessionRepository
{
    const TABLE_NAME = "comlink_telehealth_appointment_session";

    public function createSession($pc_eid, $user_id, $encounter, $pid): ?array
    {
        $sql = "INSERT INTO " . self::TABLE_NAME . " (pc_eid, user_id, encounter, pid) VALUES (?,?,?,?)";
        (new SystemLogger())->debug("Attempting to create session ", ['pc_eid' => $pc_eid, 'user_id' => $user_id, 'encounter' => $encounter, 'pid' => $pid]);
        QueryUtils::sqlInsert($sql, [$pc_eid, $user_id, $encounter, $pid]);
        // now return the record.  Should be a 1:1 relationship with appointments.  Encounters can be a m:1 relationship
        return $this->getSessionByAppointmentId($pc_eid, $user_id);
    }

    public function addRelatedPartyToSession($pc_eid, $pid_related): ?array
    {
        $patientService = new PatientService();
        $session = $this->getSessionByAppointmentId($pc_eid);
        if (empty($session)) {
            throw new InvalidArgumentException("session could not be found for pc_eid of " . $pc_eid);
        }
        $relatedPatient = $patientService->findByPid($pid_related);
        if (empty($relatedPatient)) {
            throw new InvalidArgumentException("Patient does not exist for pid " . $pid_related);
        }
        // TODO: @adunsulag do we want to keep track of a different third party here?
        // eventually if we support more than one person to the call we could handle this differently.
        // TODO: @adunsulag this really all should be put into a transaction.
        $sql = "UPDATE " . self::TABLE_NAME . " SET pid_related = ?, patient_related_start_time=NULL, "
        . " patient_related_last_update=NULL WHERE pc_eid = ? ";
        $bind = [$pid_related, $pc_eid];
        QueryUtils::sqlStatementThrowException($sql, $bind);
        return $this->getSessionByAppointmentId($pc_eid);
    }
    public function updateStartTimestamp($pc_eid, $role = 'provider'): void
    {
        $validRoles = ['provider', 'patient', 'patient_related'];
        if (!in_array($role, $validRoles)) {
            throw new InvalidArgumentException("Invalid role provided of " . $role);
        }
        $columnPrefix = $role;

        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $columnPrefix . "_start_time = NOW() WHERE pc_eid = ?";
        QueryUtils::sqlStatementThrowException($sql, [$pc_eid]);
    }
    public function updateLastSeenTimestamp($pc_eid, $role): void
    {
        $validRoles = ['provider', 'patient', 'patient_related'];
        if (!in_array($role, $validRoles)) {
            throw new InvalidArgumentException("Invalid role provided of " . $role);
        }
        $columnPrefix = $role;

        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $columnPrefix . "_last_update = NOW() WHERE pc_eid = ?";
//        (new SystemLogger())->debug("updating last seen timestamp", ['pc_eid' => $pc_eid, 'sql' => $sql, 'isProvider' => $isProvider]);
        QueryUtils::sqlStatementThrowException($sql, [$pc_eid]);
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

    public function updatePatientFromAppointment(array $session, array $appt): void
    {
        // for now we only sync the patient record if its been changed
        if ($session['pid'] != $appt['pc_pid']) {
            $sql = "UPDATE " . self::TABLE_NAME . " SET patient_start_time = NULL, patient_last_update = NULL"
            . " ,pid = ? WHERE pc_eid = ? AND pid = ? ";
            $oldPid = $session['pid'];
            $newPid = $appt['pc_pid'];
            (new SystemLogger())->info(
                "TelehealthSessionRepository->updatePatientFromAppointment() "
                . "changing session patient assignment for appointment",
                ['pc_eid' => $appt['pc_eid'], 'pid' => $oldPid, 'pc_pid' => $newPid]
            );
            QueryUtils::sqlStatementThrowException($sql, [$newPid, $appt['pc_eid'], $oldPid]);
        }
    }
}
