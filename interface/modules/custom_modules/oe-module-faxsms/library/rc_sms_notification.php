<?php

/**
 * Email or SMS Cron Notification
 *
 * Run by cron every hour, look for appointments in pre-notification period and
 * send an SMS reminder
 *
 * @author    Unknown
 * @author    Larry Lart
 * @author    Jerry Padgett
 * @author    Robert Down
 * @copyright Unknown
 * @copyright Copyright (c) 2008 Larry Lart
 * @copyright Copyright (c) 2018-2024 Jerry Padgett
 * @copyright Copyright (c) 2021 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

//hack add for command line version
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;
use OpenEMR\Modules\FaxSMS\Exception\EmailSendFailedException;
use OpenEMR\Modules\FaxSMS\Exception\InvalidEmailAddressException;
use OpenEMR\Modules\FaxSMS\Exception\SmtpNotConfiguredException;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME'] = 'localhost';
$backpic = "";
$clientApp = null;
$emailApp = null;
// for cron
$error = '';
$runtime = [];

// Check for other arguments and perform your script logic
$argc ??= 0;
$argv ??= [];
if ($argc > 1) {
    foreach ($argv as $k => $v) {
        if ($k == 0) {
            continue;
        }
        $args = explode('=', $v);
        if ((count($args ?? [])) > 1) {
            $runtime[trim($args[0])] = trim($args[1]);
        }
    }
}
$isCli = 0;
if (php_sapi_name() === 'cli') {
    $isCli = 1;
    $_SERVER["HTTP_HOST"] = "localhost";
    $ignoreAuth = true;
}

// so service can set some settings if needed on init.
$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../../globals.php");
require_once(OEGlobalsBag::getInstance()->get('srcdir') . "/appointments.inc.php");

// Check for help argument
if ($argc > 1 && (in_array('--help', $argv) || in_array('-h', $argv))) {
    displayHelp();
    exit(0);
}

if (empty($runtime['site']) && empty($session->get('site_id')) && empty($_GET['site'])) {
    echo xlt("Missing Site Id using default") . "\n";
    $_GET['site'] = $runtime['site'] = 'default';
} else {
    $_GET['site'] = $runtime['site'];
}

$TYPE = '';
if (!empty($runtime['type'])) {
    $TYPE = strtoupper($runtime['type']);
} elseif (($_GET['type'] ?? '') === 'email') {
    $TYPE = $runtime['type'] = "EMAIL";
} else {
    $TYPE = $runtime['type'] = "SMS"; // default
}

$taskManager = new \OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager();
$CRON_TIME = $taskManager->getTaskHours(strtolower($TYPE));
// use service if needed
if ($TYPE === "SMS") {
    $session->set('authUser', $runtime['user'] ?? $session->get('authUser'));
    $clientApp = AppDispatch::getApiService('sms');
    $cred = $clientApp->getCredentials();

    if (!$clientApp->verifyAcl('patients', 'appt', $runtime['user'] ?? '')) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
}
if ($TYPE === "EMAIL") {
    $session->set('authUser', $runtime['user'] ?? $session->get('authUser'));
    $emailApp = AppDispatch::getApiService('email');
    $cred = $emailApp->getEmailSetup();

    if (!$emailApp->verifyAcl('patients', 'appt', $runtime['user'] ?? '')) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
}
// close writes
session_write_close();
set_time_limit(0);

$smsNotificationHourRaw = $cred['smsHours'] ?? $cred['notification_hours'] ?? 24;
$SMS_NOTIFICATION_HOUR = is_numeric($smsNotificationHourRaw) ? (int) $smsNotificationHourRaw : 24;
$MESSAGE = $cred['smsMessage'] ?? $cred['email_message'];

// check command line for quite option
$bTestRun = isset($_REQUEST['dryrun']) ? 1 : 0;
if (!empty($runtime['testrun'])) {
    $bTestRun = 1;
}

$db_sms_msg['type'] = $TYPE;
$db_sms_msg['sms_gateway_type'] = AppDispatch::getModuleVendor();
$db_sms_msg['message'] = $MESSAGE;
?>
    <!DOCTYPE html>
    <html lang="eng">
    <head>
        <title><?php echo xlt("Notifications") ?></title>
        <?php Header::setupHeader(); ?>
    </head>
    <style>
      html {
        font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
        font-size: 14px;
      }
    </style>
    <body>
        <div class="container-fluid">
            <div>
                <div class="text-center mt-2"><h2><?php echo xlt("Working and may take a few minutes to finish.") ?></h2></div>
            </div>
            <?php
            if ($bTestRun) {
                echo xlt("We are in Test Mode and no reminders will be sent. This test will check what reminders will be sent in when running Live Mode.");
            }
            $db_patient = faxsms_getAlertPatientData(NotificationChannel::fromLegacyType($TYPE), $SMS_NOTIFICATION_HOUR);
            echo "\n<br>" . xlt('Total of') . ": " . count($db_patient ?? []) . " " . xlt('Reminders Found') . " " . ($bTestRun ? xlt("and will be sending for reminders") . " " : xlt("and Sending for reminders ")) . ' ' . $SMS_NOTIFICATION_HOUR . ' ' . xlt("hrs from now.");
            ob_flush();
            flush();
            // for every event found
            $plast = '';
            echo "<h3>======================== " . text($TYPE) . " | " . text(date("Y-m-d H:i:s")) . " =========================</h3>";
            for ($p = 0; $p < count($db_patient); $p++) {
                ob_flush();
                flush();
                $prow = $db_patient[$p];
                $db_sms_msg['message'] = $MESSAGE;

                $app_date = $prow['pc_eventDate'] . " " . $prow['pc_startTime'];
                $app_time = strtotime($app_date);

                $app_time_hour = round($app_time / 3600);
                $curr_total_hour = round(time() / 3600);

                $remaining_app_hour = round($app_time_hour - $curr_total_hour);
                $remain_hour = round($remaining_app_hour - $SMS_NOTIFICATION_HOUR);

                if ($plast != $prow['ulname']) {
                    echo "<h4>" . xlt("For Provider") . ": " . text($prow['utitle']) . ' ' . text($prow['ufname']) . ' ' . text($prow['ulname']) . "</h4>";
                    $plast = $prow['ulname'];
                }
                $strMsg = "<strong>* " . xlt("SEND NOTIFICATION BEFORE:") . $SMS_NOTIFICATION_HOUR . " | " . xlt("CRONJOB RUNS EVERY:") . $CRON_TIME . " | " . xlt("APPOINTMENT DATE TIME") . ': ' . $app_date . " | " . xlt("APPOINTMENT REMAINING HOURS") . ": " . text($remaining_app_hour) . " | " . xlt("SEND ALERT AFTER") . ': ' . text($remain_hour) . "</strong>";

                // check in the interval
                if (\OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager::isWithinCronWindow((int) $remain_hour, $CRON_TIME)) {
                    //set message
                    $db_sms_msg['message'] = cron_SetMessage($prow, $db_sms_msg);
                    // send sms to patient - if not in test mode
                    if ($TYPE == 'SMS' && $clientApp != null) {
                        $isValid = isValidPhone($prow['phone_cell']);
                        // send sms to patient - if not in test mode
                        if ($bTestRun == 0 && $isValid) {
                            $error = $clientApp->sendSMS(
                                $prow['phone_cell'] ?? '',
                                $db_sms_msg['email_subject'] ?? '',
                                $db_sms_msg['message'] ?? '',
                                $db_sms_msg['email_sender'] ?? ''
                            );
                            if (stripos((string) $error, 'error') !== false) {
                                $strMsg .= " | " . xlt("Error:") . "<strong>" . text($error) . "</strong>\n";
                                error_log($strMsg); // text
                                echo(nl2br($strMsg));
                                continue;
                            } else {
                                cron_InsertNotificationLogEntryFaxsms($TYPE, $prow, $db_sms_msg);
                            }
                        }
                        if (!$isValid) {
                            $strMsg .= "<strong style='color:red'>\n* " . xlt("INVALID Mobile Phone#") . text('$prow["phone_cell"]') . " " . xlt("SMS NOT SENT Patient") . ":</strong>" . text($prow['fname']) . " " . text($prow['lname']) . "</b>";
                            $db_sms_msg['message'] = xlt("Error: INVALID Mobile Phone") . '# ' . text($prow['phone_cell']) . xlt("SMS NOT SENT For") . ": " . text($prow['fname']) . " " . text($prow['lname']);
                            if ($bTestRun == 0) {
                                cron_InsertNotificationLogEntryFaxsms($TYPE, $prow, $db_sms_msg);
                            }
                        } else {
                            $strMsg .= " | " . xlt("SMS SENT SUCCESSFULLY TO") . "<strong> " . text($prow['phone_cell']) . "</strong>";
                            rc_sms_notification_cron_update_entry(NotificationChannel::SMS, $prow['pid'], $prow['pc_eid'], $prow['pc_recurrtype']);
                        }
                        if ((int)$prow['pc_recurrtype'] > 0) {
                            $row = fetchRecurrences($prow['pid']);
                            $strMsg .= "\n<strong>" . xlt("A Recurring") . " " . text($row[0]['pc_catname']) . " " . xlt("Event Occurring") . " " . text($row[0]['pc_recurrspec']) . "</strong>";
                        }
                        $strMsg .= "\n" . text($db_sms_msg['message']) . "\n";
                        echo(nl2br($strMsg));
                    }
                    if ($TYPE == 'EMAIL' && $emailApp != null) {
                        $isValid = $emailApp->validEmail($prow['email']);
                        if ($bTestRun == 0 && $isValid) {
                            try {
                                $emailApp->emailReminder(
                                    $prow['email'] ?? '',
                                    $db_sms_msg['message'],
                                );
                                // Success - create notification log entry
                                cron_InsertNotificationLogEntryFaxsms($TYPE, $prow, $db_sms_msg);
                            } catch (InvalidEmailAddressException) {
                                $strMsg .= formatErrorMessage(xlt("Invalid email address"));
                                echo(nl2br($strMsg));
                                continue;
                            } catch (SmtpNotConfiguredException) {
                                $strMsg .= formatErrorMessage(xlt("SMTP not configured"));
                                echo(nl2br($strMsg));
                                continue;
                            } catch (EmailSendFailedException $e) {
                                $strMsg .= formatErrorMessage(xlt("Failed to send email") . ": " . text($e->getMessage()));
                                echo(nl2br($strMsg));
                                continue;
                            } catch (\PHPMailer\PHPMailer\Exception $e) {
                                $strMsg .= formatErrorMessage(xlt("Email error") . ": " . text($e->getMessage()));
                                echo(nl2br($strMsg));
                                continue;
                            }
                        }
                        if (!$isValid) {
                            $strMsg .= "<strong style='color:red'>\n* " . xlt("INVALID Email") . text('$prow["email"]') . " " . xlt("EMAIL NOT SENT Patient") . ":</strong>" . text($prow['fname']) . " " . text($prow['lname']) . "</b>";
                            $db_sms_msg['message'] = xlt("Error: INVALID EMAIL") . '# ' . text($prow['email']) . xlt("EMAIL NOT SENT For") . ": " . text($prow['fname']) . " " . text($prow['lname']);
                            if ($bTestRun == 0) {
                                cron_InsertNotificationLogEntryFaxsms($TYPE, $prow, $db_sms_msg);
                            }
                        } else {
                            $strMsg .= " | " . xlt("EMAILED SUCCESSFULLY TO") . "<strong> " . text($prow['email']) . "</strong>";
                            rc_sms_notification_cron_update_entry(NotificationChannel::EMAIL, $prow['pid'], $prow['pc_eid'], $prow['pc_recurrtype']);
                        }
                        if ((int)$prow['pc_recurrtype'] > 0) {
                            $row = fetchRecurrences($prow['pid']);
                            $strMsg .= "\n<strong>" . xlt("A Recurring") . " " . text($row[0]['pc_catname']) . " " . xlt("Event Occurring") . " " . text($row[0]['pc_recurrspec']) . "</strong>";
                        }
                        $strMsg .= "\n" . text($db_sms_msg['message']) . "\n";
                        echo(nl2br($strMsg));
                    }
                }
            }
            unset($clientApp);
            unset($emailApp);
            echo "<br /><h2>" . xlt("Done!") . "</h2>";
            ?>
        </div>
    </body>
    </html>

<?php
function isValidPhone($phone): array|bool|string|null
{
    $justNums = preg_replace("/[^0-9]/", '', (string) $phone);
    if (strlen((string) $justNums) === 11) {
        $justNums = preg_replace("/^1/", '', (string) $justNums);
    }
    //if we have 10 digits left, it's probably valid.
    if (strlen((string) $justNums) === 10) {
        return $justNums;
    } else {
        return false;
    }
}

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

    if ($bTestRun || (int)trim($recur) > 0) {
        return;
    }

    $column = match ($channel) {
        NotificationChannel::SMS   => 'pc_sendalertsms',
        NotificationChannel::EMAIL => 'pc_sendalertemail',
    };

    $query = "UPDATE openemr_postcalendar_events SET {$column} = 'YES' WHERE pc_pid = ? AND pc_eid = ?";

    QueryUtils::sqlStatementThrowException($query, [$pid, $pc_eid]);
}

/**
 * Cron Get Alert Patient Data
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
    $adj_date = (int)date("H") + $notificationHour;
    $check_date = date("Y-m-d", mktime($adj_date, 0, 0, (int)date("m"), (int)date("d"), (int)date("Y")));

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
            $recurringEids[(string)$pcEid] = true;
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
                $sentNotifications[(string)$eid][$date] = true;
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
            && isset($sentNotifications[(string)$pcEid][$eventDate])
        ) {
            continue;
        }
        $normalized[] = $event;
    }
    return $normalized;
}

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

/**
 * Format an error message for display
 *
 * @param string $message The translated error message
 * @return string Formatted error message with HTML
 */
function formatErrorMessage(string $message): string
{
    return " | " . xlt("Error:") . " <strong>" . $message . "</strong>\n";
}

/**
 * Cron Insert Notification Log Entry
 *
 * @param string $type
 * @param array  $prow
 * @param array  $db_sms_msg
 * @return void
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

/**
 * Cron Set Message
 *
 * @param array $prow
 * @param array $db_sms_msg
 * @return string
 */
function cron_SetMessage($prow, $db_sms_msg): string
{
    $NAME = $prow['title'] . " " . $prow['fname'] . " " . $prow['mname'] . " " . $prow['lname'];
    $apptProvider = $prow['utitle'] . ' ' . $prow['ufname'] . ' ' . $prow['ulname'];
    $PROVIDER = $apptProvider;
    $ORG = $prow['name'];
    $dtWrk = strtotime($prow['pc_eventDate'] . ' ' . $prow['pc_startTime']);
    $DATE = date('l F j, Y', $dtWrk);
    $STARTTIME = date('g:i A', $dtWrk);
    $ENDTIME = $prow['pc_endTime'];
    $find_array = ["***NAME***", "***PROVIDER***", "***DATE***", "***STARTTIME***", "***ENDTIME***", "***ORG***"];
    $replace_array = [$NAME, $PROVIDER, $DATE, $STARTTIME, $ENDTIME, $ORG];
    $message = str_replace($find_array, $replace_array, $db_sms_msg['message']);
    $message = text($message);

    return $message;
}

function displayHelp(): void
{
    //echo text($helpt);
    $help =
        <<<HELP

Usage:   php rc_sms_notification.php [options]
Example: php rc_sms_notification.php site=default user=admin type=sms testrun=1
--help  Display this help message
Options:
  site={site_id}    Site
  user={authUser}   Authorized username not id.
  type={sms}        Send method SMS or email.
  testrun={1}       Test run set to 1

HELP;

    echo text($help);
}
