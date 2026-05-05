<?php

/**
 * MedEx Message Receive Service
 *
 * Handles incoming replies and status updates from MedEx server
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2024-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;

class MessageReceiveService
{
    /**
     * Process incoming message reply/status from MedEx
     *
     * @param array<string,mixed> $data Message data from MedEx callback
     * @return array<string,mixed> Response with success status
     */
    public function receive(array $data): array
    {
        if (empty($data['campaign_uid'])) {
            return [
                'success' => true,
                'message' => 'No campaigns to process'
            ];
        }

        // Get patient ID from various sources
        $patientId = $this->getPatientId($data);

        if (!$patientId) {
            return [
                'success' => false,
                'error' => 'Could not determine patient ID'
            ];
        }

        // Default message type if not provided
        if (empty($data['M_type'])) {
            $data['M_type'] = 'pending';
        }

        // Insert message record into medex_outgoing
        $this->insertMessageRecord($data, $patientId);

        // Process based on reply type
        $this->processReply($data, $patientId);

        return [
            'success' => true,
            'message' => $data['M_type'] . ' reply processed',
            'comments' => $data['pc_eid'] . ' - ' . $data['campaign_uid'] . ' - ' . $data['M_type'],
            'pid' => $patientId
        ];
    }

    /**
     * Get patient ID from various data sources
     *
     * @param array<string,mixed> $data
     * @return int|null
     */
    private function getPatientId(array $data): ?int
    {
        // Direct patient_id
        if (!empty($data['patient_id'])) {
            return (int)$data['patient_id'];
        }

        // From e_pid
        if (!empty($data['e_pid'])) {
            return (int)$data['e_pid'];
        }

        // From appointment pc_eid
        if (!empty($data['pc_eid'])) {
            $records = QueryUtils::fetchRecords(
                "SELECT pc_pid FROM openemr_postcalendar_events WHERE pc_eid = ?",
                [$data['pc_eid']]
            );
            $result = $records[0] ?? null;

            return isset($result['pc_pid']) ? (int)$result['pc_pid'] : null;
        }

        return null;
    }

    /**
     * Insert message record into medex_outgoing table
     *
     * @param array<string,mixed> $data
     * @param int $patientId
     * @return void
     */
    private function insertMessageRecord(array $data, int $patientId): void
    {
        $sql = "INSERT INTO medex_outgoing
                (msg_pc_eid, msg_pid, campaign_uid, msg_type, msg_reply, msg_extra_text, msg_date, medex_uid)
                VALUES (?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), ?)";

        QueryUtils::sqlStatementThrowException($sql, [
            $data['pc_eid'] ?? null,
            $patientId,
            $data['campaign_uid'],
            $data['M_type'],
            $data['msg_reply'] ?? '',
            $data['msg_extra'] ?? '',
            $data['msg_uid'] ?? ''
        ]);
    }

    /**
     * Process reply based on type
     *
     * @param array<string,mixed> $data
     * @param int $patientId
     * @return void
     */
    private function processReply(array $data, int $patientId): void
    {
        $reply = $data['msg_reply'] ?? '';
        $msgType = $data['M_type'] ?? '';
        $pcEid = $data['pc_eid'] ?? null;

        switch ($reply) {
            case 'CONFIRMED':
                $this->handleConfirmed($pcEid, $msgType);
                break;

            case 'CALL':
                $this->handleCallRequest($pcEid);
                break;

            case 'STOP':
                $this->handleOptOut($patientId, $msgType);
                break;

            case 'SENT':
            case 'READ':
                $this->handleSentOrRead($pcEid);
                break;

            case 'FAILED':
                $this->handleFailed($pcEid, $data);
                break;

            case 'BOUNCE':
                $this->handleBounce($patientId, $msgType);
                break;

            default:
                // Other reply types - just log
                error_log("MedEx Receive: Unknown reply type '{$reply}' for pc_eid {$pcEid}");
                break;
        }
    }

    /**
     * Handle CONFIRMED reply - patient confirmed appointment
     *
     * @param int|null $pcEid
     * @param string $msgType
     * @return void
     */
    private function handleConfirmed(?int $pcEid, string $msgType): void
    {
        if (!$pcEid) {
            return;
        }

        // Update appointment status
        QueryUtils::sqlStatementThrowException(
            "UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_eid = ?",
            [$msgType, $pcEid]
        );

        // Update patient tracker if exists
        $trackerRecords = QueryUtils::fetchRecords(
            "SELECT id, lastseq FROM patient_tracker WHERE eid = ?",
            [$pcEid]
        );
        $tracker = $trackerRecords[0] ?? null;

        if (!empty($tracker['id'])) {
            $newSeq = ($tracker['lastseq'] ?? 0) + 1;

            QueryUtils::sqlStatementThrowException(
                "UPDATE patient_tracker SET lastseq = ? WHERE eid = ?",
                [$newSeq, $pcEid]
            );

            QueryUtils::sqlStatementThrowException(
                "INSERT INTO patient_tracker_element
                (pt_tracker_id, start_datetime, user, status, seq)
                VALUES (?, NOW(), 'MedEx', ?, ?)",
                [$tracker['id'], $msgType, $newSeq]
            );
        }

        error_log("MedEx: Appointment {$pcEid} confirmed by patient");
    }

    /**
     * Handle CALL reply - patient needs to call office
     *
     * @param int|null $pcEid
     * @return void
     */
    private function handleCallRequest(?int $pcEid): void
    {
        if (!$pcEid) {
            return;
        }

        QueryUtils::sqlStatementThrowException(
            "UPDATE openemr_postcalendar_events SET pc_apptstatus = 'CALL' WHERE pc_eid = ?",
            [$pcEid]
        );

        error_log("MedEx: Appointment {$pcEid} requires call from office");

        // Create onsite portal message/alert for front desk
        $appt = QueryUtils::sqlQueryThrowException(
            "SELECT pc_pid, pc_eventDate, pc_startTime FROM openemr_postcalendar_events WHERE pc_eid = ?",
            [$pcEid]
        );

        if ($appt) {
            $noteText = "Patient requested office call for appointment on " .
                        date('m/d/Y', strtotime($appt['pc_eventDate'])) . " at " .
                        date('g:i A', strtotime($appt['pc_startTime']));

            QueryUtils::sqlStatementThrowException(
                "INSERT INTO onsite_messages (username, message, authorized, activity)
                 SELECT 'portal-user', ?, '1', '1'
                 FROM patient_data WHERE pid = ? LIMIT 1",
                [$noteText, $appt['pc_pid']]
            );
        }
    }

    /**
     * Handle STOP reply - patient opts out
     *
     * @param int $patientId
     * @param string $msgType
     * @return void
     */
    private function handleOptOut(int $patientId, string $msgType): void
    {
        // Update HIPAA preferences based on message type
        switch ($msgType) {
            case 'AVM':
                QueryUtils::sqlStatementThrowException(
                    "UPDATE patient_data SET hipaa_voice = 'NO' WHERE pid = ?",
                    [$patientId]
                );
                error_log("MedEx: Patient {$patientId} opted out of voice messages");
                break;

            case 'SMS':
                QueryUtils::sqlStatementThrowException(
                    "UPDATE patient_data SET hipaa_allowsms = 'NO' WHERE pid = ?",
                    [$patientId]
                );
                error_log("MedEx: Patient {$patientId} opted out of SMS messages");
                break;

            case 'EMAIL':
                QueryUtils::sqlStatementThrowException(
                    "UPDATE patient_data SET hipaa_allowemail = 'NO' WHERE pid = ?",
                    [$patientId]
                );
                error_log("MedEx: Patient {$patientId} opted out of email messages");
                break;

            default:
                error_log("MedEx: Patient {$patientId} opted out - unknown type {$msgType}");
                break;
        }
    }

    /**
     * Handle SENT or READ status - message delivered
     *
     * @param int|null $pcEid
     * @return void
     */
    private function handleSentOrRead(?int $pcEid): void
    {
        if (!$pcEid) {
            return;
        }

        // Delete pending "To Send" messages for this appointment
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM medex_outgoing WHERE msg_pc_eid = ? AND msg_reply = 'To Send'",
            [$pcEid]
        );
    }

    /**
     * Handle FAILED status - message delivery failed
     *
     * @param int|null $pcEid
     * @param array<string,mixed> $data
     * @return void
     */
    private function handleFailed(?int $pcEid, array $data): void
    {
        if (!$pcEid) {
            return;
        }

        $errorMsg = $data['msg_extra'] ?? 'Unknown error';
        error_log("MedEx: Message failed for appointment {$pcEid}: " . $errorMsg);

        // Create note in appointment for staff follow-up
        $appt = QueryUtils::sqlQueryThrowException(
            "SELECT pc_pid FROM openemr_postcalendar_events WHERE pc_eid = ?",
            [$pcEid]
        );

        if ($appt) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO onsite_messages (username, message, authorized, activity)
                 SELECT 'portal-user', ?, '1', '1'
                 FROM patient_data WHERE pid = ? LIMIT 1",
                ["MedEx message delivery failed: {$errorMsg}. Please contact patient directly.", $appt['pc_pid']]
            );
        }
    }

    /**
     * Handle BOUNCE status - email bounced or invalid phone
     *
     * @param int $patientId
     * @param string $msgType
     * @return void
     */
    private function handleBounce(int $patientId, string $msgType): void
    {
        error_log("MedEx: {$msgType} bounced for patient {$patientId}");

        // Add note to patient chart flagging contact info for verification
        $contactType = match ($msgType) {
            'EMAIL' => 'email address',
            'SMS' => 'cell phone number',
            'AVM' => 'phone number',
            default => 'contact information'
        };

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO onsite_messages (username, message, authorized, activity)
             SELECT 'portal-user', ?, '1', '1'
             FROM patient_data WHERE pid = ? LIMIT 1",
            ["MedEx message bounced - please verify patient's {$contactType} is correct.", $patientId]
        );

        // Also add a note to pnotes table for staff review
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO pnotes (date, body, pid, user, groupname, activity, title, assigned_to)
             VALUES (NOW(), ?, ?, 'MedEx', 'Default', 1, 'Contact Info Verification Needed', '')",
            ["Patient's {$contactType} appears invalid (MedEx message bounced). Please verify and update.", $patientId]
        );
    }

    /**
     * Get message statistics for a patient
     *
     * @param int $patientId
     * @param int $limit
     * @return array<array<string,mixed>>
     */
    public function getPatientMessageHistory(int $patientId, int $limit = 50): array
    {
        $sql = "SELECT mo.*,
                       cal.pc_eventDate, cal.pc_startTime,
                       cal.pc_apptstatus
                FROM medex_outgoing mo
                LEFT JOIN openemr_postcalendar_events cal ON mo.msg_pc_eid = cal.pc_eid
                WHERE mo.msg_pid = ?
                ORDER BY mo.msg_date DESC
                LIMIT ?";

        return QueryUtils::fetchRecords($sql, [$patientId, $limit]);
    }

    /**
     * Get message statistics for an appointment
     *
     * @param int $pcEid
     * @return array<array<string,mixed>>
     */
    public function getAppointmentMessages(int $pcEid): array
    {
        $sql = "SELECT * FROM medex_outgoing
                WHERE msg_pc_eid = ?
                ORDER BY msg_date DESC";

        return QueryUtils::fetchRecords($sql, [$pcEid]);
    }

    /**
     * Get pending messages (To Send status)
     *
     * @return array<array<string,mixed>>
     */
    public function getPendingMessages(): array
    {
        $sql = "SELECT mo.*,
                       pat.fname, pat.lname, pat.phone_cell, pat.email,
                       cal.pc_eventDate, cal.pc_startTime
                FROM medex_outgoing mo
                LEFT JOIN patient_data pat ON mo.msg_pid = pat.pid
                LEFT JOIN openemr_postcalendar_events cal ON mo.msg_pc_eid = cal.pc_eid
                WHERE mo.msg_reply = 'To Send'
                ORDER BY mo.msg_date ASC";

        return QueryUtils::fetchRecords($sql);
    }
}
