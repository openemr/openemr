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
 */

//hack add for command line version
use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME'] = 'localhost';
$backpic = "";
$clientApp = null;
$emailApp = null;
// for cron
$error = '';
$runtime = [];

// Check for other arguments and perform your script logic
if (($argc ?? null) > 1) {
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
require_once("$srcdir/appointments.inc.php");

// Check for help argument
if ($argc > 1 && (in_array('--help', $argv) || in_array('-h', $argv))) {
    displayHelp();
    exit(0);
}

if (empty($runtime['site']) && empty($_SESSION['site_id']) && empty($_GET['site'])) {
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

$CRON_TIME = 150;
// use service if needed
if ($TYPE === "SMS") {
    $_SESSION['authUser'] = $runtime['user'] ?? $_SESSION['authUser'];
    $clientApp = AppDispatch::getApiService('sms');
    $cred = $clientApp->getCredentials();

    if (!$clientApp->verifyAcl('patients', 'appt', $runtime['user'] ?? '')) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
}
if ($TYPE === "EMAIL") {
    $_SESSION['authUser'] = $runtime['user'] ?? $_SESSION['authUser'];
    $emailApp = AppDispatch::getApiService('email');
    $cred = $emailApp->getEmailSetup();

    if (!$emailApp->verifyAcl('patients', 'appt', $runtime['user'] ?? '')) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
}
// close writes
session_write_close();
set_time_limit(0);

$SMS_NOTIFICATION_HOUR = $cred['smsHours'] ?? $cred['notification_hours'] ?? 24;
$MESSAGE = $cred['smsMessage'] ?? $cred['email_message'];

// check command line for quite option
$bTestRun = isset($_REQUEST['dryrun']) ? 1 : 0;
if (!empty($runtime['testrun'])) {
    $bTestRun = 1;
}

$curr_date = date("Y-m-d");
$curr_time = time();
$check_date = date("Y-m-d", mktime((date("h") + $SMS_NOTIFICATION_HOUR), 0, 0, date("m"), date("d"), date("Y")));

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
            $db_patient = cron_GetAlertPatientData();
            echo "\n<br>" . xlt('Total of') . ": " . count($db_patient ?? []) . " " . xlt('Reminders Found') . " " . ($bTestRun ? xlt("and will be sending for reminders") . " " : xlt("and Sending for reminders ")) . ' ' . text($SMS_NOTIFICATION_HOUR) . ' ' . xlt("hrs from now.");
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
                $strMsg = "<strong>* " . xlt("SEND NOTIFICATION BEFORE:") . text($SMS_NOTIFICATION_HOUR) . " | " . xlt("CRONJOB RUNS EVERY:") . text($CRON_TIME) . " | " . xlt("APPOINTMENT DATE TIME") . ': ' . $app_date . " | " . xlt("APPOINTMENT REMAINING HOURS") . ": " . text($remaining_app_hour) . " | " . xlt("SEND ALERT AFTER") . ': ' . text($remain_hour) . "</strong>";

                // check in the interval
                if ($remain_hour >= -($CRON_TIME) && $remain_hour <= $CRON_TIME) {
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
                            if (stripos($error, 'error') !== false) {
                                $strMsg .= " | " . xlt("Error:") . "<strong>" . text($error) . "</strong>\n";
                                error_log($strMsg); // text
                                echo(nl2br($strMsg));
                                continue;
                            } else {
                                cron_InsertNotificationLogEntry($TYPE, $prow, $db_sms_msg);
                            }
                        }
                        if (!$isValid) {
                            $strMsg .= "<strong style='color:red'>\n* " . xlt("INVALID Mobile Phone#") . text('$prow["phone_cell"]') . " " . xlt("SMS NOT SENT Patient") . ":</strong>" . text($prow['fname']) . " " . text($prow['lname']) . "</b>";
                            $db_sms_msg['message'] = xlt("Error: INVALID Mobile Phone") . '# ' . text($prow['phone_cell']) . xlt("SMS NOT SENT For") . ": " . text($prow['fname']) . " " . text($prow['lname']);
                            if ($bTestRun == 0) {
                                cron_InsertNotificationLogEntry($TYPE, $prow, $db_sms_msg);
                            }
                        } else {
                            $strMsg .= " | " . xlt("SMS SENT SUCCESSFULLY TO") . "<strong> " . text($prow['phone_cell']) . "</strong>";
                            cron_UpdateEntry($TYPE, $prow['pid'], $prow['pc_eid'], $prow['pc_recurrtype']);
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
                                $error = $emailApp->emailReminder(
                                    $prow['email'] ?? '',
                                    $db_sms_msg['message'],
                                );
                            } catch (\PHPMailer\PHPMailer\Exception $e) {
                                $error = 'Error' . ' ' . $e->getMessage();
                            }
                            if (stripos($error, 'error') !== false) {
                                $strMsg .= " | " . xlt("Error:") . "<strong> " . text($error) . "</strong>\n";
                                echo(nl2br($strMsg));
                                continue;
                            } else {
                                cron_InsertNotificationLogEntry($TYPE, $prow, $db_sms_msg);
                            }
                        }
                        if (!$isValid) {
                            $strMsg .= "<strong style='color:red'>\n* " . xlt("INVALID Email") . text('$prow["email"]') . " " . xlt("EMAIL NOT SENT Patient") . ":</strong>" . text($prow['fname']) . " " . text($prow['lname']) . "</b>";
                            $db_sms_msg['message'] = xlt("Error: INVALID EMAIL") . '# ' . text($prow['email']) . xlt("EMAIL NOT SENT For") . ": " . text($prow['fname']) . " " . text($prow['lname']);
                            if ($bTestRun == 0) {
                                cron_InsertNotificationLogEntry($TYPE, $prow, $db_sms_msg);
                            }
                        } else {
                            $strMsg .= " | " . xlt("EMAILED SUCCESSFULLY TO") . "<strong> " . text($prow['email']) . "</strong>";
                            cron_UpdateEntry($TYPE, $prow['pid'], $prow['pc_eid'], $prow['pc_recurrtype']);
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
    $justNums = preg_replace("/[^0-9]/", '', $phone);
    if (strlen($justNums) === 11) {
        $justNums = preg_replace("/^1/", '', $justNums);
    }
    //if we have 10 digits left, it's probably valid.
    if (strlen($justNums) === 10) {
        return $justNums;
    } else {
        return false;
    }
}

/**
 * Integrate cron functions into this script
 *
 * Borrowed from cron_functions.php. Update status yes if alert send to patient
 *
 * @param string $type
 * @param int    $pid
 * @param int    $pc_eid
 * @param string $recur
 * @return int
 */
function cron_UpdateEntry($type, $pid, $pc_eid, $recur = '')
{
    global $bTestRun;

    if ($bTestRun || (int)trim($recur) > 0) {
        return 1;
    }

    $query = "UPDATE openemr_postcalendar_events SET";

    if ($type == 'SMS') {
        $query .= " pc_sendalertsms='YES', pc_apptstatus='SMS' ";
    } elseif ($type == 'EMAIL') {
        $query .= " pc_sendalertemail='YES', pc_apptstatus='EMAIL' ";
    } else {
        $query .= " pc_sendalertsms='NO' ";
    }

    $query .= " where pc_pid=? and pc_eid=? ";

    return sqlStatement($query, array($pid, $pc_eid));
}

/**
 * Cron Get Alert Patient Data
 * *
 *
 * @param $type
 * @return array
 */
function cron_GetAlertPatientData()
{
    global $SMS_NOTIFICATION_HOUR, $TYPE;
    $where = " AND (p.hipaa_allowsms='YES' AND p.phone_cell<>'' AND (e.pc_sendalertsms != 'YES' || e.pc_apptstatus != 'SMS') AND e.pc_apptstatus != 'x')";
    if ($TYPE == 'EMAIL') {
        $where = " AND (p.hipaa_allowemail='YES' AND p.email<>'' AND (e.pc_sendalertemail != 'YES' || e.pc_apptstatus != 'EMAIL') AND e.pc_apptstatus != 'x')";
    }
    $adj_date = date("h") + $SMS_NOTIFICATION_HOUR;
    $check_date = date("Y-m-d", mktime($adj_date, 0, 0, date("m"), date("d"), date("Y")));
    $patient_array = fetchEvents($check_date, $check_date, $where, 'u.lname,pc_startTime,p.lname');

    return $patient_array;
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
 * Cron Insert Notification Log Entry
 *
 * @param string $type
 * @param array  $prow
 * @param array  $db_sms_msg
 * @return void
 */
function cron_InsertNotificationLogEntry($type, $prow, $db_sms_msg): void
{
    if ($type == 'SMS') {
        $smsgateway_info = "";
    } else {
        $smsgateway_info = $db_sms_msg['email_sender'] . "|||" . $db_sms_msg['email_subject'];
    }

    $patient_info = $prow['title'] . " " . $prow['fname'] . " " . $prow['mname'] . " " . $prow['lname'] . "|||" . $prow['phone_cell'] . "|||" . $prow['email'];
    $data_info = $prow['pc_eventDate'] . "|||" . $prow['pc_endDate'] . "|||" . $prow['pc_startTime'] . "|||" . $prow['pc_endTime'];
    $sdate = date("Y-m-d H:i:s");
    $sql_loginsert = "INSERT INTO `notification_log` (`iLogId` , `pid` , `pc_eid` , `sms_gateway_type` , `message` , `type` , `patient_info` , `smsgateway_info` , `pc_eventDate` , `pc_endDate` , `pc_startTime` , `pc_endTime` , `dSentDateTime`) VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?)";

    $safe = array($prow['pid'], $prow['pc_eid'], $db_sms_msg['sms_gateway_type'], $db_sms_msg['message'], $db_sms_msg['type'], $patient_info, $smsgateway_info, $prow['pc_eventDate'], $prow['pc_endDate'], $prow['pc_startTime'], $prow['pc_endTime'], $sdate);

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
    $find_array = array("***NAME***", "***PROVIDER***", "***DATE***", "***STARTTIME***", "***ENDTIME***", "***ORG***");
    $replace_array = array($NAME, $PROVIDER, $DATE, $STARTTIME, $ENDTIME, $ORG);
    $message = str_replace($find_array, $replace_array, $db_sms_msg['message']);
    $message = text($message);

    return $message;
}

/**
 * Get Notification Settings
 *
 * @return array|false
 */
function cron_GetNotificationSettings(): bool|array
{
    $strQuery = "SELECT * FROM notification_settings WHERE type='SMS/Email Settings'";
    $vectNotificationSettings = sqlFetchArray(sqlStatement($strQuery));

    return ($vectNotificationSettings);
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
