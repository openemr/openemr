<?php

/**
 * Shared helpers for the appointment-reminder cron.
 *
 * Extracted from the popup entry point `rc_sms_notification.php` so
 * non-UI callers (the CLI background-service wrapper in
 * `run_notifications.php`, unit tests) can pull in just the procedural
 * support functions without also loading the popup's HTML chrome and
 * scan-and-send loop. Both entry points require_once this file.
 *
 * The scan-and-send pipeline itself lives in
 * `OpenEMR\Modules\FaxSMS\Notification\AppointmentNotificationRunner`;
 * the functions here are the thin SQL/formatting primitives that the
 * runner and existing callers invoke.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Unknown
 * @author    Larry Lart
 * @author    Jerry Padgett
 * @author    Robert Down
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Unknown
 * @copyright Copyright (c) 2008 Larry Lart
 * @copyright Copyright (c) 2018-2024 Jerry Padgett
 * @copyright Copyright (c) 2021 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @codeCoverageIgnore Procedural global-namespace SQL helpers loaded via
 *     require_once from two entry points. Every function here either
 *     issues raw SQL (notification_log inserts, calendar updates) or
 *     reads `OEGlobalsBag` state set by those entry points. Exercising
 *     them in isolation requires a real database; the orchestrator that
 *     calls them (`AppointmentNotificationRunner`) is covered separately
 *     in `tests/Tests/Isolated/Modules/FaxSMS/Notification/`.
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;

if (!function_exists('rc_sms_notification_cron_update_entry')) {
    /**
     * Mark a non-recurring appointment as already-notified for a given channel.
     *
     * Only the per-channel alert flag (pc_sendalertsms / pc_sendalertemail) is
     * updated. pc_apptstatus is intentionally left alone — it holds the real
     * appointment status set by front-desk staff (Pending, Arrived, etc.) and
     * must not be overwritten with notification metadata. See #11479.
     *
     * Recurring events are intentionally skipped: the pc_sendalertsms /
     * pc_sendalertemail columns live on the base event row which is shared by
     * every occurrence. Setting them would suppress reminders for all future
     * occurrences. Recurring-event dedup is handled in
     * faxsms_getAlertPatientData() by checking the notification_log keyed on
     * (pc_eid, pc_eventDate, type).
     */
    function rc_sms_notification_cron_update_entry(NotificationChannel $channel, int $pid, int $pc_eid, string $recur = ''): void
    {
        global $bTestRun;

        if ($bTestRun || (int) trim($recur) > 0) {
            return;
        }

        $column = match ($channel) {
            NotificationChannel::SMS   => 'pc_sendalertsms',
            NotificationChannel::EMAIL => 'pc_sendalertemail',
        };

        $query = "UPDATE openemr_postcalendar_events SET {$column} = 'YES' WHERE pc_pid = ? AND pc_eid = ?";

        QueryUtils::sqlStatementThrowException($query, [$pid, $pc_eid]);
    }
}

if (!function_exists('faxsms_getAlertPatientData')) {
    /**
     * Return appointments due for a reminder on the given channel.
     *
     * Pass the notification channel and hours-ahead explicitly rather than reading
     * them from globals. The Background Services entry path loads this file via
     * require_once from inside a function, which traps the file's top-level
     * variables as function locals — `global $TYPE` then sees null and the wrong
     * WHERE clause runs, causing duplicate email reminders. See issue #11477.
     *
     * Renamed from `cron_GetAlertPatientData()` to a module-prefixed name to avoid
     * a PHP case-insensitive collision with a legacy `cron_getAlertpatientData()`
     * that historically lived in the (now-removed) `modules/sms_email_reminder`
     * module.
     *
     * @return list<array<mixed>>
     */
    function faxsms_getAlertPatientData(NotificationChannel $channel, int $notificationHour): array
    {
        $where = match ($channel) {
            NotificationChannel::EMAIL => " AND (p.hipaa_allowemail='YES' AND p.email<>'' AND e.pc_sendalertemail != 'YES' AND e.pc_apptstatus != 'x')",
            NotificationChannel::SMS   => " AND (p.hipaa_allowsms='YES' AND p.phone_cell<>'' AND e.pc_sendalertsms != 'YES' AND e.pc_apptstatus != 'x')",
        };
        $adj_date = (int) date("H") + $notificationHour;
        $check_date = date("Y-m-d", mktime($adj_date, 0, 0, (int) date("m"), (int) date("d"), (int) date("Y")));

        $events = fetchEvents($check_date, $check_date, $where, 'u.lname,pc_startTime,p.lname');
        if (!is_array($events)) {
            return [];
        }

        // For recurring events, the events-table dedup columns
        // (pc_sendalertsms/pc_sendalertemail) live on the base event row shared
        // by all occurrences, so they cannot distinguish individual occurrence
        // dates. Check the notification_log instead, keyed on
        // (pc_eid, pc_eventDate, type).
        // $channel is the PHP enum NotificationChannel ('SMS'/'EMAIL').
        // notification_log.type is a MySQL enum('SMS','Email'). MySQL enum
        // comparisons are case-insensitive, so the PHP value matches regardless
        // of casing differences between the two enums.
        $channelType = $channel->value;

        // Collect recurring event IDs so we can batch-check notification_log
        // in a single query instead of one query per occurrence. Use string
        // keys to match the mixed types from fetchEvents() and notification_log
        // without requiring casts from mixed.
        /** @var array<string, true> */
        $recurringEids = [];
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }
            $recurrtype = $event['pc_recurrtype'] ?? 0;
            $pcEid = $event['pc_eid'] ?? null;
            if (is_numeric($recurrtype) && $recurrtype > 0 && is_numeric($pcEid)) {
                $recurringEids[(string) $pcEid] = true;
            }
        }

        /** @var array<string, array<string, true>> */
        $sentNotifications = [];
        if ($recurringEids !== []) {
            $pcEids = array_keys($recurringEids);
            $placeholders = implode(',', array_fill(0, count($pcEids), '?'));
            $rows = QueryUtils::fetchRecords(
                "SELECT pc_eid, pc_eventDate FROM notification_log WHERE type = ? AND pc_eventDate = ? AND pc_eid IN ($placeholders)",
                array_merge([$channelType, $check_date], $pcEids),
            );
            foreach ($rows as $row) {
                $eid = $row['pc_eid'] ?? null;
                $date = $row['pc_eventDate'] ?? null;
                if (is_numeric($eid) && is_string($date)) {
                    $sentNotifications[(string) $eid][$date] = true;
                }
            }
        }

        $normalized = [];
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }
            $recurrtype = $event['pc_recurrtype'] ?? 0;
            $eventDate = $event['pc_eventDate'] ?? '';
            $pcEid = $event['pc_eid'] ?? null;
            if (
                is_numeric($recurrtype) && $recurrtype > 0
                && is_string($eventDate)
                && is_numeric($pcEid)
                && isset($sentNotifications[(string) $pcEid][$eventDate])
            ) {
                continue;
            }
            $normalized[] = $event;
        }
        return $normalized;
    }
}

if (!function_exists('cron_GetNotificationData')) {
    /**
     * Cron Get Notification Data
     *
     * @param string $type
     * @return array|false
     */
    function cron_GetNotificationData($type): bool|array
    {
        $db_sms_msg['notification_id'] = '';
        $db_sms_msg['sms_gateway_type'] = '';

        $query = "Select * From automatic_notification Where type = ?";
        $db_sms_msg = sqlFetchArray(sqlStatement($query, [$type]));

        return $db_sms_msg;
    }
}

if (!function_exists('cron_InsertNotificationLogEntryFaxsms')) {
    /**
     * Cron Insert Notification Log Entry
     *
     * @param string       $type
     * @param array<mixed> $prow
     * @param array<mixed> $db_sms_msg
     */
    function cron_InsertNotificationLogEntryFaxsms($type, $prow, $db_sms_msg): void
    {
        $smsgateway_info = $type == 'SMS' ? "" : $db_sms_msg['email_sender'] . "|||" . $db_sms_msg['email_subject'];

        $patient_info = $prow['title'] . " " . $prow['fname'] . " " . $prow['mname'] . " " . $prow['lname'] . "|||" . $prow['phone_cell'] . "|||" . $prow['email'];
        $data_info = $prow['pc_eventDate'] . "|||" . $prow['pc_endDate'] . "|||" . $prow['pc_startTime'] . "|||" . $prow['pc_endTime'];
        $sdate = date("Y-m-d H:i:s");
        $sql_loginsert = "INSERT INTO `notification_log` (`iLogId` , `pid` , `pc_eid` , `sms_gateway_type` , `message` , `type` , `patient_info` , `smsgateway_info` , `pc_eventDate` , `pc_endDate` , `pc_startTime` , `pc_endTime` , `dSentDateTime`) VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?)";

        $safe = [$prow['pid'], $prow['pc_eid'], $db_sms_msg['sms_gateway_type'], $db_sms_msg['message'], $db_sms_msg['type'], $patient_info, $smsgateway_info, $prow['pc_eventDate'], $prow['pc_endDate'], $prow['pc_startTime'], $prow['pc_endTime'], $sdate];

        sqlStatement($sql_loginsert, $safe);
    }
}
