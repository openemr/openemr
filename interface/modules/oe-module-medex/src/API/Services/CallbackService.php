<?php

/**
 * Callback Service - Handles incoming message responses from MedEx
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi\Services;

use OpenEMR\Common\Database\QueryUtils;

class CallbackService extends BaseService
{
    /**
     * Receive and process message response from MedEx
     *
     * @param array<string,mixed>|string $data
     * @return array<string,mixed>
     */
    public function receive(array|string $data = ''): array
    {
        $response = [];

        if ($data === '') {
            $data = $_POST;
        }

        if (empty($data['campaign_uid'])) {
            $response['success'] = "No campaigns to process.";
            return $response;
        }

        if (!isset($data['patient_id']) || !$data['patient_id']) {
            if (!empty($data['e_pid'])) {
                $data['patient_id'] = $data['e_pid'];
            } elseif (!empty($data['pc_eid'])) {
                $patientRecords = QueryUtils::fetchRecords(
                    "SELECT pid FROM openemr_postcalendar_events WHERE pc_eid=?",
                    [$data['pc_eid']]
                );
                $patient = $patientRecords[0] ?? null;
                $data['patient_id'] = $patient['pid'] ?? null;
            }
        }

        if (!empty($data['patient_id'])) {
            // Insert message record
            $sqlINSERT = "INSERT INTO medex_outgoing (msg_pc_eid, msg_pid, campaign_uid, msg_type, msg_reply, msg_extra_text, msg_date, medex_uid)
                            VALUES (?,?,?,?,?,?,utc_timestamp(),?)";

            if (empty($data['M_type'])) {
                $data['M_type'] = 'pending';
            }

            QueryUtils::sqlStatementThrowException(
                $sqlINSERT,
                [
                    $data['pc_eid'] ?? null,
                    $data['patient_id'],
                    $data['campaign_uid'],
                    $data['M_type'],
                    $data['msg_reply'] ?? null,
                    $data['msg_extra'] ?? null,
                    $data['msg_uid'] ?? null
                ]
            );

            // Handle CONFIRMED appointments
            if (($data['msg_reply'] ?? '') === 'CONFIRMED') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_eid=?",
                    [$data['msg_type'] ?? null, $data['pc_eid'] ?? null]
                );

                // Update patient tracker
                $trackerRecords = QueryUtils::fetchRecords(
                    "SELECT id, lastseq FROM patient_tracker WHERE eid=?",
                    [$data['pc_eid'] ?? null]
                );
                $tracker = $trackerRecords[0] ?? null;

                if (!empty($tracker['id'])) {
                    $newSeq = ($tracker['lastseq'] ?? 0) + 1;
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE patient_tracker SET lastseq = ? WHERE eid=?",
                        [$newSeq, $data['pc_eid'] ?? null]
                    );

                    $datetime = date("Y-m-d H:i:s");
                    QueryUtils::sqlStatementThrowException(
                        "INSERT INTO patient_tracker_element
                            (pt_tracker_id, start_datetime, user, status, seq)
                            VALUES (?,?,?,?,?)",
                        [$tracker['id'], $datetime, 'MedEx', $data['msg_type'] ?? null, $newSeq]
                    );
                }
            } elseif (($data['msg_reply'] ?? '') === 'CALL') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE openemr_postcalendar_events SET pc_apptstatus = 'CALL' WHERE pc_eid=?",
                    [$data['pc_eid'] ?? null]
                );
            } elseif (($data['msg_type'] ?? '') === 'AVM' && ($data['msg_reply'] ?? '') === 'STOP') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE patient_data SET hipaa_voice = 'NO' WHERE pid=?",
                    [$data['patient_id']]
                );
            } elseif (($data['msg_type'] ?? '') === 'SMS' && ($data['msg_reply'] ?? '') === 'STOP') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE patient_data SET hipaa_allowsms = 'NO' WHERE pid=?",
                    [$data['patient_id']]
                );
            } elseif (($data['msg_type'] ?? '') === 'EMAIL' && ($data['msg_reply'] ?? '') === 'STOP') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE patient_data SET hipaa_allowemail = 'NO' WHERE pid=?",
                    [$data['patient_id']]
                );
            }

            // Clean up pending messages
            if (in_array($data['msg_reply'] ?? '', ['SENT', 'READ'])) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM medex_outgoing WHERE msg_pc_eid=? AND msg_reply='To Send'",
                    [$data['pc_eid'] ?? null]
                );
            }

            $response['comments'] = ($data['pc_eid'] ?? '') . " - " .
                                   $data['campaign_uid'] . " - " .
                                   ($data['msg_type'] ?? '') . " - " .
                                   ($data['reply'] ?? '') . " - " .
                                   ($data['extra'] ?? '');
            $response['pid'] = $data['patient_id'];
            $response['success'] = ($data['msg_type'] ?? 'unknown') . " reply";
        } else {
            $response['success'] = "completed";
        }

        return $response;
    }
}
