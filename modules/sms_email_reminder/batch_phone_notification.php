<?php

/*
 * Purpose: to be run by cron every hour, look for appointments
 * in the pre-notification period and send an phone reminder
 * Based on cron_email_notification by Larry Lart
 *
 * @package OpenEMR
 * @author Maviq <info@maviq.com>
 * @copyright Copyright (c) 2010 Maviq <info@maviq.com>
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Enable this script via environment variable
if (!getenv('OPENEMR_ENABLE_BATCH_PHONE_NOTIFICATION')) {
    die('Set OPENEMR_ENABLE_BATCH_PHONE_NOTIFICATION=1 environment variable to enable this script');
}

$backpic = "";
//phone notification
$ignoreAuth = 1;

//Set the working directory to the path of the file
$current_dir = dirname((string) $_SERVER['SCRIPT_FILENAME']);
chdir($current_dir);



require_once("../../interface/globals.php");
require_once("$srcdir/maviq_phone_api.php");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$type = "Phone";
$before_trigger_hours = 72; // 3 days is default
//Get the values from Global
$before_trigger_hours = $GLOBALS['phone_notification_hour'];
//set up the phone notification settings for external phone service
$phone_url =    $GLOBALS['phone_gateway_url'] ;
$phone_id = $GLOBALS['phone_gateway_username'];
$cryptoGen = new CryptoGen();
$phone_token = $cryptoGen->decryptStandard($GLOBALS['phone_gateway_password']);
$phone_time_range = $GLOBALS['phone_time_range'];

//get the facility_id-message map
$facilities = cron_getFacilitiesMap($facilityService);
//print_r($facilities);
$fac_phone_map = $facilities['phone_map'];
$fac_msg_map = $facilities['msg_map'];

// get patient data for send alert
$db_patient = cron_getPhoneAlertpatientData($type, $before_trigger_hours);
echo "<br />" . htmlspecialchars(xl("Total Records Found") . ": " . count($db_patient), ENT_QUOTES);

//Create a new instance of the phone service client
$client = new MaviqClient($phone_id, $phone_token, $phone_url);

for ($p = 0; $p < count($db_patient); $p++) {
    $prow = $db_patient[$p];

    //Get the apptDate and apptTime
    $p_date = $prow['pc_eventDate'];
    //Need to format date to m/d/Y for Maviq API
    $pieces = explode("-", (string) $p_date);
    $appt_date = date("m/d/Y", mktime(0, 0, 0, $pieces[1], $pieces[2], $pieces[0]));
    $appt_time = $prow['pc_startTime'];
    //get the greeting
    $greeting = $fac_msg_map[$prow['pc_facility']];
    if ($greeting == null) {
            //Use the default when the message is not found
            $greeting = $GLOBALS['phone_appt_message']['Default'];
    }

    //Set up the parameters for the call
    $data = [
                "firstName" => $prow['fname'],
                "lastName" => $prow['lname'],
                "phone" => $prow['phone_home'],
        "apptDate" => $appt_date,
        "apptTime" => $appt_time,
        "doctor" => $prow['pc_aid'],
        "greeting" => $greeting,
        "timeRange" => $phone_time_range,
        "type" => "appointment",
        "timeZone" => date('P'),
        "callerId" => $fac_phone_map[$prow['pc_facility']]
        ];

    //Make the call
    $response = $client->sendRequest("appointment", "POST", $data);

        // check response for success or error
    if ($response->IsError) {
        $strMsg =   "Error starting phone call for {$prow['fname']} | {$prow['lname']} | {$prow['phone_home']} | {$appt_date} | {$appt_time} | {$response->ErrorMessage}\n";
    } else {
        $strMsg = "\n========================" . $type . " || " . date("Y-m-d H:i:s") . "=========================";
        $strMsg .= "\nPhone reminder sent successfully: {$prow['fname']} | {$prow['lname']} |	| {$prow['phone_home']} | {$appt_date} | {$appt_time} ";
        // insert entry in notification_log table
        cron_InsertNotificationLogEntrySmsEmail($prow, $greeting, $phone_url);

    //update entry >> pc_sendalertsms='Yes'
        cron_updateentry($type, $prow['pid'], $prow['pc_eid']);
    }

    //echo $strMsg;
    sms_reminder_WriteLog($strMsg);
}

sqlClose();

function cron_InsertNotificationLogEntrySmsEmail($prow, $phone_msg, $phone_gateway): void
{
    $patient_info = $prow['title'] . " " . $prow['fname'] . " " . $prow['mname'] . " " . $prow['lname'] . "|||" . $prow['phone_home'];

    $message = $phone_msg;

    $sql_loginsert = "INSERT INTO `notification_log` ( `iLogId` , `pid` , `pc_eid` , `message`, `type` , `patient_info` , `smsgateway_info` , `pc_eventDate` , `pc_endDate` , `pc_startTime` , `pc_endTime` , `dSentDateTime` ) VALUES ";
    $sql_loginsert .= "(NULL , ?, ?, ?, 'Phone', ?, ?, ?, ?, ?, ?, ?)";
    $db_loginsert = ( sqlStatement($sql_loginsert, [$prow['pid'], $prow['pc_eid'], $message, $patient_info, $phone_gateway, $prow['pc_eventDate'], $prow['pc_endDate'], $prow['pc_startTime'], $prow['pc_endTime'], date("Y-m-d H:i:s")]));
}

/**
 * Write log into file.
 *
 * @param string $data
 */
function sms_reminder_WriteLog($data): void
{
    $log_file = $GLOBALS['phone_reminder_log_dir'];

    if ($log_file != null) {
        $filename = $log_file . "/" . "phone_reminder_cronlog_" . date("Ymd") . ".html";

        if (!$fp = fopen($filename, 'a')) {
            print "Cannot open file ($filename)";
        } else {
            $sdata = "\n====================================================================\n";

            if (!fwrite($fp, $data . $sdata)) {
                print "Cannot write to file ($filename)";
            }

            fclose($fp);
        }
    }
}
