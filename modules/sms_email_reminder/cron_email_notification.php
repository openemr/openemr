<?php

/*
 * Purpose: to be run by cron every hour, look for appointments
 * in the pre-notification period and send an email reminder
 *
 * @package OpenEMR
 * @author Larry Lart
 * @copyright Copyright (c) 2008 Larry Lart
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// comment below exit if plan to use this script
exit;

// larry :: hack add for command line version
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME'] = 'localhost';
$backpic = "";

// email notification
$ignoreAuth = 1;
require_once("../../interface/globals.php");
require_once("cron_functions.php");

$TYPE = "Email";
$CRON_TIME = 5;

// set cron time (time to event ?) - todo extra tests
$vectNotificationSettings = cron_GetNotificationSettings();
$CRON_TIME = $vectNotificationSettings['Send_Email_Before_Hours'];

$check_date = date("Y-m-d", mktime(date("h") + $EMAIL_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));


// get data from automatic_notification table
$db_email_msg = cron_getNotificationData($TYPE);
//my_print_r($db_email_msg);

// get patient data for send alert
$db_patient = cron_getAlertpatientData($TYPE);
echo "<br />Total " . count($db_patient) . " Records Found\n";
for ($p = 0; $p < count($db_patient); $p++) {
    $prow = $db_patient[$p];
    //my_print_r($prow);
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
    $remain_hour = round($remaining_app_hour - $EMAIL_NOTIFICATION_HOUR);

    $strMsg = "\n========================" . $TYPE . " || " . date("Y-m-d H:i:s") . "=========================";
    $strMsg .= "\nSEND NOTIFICATION BEFORE:" . $EMAIL_NOTIFICATION_HOUR . " || CRONJOB RUN EVERY:" . $CRON_TIME . " || APPDATETIME:" . $app_date . " || REMAINING APP HOUR:" . ($remaining_app_hour) . " || SEND ALERT AFTER:" . ($remain_hour);

    if ($remain_hour >= -($CRON_TIME) &&  $remain_hour <= $CRON_TIME) {
        // insert entry in notification_log table
        cron_InsertNotificationLogEntry($TYPE, $prow, $db_email_msg);

        //set message
        $db_email_msg['message'] = cron_setmessage($prow, $db_email_msg);

        // send mail to patinet
        cron_SendMail(
            $prow['email'],
            $db_email_msg['email_subject'],
            $db_email_msg['message'],
            $db_email_msg['email_sender']
        );

        //update entry >> pc_sendalertemail='Yes'
        cron_updateentry($TYPE, $prow['pid'], $prow['pc_eid']);

        $strMsg .= " || ALERT SENT SUCCESSFULLY TO " . $prow['email'];
        $strMsg .= "\n" . $patient_info . "\n" . $smsgateway_info . "\n" . $data_info . "\n" . $db_email_msg['message'];
    }

    WriteLog($strMsg);

    // larry :: get notification data again - since was updated by cron_updateentry
    // todo :: instead fix not to modify the template aka $db_email_msg
    $db_email_msg = cron_getNotificationData($TYPE);
}

sqlClose();
?>

<html>
<head>
<title>Conrjob - Email Notification</title>
</head>
<body>
    <center>
    </center>
</body>
</html>
