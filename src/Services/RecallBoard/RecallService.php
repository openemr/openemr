<?php

/**
 * Recall Board Service
 * Core recall functionality for OpenEMR
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2024 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\RecallBoard;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class RecallService
{
    /**
     * Get the recalls table name
     *
     * @return string Table name (patient_recalls)
     */
    private static function getRecallsTable(): string
    {
        return 'patient_recalls';
    }

    /**
     * Get the recall actions table name
     *
     * @return string Table name (recall_board_actions)
     */
    private static function getActionsTable(): string
    {
        return 'recall_board_actions';
    }

    /**
     * Calculate patient age from date of birth
     *
     * @param string $dob Date of birth (YYYY-MM-DD)
     * @param string $asof Calculate age as of this date (default: today)
     * @return int Age in years
     */
    public static function getAge(string $dob, string $asof = ''): int
    {
        if (empty($asof)) {
            $asof = date('Y-m-d');
        }

        $a1 = explode('-', substr($dob, 0, 10));
        $a2 = explode('-', substr($asof, 0, 10));
        $age = (int)$a2[0] - (int)$a1[0];

        if ((int)$a2[1] < (int)$a1[1] || ((int)$a2[1] == (int)$a1[1] && (int)$a2[2] < (int)$a1[2])) {
            --$age;
        }

        return $age;
    }

    /**
     * Save or update a patient recall
     *
     * @param array<string,mixed> $data Recall data from form
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function saveRecall(array $data): void
    {
        // Handle both form field names and direct parameters
        $pid = $data['new_pid'] ?? $data['pid'] ?? '';
        $provider = $data['new_provider'] ?? $data['provider'] ?? '';
        $facility = $data['new_facility'] ?? $data['facility'] ?? '';
        $eventDate = $data['form_recall_date'] ?? $data['RECALL_DATE'] ?? '';
        $reason = $data['new_reason'] ?? $data['reason'] ?? '';

        if (!$pid) {
            throw new \InvalidArgumentException('Patient ID required');
        }

        if (!$eventDate) {
            throw new \InvalidArgumentException('Recall date required');
        }

        // Convert date from display format (m/d/Y or configured format) to database format (Y-m-d)
        // DateToYYYYMMDD() is in globals.php which is already loaded
        $eventDate = DateToYYYYMMDD($eventDate);

        $table = self::getRecallsTable();

        // Check if recall exists
        $existing = QueryUtils::fetchRecords(
            "SELECT * FROM {$table} WHERE r_pid = ?",
            [$pid]
        );

        if (!empty($existing)) {
            // Update existing recall
            QueryUtils::sqlStatementThrowException(
                "UPDATE {$table}
                 SET r_eventDate = ?, r_facility = ?, r_provider = ?, r_reason = ?
                 WHERE r_pid = ?",
                [$eventDate, $facility, $provider, $reason, $pid]
            );
        } else {
            // Insert new recall
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO {$table}
                 (r_pid, r_eventDate, r_facility, r_provider, r_reason)
                 VALUES (?, ?, ?, ?, ?)",
                [$pid, $eventDate, $facility, $provider, $reason]
            );
        }

        // Update patient demographics if provided
        // Note: "Demographic changes made here are recorded system-wide"
        $phone_home = $data['new_phone_home'] ?? null;
        $phone_cell = $data['new_phone_cell'] ?? null;
        $email = $data['new_email'] ?? null;
        $email_allow = $data['new_email_allow'] ?? null;
        $voice = $data['new_voice'] ?? null;
        $allowsms = $data['new_allowsms'] ?? null;
        $address = $data['new_address'] ?? null;
        $postal_code = $data['new_postal_code'] ?? null;
        $city = $data['new_city'] ?? null;
        $state = $data['new_state'] ?? null;

        if ($phone_home || $phone_cell || $email || $address || $city || $state || $postal_code) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE patient_data
                 SET phone_home = COALESCE(?, phone_home),
                     phone_cell = COALESCE(?, phone_cell),
                     email = COALESCE(?, email),
                     hipaa_allowemail = COALESCE(?, hipaa_allowemail),
                     hipaa_voice = COALESCE(?, hipaa_voice),
                     hipaa_allowsms = COALESCE(?, hipaa_allowsms),
                     street = COALESCE(?, street),
                     postal_code = COALESCE(?, postal_code),
                     city = COALESCE(?, city),
                     state = COALESCE(?, state)
                 WHERE pid = ?",
                [
                    $phone_home,
                    $phone_cell,
                    $email,
                    $email_allow,
                    $voice,
                    $allowsms,
                    $address,
                    $postal_code,
                    $city,
                    $state,
                    $pid
                ]
            );
        }
    }

    /**
     * Delete a patient recall
     *
     * @param int|null $pid Patient ID
     * @param int|null $r_ID Recall ID
     * @return void
     */
    public static function deleteRecall(?int $pid = null, ?int $r_ID = null): void
    {
        // Get from POST if not provided (for backward compatibility)
        $pid = $pid ?? ($_POST['pid'] ?? null);
        $r_ID = $r_ID ?? ($_POST['r_ID'] ?? null);

        if (!$pid && !$r_ID) {
            return;
        }

        $recallsTable = self::getRecallsTable();
        $actionsTable = self::getActionsTable();

        // Delete recall record
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM {$recallsTable} WHERE r_pid = ? OR r_ID = ?",
            [$pid, $r_ID]
        );
    }

    /**
     * Record an outgoing action for the recall board
     *
     * @param string $pc_eid Message context (eg. 'recall_123')
     * @param string $type Action type (eg. 'postcards','labels','phone','notes')
     * @param int $userId User ID who performed the action
     * @param string $extra Optional extra text
     * @return void
     */
    public static function addAction(string $pc_eid, string $type, int $userId, string $extra = ''): void
    {
        $actionsTable = self::getActionsTable();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$actionsTable} (msg_pc_eid, msg_type, msg_reply, msg_extra_text, msg_date) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())",
            [$pc_eid, $type, $userId, $extra]
        );
    }

    /**
     * Get the postcard top template from core globals.
     *
     * @return string
     */
    public static function getPostcardTop(): string
    {
        $globals = OEGlobalsBag::getInstance();
        return (string)$globals->get('recall_board_postcard_top');
    }
}
