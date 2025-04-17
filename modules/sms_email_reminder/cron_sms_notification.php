<?php

////////////////////////////////////////////////////////////////////
// Package: sms_cron_notification
// Purpose: to be run by cron every hour, look for appointments
//      in the pre-notification period and send an sms reminder
//
// Created by:
// Updated by:  Larry Lart on 11/03/2008
////////////////////////////////////////////////////////////////////

// comment below exit if plan to use this script
exit;

// larry :: hack add for command line version
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME'] = 'localhost';
$backpic = "";

// email notification
$ignoreAuth = 1;
include_once("../../interface/globals.php");
include_once("cron_functions.php");

// check command line for quite option
$bTestRun = 0;
if ($argc > 1 && $argv[1] == 'test') {
    $bTestRun = 1;
}

$TYPE = "SMS";
$CRON_TIME = 5;

$curr_date = date("Y-m-d");
$curr_time = time();
$check_date = date("Y-m-d", mktime(date("h") + $SMS_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));

// larry :: move this in the loop to keep it fresh - perhaps try to use it without change
// it's content - to do latter
$db_email_msg = cron_getNotificationData($TYPE);

// object for sms
global $mysms;
if ($db_email_msg['sms_gateway_type'] == 'CLICKATELL') {
    include_once("sms_clickatell.php");
} elseif ($db_email_msg['sms_gateway_type'] == 'TMB4') {
    include_once("sms_tmb4.php");
}

// get notification settings
$vectNotificationSettings = cron_GetNotificationSettings();
$SMS_GATEWAY_USENAME = $vectNotificationSettings['SMS_gateway_username'];
$SMS_GATEWAY_PASSWORD = $vectNotificationSettings['SMS_gateway_password'];
$SMS_GATEWAY_APIKEY = $vectNotificationSettings['SMS_gateway_apikey'];
// set cron time (time to event ?) - todo extra tests
$CRON_TIME = $vectNotificationSettings['Send_SMS_Before_Hours'];

//echo "\nDEBUG :: user=".$vectNotificationSettings['SMS_gateway_username']."\n";

// create sms object
$mysms = new sms($SMS_GATEWAY_USENAME, $SMS_GATEWAY_PASSWORD, $SMS_GATEWAY_APIKEY);

$db_patient = cron_getAlertpatientData($TYPE);
echo "\n<br />Total " . text(count($db_patient)) . " Records Found";

// for every event found
for ($p = 0; $p < count($db_patient); $p++) {
    $prow = $db_patient[$p];

    //echo "\n-----\nDEBUG :cron_sms: found patient = ".$prow['fname']." ".$prow['lname']."\n";

    // my_print_r($prow);
    /*
    if($prow['pc_eventDate'] < $check_date)
    {
        $app_date = date("Y-m-d")." ".$prow['pc_startTime'];
    }else{
        $app_date = $prow['pc_eventDate']." ".$prow['pc_startTime'];
    }
    */
    $app_date = $prow['pc_eventDate'] . " " . $prow['pc_startTime'];
    $app_time = strtotime($app_date);

    $app_time_hour = round($app_time / 3600);
    $curr_total_hour = round(time() / 3600);

    $remaining_app_hour = round($app_time_hour - $curr_total_hour);
    $remain_hour = round($remaining_app_hour - $SMS_NOTIFICATION_HOUR);

    // larry :: debug
    //echo "\nDEBUG :: checkdate=$check_date, app_date=$app_date, apptime=$app_time remain_hour=$remain_hour -- CRON_TIME=$CRON_TIME\n";

    // build log message
    $strMsg = "\n========================" . $TYPE . " || " . date("Y-m-d H:i:s") . "=========================";
    $strMsg .= "\nSEND NOTIFICATION BEFORE:" . $SMS_NOTIFICATION_HOUR . " || CRONJOB RUN EVERY:" . $CRON_TIME . " || APPDATETIME:" . $app_date . " || REMAINING APP HOUR:" . ($remaining_app_hour) . " || SEND ALERT AFTER:" . ($remain_hour);

    // check in the interval
    if ($remain_hour >= -($CRON_TIME) &&  $remain_hour <= $CRON_TIME) {
        // insert entry in notification_log table
        cron_InsertNotificationLogEntry($TYPE, $prow, $db_email_msg);

        //set message
        $db_email_msg['message'] = cron_setmessage($prow, $db_email_msg);

        // send sms to patinet - if not in test mode
        if ($bTestRun == 0) {
            cron_SendSMS(
                $prow['phone_cell'],
                $db_email_msg['email_subject'],
                $db_email_msg['message'],
                $db_email_msg['email_sender']
            );
        }

        // larry :: debug
        echo "\nDEBUG :: sms was sent to= " . text($prow['phone_cell']) .
                    " \nsender= " . text($db_email_msg['email_sender']) .
                    " \nsbj= " . text($db_email_msg['email_subject']) .
                    " \nmsg= " . text($db_email_msg['message']) . "\n";

        //update entry >> pc_sendalertsms='Yes'
        cron_updateentry($TYPE, $prow['pid'], $prow['pc_eid']);

        $strMsg .= " || ALERT SENT SUCCESSFULLY TO " . $prow['phone_cell'];
        $strMsg .= "\n" . $patient_info . "\n" . $smsgateway_info . "\n" . $data_info . "\n" . $db_email_msg['message'];
    }

    // write logs for every reminder sent
    WriteLog($strMsg);

    // larry :: update notification data again - todo :: fix change in cron_updateentry
    $db_email_msg = cron_getNotificationData($TYPE);
}

unset($mysms);
sqlClose();

?>

<html>
<head>
<title>Conrjob - SMS Notification</title>
</head>
<body>
    <center>
    </center>
</body>
</html>
