<?php
////////////////////////////////////////////////////////////////////
// Package:	rc_sms_cron_notification
// Purpose:	to be run by cron every hour, look for appointments
//		in the pre-notification period and send an sms reminder
//
// Created by:
// Updated by:	Larry Lart on 11/03/2008
// Updated by:	Jerry Padgett on 06/19/2018
// Rework of original
////////////////////////////////////////////////////////////////////
//hack add for command line version
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME'] = 'localhost';
$backpic = "";

// for cron
if ($argc > 1 && empty($_SESSION['site_id']) && empty($_GET['site'])) {
    $c = stripos($argv[1], 'site=');
    if ($c === false) {
        echo xlt("Missing Site Id using default") . "\n";
        $argv[1] = "site=default";
    }
    $args = explode('=', $argv[1]);
    $_GET['site'] = isset($args[1]) ? $args[1] : 'default';
}

$ignoreAuth = 1;
require_once("../../interface/globals.php");
require_once("$srcdir/appointments.inc.php");
require_once("./libs/controller/oeFaxSMSClient.php");

$clientApp = new oeFaxSMSClient();

session_write_close();
set_time_limit(0);

$cred = $clientApp->getCredentials();
$SMS_NOTIFICATION_HOUR = $cred['smsHours'];
$MESSAGE = $cred['smsMessage'];

// check command line for quite option
$bTestRun = isset($_REQUEST['dryrun']) ? 1 : 0;
if ($argc > 1 && $argv[2] == 'test') {
    $bTestRun = 1;
}

$TYPE = "SMS";
$CRON_TIME = 150;

$curr_date = date("Y-m-d");
$curr_time = time();
$check_date = date("Y-m-d", mktime(date("h") + $SMS_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));

//$db_sms_msg = cron_getNotificationData($TYPE);
$db_sms_msg['sms_gateway_type'] = "SMS";
$db_sms_msg['message'] = $MESSAGE;
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Conrjob - SMS Notification</title>
    </head>
    <style>
        html {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            font-size: 12px;
        }
    </style>
<body>
    <div>
    <center>
        <p>
        <h2><?php echo xlt("Working and may take a few minutes to finish.") ?></h2></p>
    </center>
    ";
<?php
if ($bTestRun) {
    echo xlt("We are in Test Mode and no reminders will be sent. This will check what reminders will be sent in Live Mode.");
}

$db_patient = cron_getAlertpatientData($TYPE);
echo "\n<br>" . xlt('Total of') . ": " . count($db_patient) . " " . xlt('Reminders Found') . " " . ($bTestRun ? xlt("and not Sending for reminders") . " " : xlt("and Sending for reminders ")) . $SMS_NOTIFICATION_HOUR . xlt("hrs from now.");
ob_flush();
flush();
// for every event found
$plast = '';
echo "<h3>========================" . $TYPE . " | " . date("Y-m-d H:i:s") . "=========================</h3>";
for ($p = 0; $p < count($db_patient); $p++) {
    ob_flush();
    flush();
    $prow = $db_patient[$p];
    $db_sms_msg['sms_gateway_type'] = "RCSMS";
    $db_sms_msg['message'] = $MESSAGE;

    $app_date = $prow['pc_eventDate'] . " " . $prow['pc_startTime'];
    $app_time = strtotime($app_date);

    $app_time_hour = round($app_time / 3600);
    $curr_total_hour = round(time() / 3600);

    $remaining_app_hour = round($app_time_hour - $curr_total_hour);
    $remain_hour = round($remaining_app_hour - $SMS_NOTIFICATION_HOUR);

    if ($plast != $prow['ulname']) {
        echo "<h4>For Provider: " . $prow['utitle'] . ' ' . $prow['ufname'] . ' ' . $prow['ulname'] . "</h4>";
        $plast = $prow['ulname'];
    }
    $strMsg = "<b>* SEND NOTIFICATION BEFORE:" . $SMS_NOTIFICATION_HOUR . " | CRONJOB RUN EVERY:" . $CRON_TIME . " | APPDATETIME: " . $app_date . " | REMAINING APP HOUR:" . ($remaining_app_hour) . " | SEND ALERT AFTER:" . ($remain_hour) . "";

    // check in the interval
    if ($remain_hour >= -($CRON_TIME) && $remain_hour <= $CRON_TIME) {
        //set message
        $db_sms_msg['message'] = cron_setmessage($prow, $db_sms_msg);
        $isValid = isValidPhone($prow['phone_cell']);

        // send sms to patient - if not in test mode
        if ($bTestRun == 0 && $isValid) {
            cron_InsertNotificationLogEntry($TYPE, $prow, $db_sms_msg);
            $clientApp->sendSMS(
                $prow['phone_cell'],
                $db_sms_msg['email_subject'],
                $db_sms_msg['message'],
                $db_sms_msg['email_sender']
            );
        }


        if (!$isValid) {
            $strMsg .= "<strong style='color:red'>\n* INVALID Mobile Phone# " . $prow['phone_cell'] . " SMS NOT SENT</strong> Patient: " . $prow['fname'] . " " . $prow['lname'] . "</b>";
            $db_sms_msg[message] = "ERROR: INVALID Mobile Phone# " . $prow['phone_cell'] . " SMS NOT SENT For: " . $prow['fname'] . " " . $prow['lname'];
            if ($bTestRun == 0)
                cron_InsertNotificationLogEntry($TYPE, $prow, $db_sms_msg);
        } else {
            $strMsg .= " | SENT SUCCESSFULLY TO <strong>" . $prow['phone_cell'] . "</strong></b>";
            cron_updateentry($TYPE, $prow['pid'], $prow['pc_eid'], $prow['pc_recurrtype']);
        }
        if ((int)$prow['pc_recurrtype'] > 0) {
            $row = fetchRecurrences($prow['pid']);
            $strMsg .= "\n<b>A Recurring " . $row[0]['pc_catname'] . " Event Occuring " . $row[0]['pc_recurrspec'] . "</b> Note: Appointment status left unchanged.";
        }
        $strMsg .= "\n" . $db_sms_msg['message'] . "\n";
        echo nl2br($strMsg);
    }
}

unset($clientApp);

echo "\n<br><h2>Done!</h2>
</div>
</body>
</html>";

function isValidPhone($phone)
{
    $justNums = preg_replace("/[^0-9]/", '', $phone);
    if (strlen($justNums) === 11)
        $justNums = preg_replace("/^1/", '', $justNums);
    //if we have 10 digits left, it's probably valid.
    if (strlen($justNums) === 10)
        return $justNums;
    else
        return false;

}

// integrate cron functions into this script.
// borrowed from cron_functions.php
//
////////////////////////////////////////////////////////////////////
// Function:	cron_updateentry
// Purpose:	update status yes if alert send to patient
////////////////////////////////////////////////////////////////////
function cron_updateentry($type, $pid, $pc_eid, $recur = '')
{
    global $bTestRun;

    if ($bTestRun || (int)trim($recur) > 0) return 1;

    $query = "UPDATE openemr_postcalendar_events SET ";

    if ($type == 'SMS') {
        $query .= " pc_sendalertsms='YES', pc_apptstatus='SMS' ";
    } else {
        $query .= " pc_sendalertemail='YES' ";
    }

    $query .= " where pc_pid=? and pc_eid=? ";
    $db_sql = sqlStatement($query, array($pid, $pc_eid));
}

////////////////////////////////////////////////////////////////////
// Function:	cron_getAlertpatientData
// Purpose:	get patient data for send to alert
////////////////////////////////////////////////////////////////////
function cron_getAlertpatientData($type)
{
    global $SMS_NOTIFICATION_HOUR, $EMAIL_NOTIFICATION_HOUR;
    $where = " AND (p.hipaa_allowsms='YES' AND p.phone_cell<>'' AND e.pc_sendalertsms != 'YES' AND e.pc_apptstatus != 'x')";
    $check_date = date("Y-m-d", mktime(date("h") + $SMS_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));
    $patient_array = fetchEvents($check_date, $check_date, $where, 'u.lname,pc_startTime,p.lname');

    return $patient_array;
}

////////////////////////////////////////////////////////////////////
// Function:	cron_getNotificationData
// Purpose:	get alert notification data
////////////////////////////////////////////////////////////////////
function cron_getNotificationData($type)
{
    $db_sms_msg['notification_id'] = '';
    $db_sms_msg['sms_gateway_type'] = '';

    $query = "select * from automatic_notification where type='$type' ";
    //echo "<br>".$query;
    $db_sms_msg = sqlFetchArray(sqlStatement($query));
    return $db_sms_msg;
}

////////////////////////////////////////////////////////////////////
// Function:	cron_InsertNotificationLogEntry
// Purpose:	insert log entry in table
////////////////////////////////////////////////////////////////////
function cron_InsertNotificationLogEntry($type, $prow, $db_sms_msg)
{
    global $SMS_GATEWAY_USENAME, $SMS_GATEWAY_PASSWORD, $SMS_GATEWAY_APIKEY;
    if ($type == 'SMS') {
        $smsgateway_info = ""; //$db_sms_msg['sms_gateway_type'] . "|||" . $SMS_GATEWAY_USENAME . "|||" . $SMS_GATEWAY_PASSWORD . "|||" . $SMS_GATEWAY_APIKEY;
    } else {
        $smsgateway_info = $db_sms_msg['email_sender'] . "|||" . $db_sms_msg['email_subject'];
    }

    $patient_info = $prow['title'] . " " . $prow['fname'] . " " . $prow['mname'] . " " . $prow['lname'] . "|||" . $prow['phone_cell'] . "|||" . $prow['email'];
    $data_info = $prow['pc_eventDate'] . "|||" . $prow['pc_endDate'] . "|||" . $prow['pc_startTime'] . "|||" . $prow['pc_endTime'];
    $sdate = date("Y-m-d H:i:s");
    $sql_loginsert = "INSERT INTO `notification_log` ( `iLogId` , `pid` , `pc_eid` , `sms_gateway_type` , `message` , `type` , `patient_info` , `smsgateway_info` , `pc_eventDate` , `pc_endDate` , `pc_startTime` , `pc_endTime` , `dSentDateTime` ) VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?)";

    $safe = array($prow[pid], $prow[pc_eid], $db_sms_msg[sms_gateway_type], $db_sms_msg[message], $db_sms_msg[type] || '', $patient_info, $smsgateway_info, $prow[pc_eventDate], $prow[pc_endDate], $prow[pc_startTime], $prow[pc_endTime], $sdate);

    $db_loginsert = sqlStatement($sql_loginsert, $safe);
}

////////////////////////////////////////////////////////////////////
// Function:	cron_setmessage
// Purpose:	set the message
////////////////////////////////////////////////////////////////////
function cron_setmessage($prow, $db_sms_msg)
{
    // larry :: debug
    //echo "\nDEBUG :cron_setmessage: set message ".$prow['title']." ".$prow['fname']." ".$prow['mname']." ".$prow['lname']."\n";

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
    $message = htmlspecialchars($message);
    return $message;
}

////////////////////////////////////////////////////////////////////
// Function:	cron_GetNotificationSettings
// Purpose:	get notification settings
////////////////////////////////////////////////////////////////////
function cron_GetNotificationSettings()
{
    $strQuery = "SELECT * FROM notification_settings WHERE type='SMS/Email Settings'";
    $vectNotificationSettings = sqlFetchArray(sqlStatement($strQuery));

    return ($vectNotificationSettings);
}

