<?php

/**
 * CRON FUNCTIONS - to use with cron_smd(*) and cron_email backend
 * scripts to notify events
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Larry Lart
 * @copyright Copyright (c) 2008 Larry Lart
 * @copyright Copyright (c) 2022 Luis A. Uriarte <luis.uriarte@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

global $smsgateway_info;
global $patient_info;
global $SMS_NOTIFICATION_HOUR;
global $EMAIL_NOTIFICATION_HOUR;

////////////////////////////////////////////////////////////////////
// Function:    cron_SendMail
// Purpose: send mail
// Input:   to, cc, subject and email body
// Output:  status - if sent or not
////////////////////////////////////////////////////////////////////
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use OpenEMR\Common\Crypto\CryptoGen;

function cron_SendMail($to, $cc, $subject, $vBody)
{
    // check if smtp globals set
    if ($GLOBALS['SMTP_HOST'] == '') {
        $mstatus = true;
        $mstatus = @mail($to, $cc, $subject, $vBody);
    } else {
        $SenderName = $GLOBALS['patient_reminder_sender_name'];
        $SenderEmail = $GLOBALS['patient_reminder_sender_email'];
        $mail = new PHPMailer();
        $mail->SMTPDebug = 3;
        $mail->IsSMTP();
        $mail->Host = $GLOBALS['SMTP_HOST'];
        $mail->Port = $GLOBALS['SMTP_PORT'];
        $mail->SMTPAuth = true;
        $mail->Username = $GLOBALS['SMTP_USER'];
        $cryptoGen = new CryptoGen();
        $mail->Password = $cryptoGen->decryptStandard($GLOBALS['SMTP_PASS']);
        $mail->SMTPSecure = $GLOBALS['SMTP_SECURE'];
        $mail->CharSet = "UTF-8";
        $mail->From = $SenderEmail;
        $mail->FromName = $SenderName;
        $mail->AddAddress($to);
        // $mail->addCC($cc); //Remove comment to send, also to trusted mail
        $mail->WordWrap = 50;
        $mail->IsHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $vBody;
        if (!$mail->send()) {
            echo "Cound not send the message to " . text($to) . ".\nError: " . text($mail->ErrorInfo) . "\n";
            $mstatus = false;
        } else {
            echo "Message sent to " . text($to) . " OK.\n";
            $mstatus = true;
        }
        unset($mail);
    }
    return $mstatus;
}

////////////////////////////////////////////////////////////////////
// Function:    WriteLog
// Purpose: written log into file
////////////////////////////////////////////////////////////////////
function WriteLog($data)
{
    if (stripos(PHP_OS, 'win') === 0) {
        $filename = "\\logs\\cronlog_" . date("Y-m-d_H-i-s") . ".html";
    } else {
        $filename = "/logs/cronlog_" . date("Y-m-d_H-i-s") . ".html";
    }
    if (!$fp = fopen(__DIR__ . $filename, 'w+e')) {
        print "Cannot open file (" . text($filename) . ")";
        exit;
    }
    $sdata = "\n====================================================================\n";
    if (!fwrite($fp, $sdata . $data . $sdata)) {
        print "Cannot write to file (" . text($filename) . ")";
        exit;
    }

    fclose($fp);
}

////////////////////////////////////////////////////////////////////
// define my_print_r - used for debuging - if not defined
////////////////////////////////////////////////////////////////////
if (!function_exists('my_print_r')) {
    function my_print_r($data)
    {
        echo "<pre>";
        echo (text(print_r($data, true)));
        echo "</pre>";
    }
}

////////////////////////////////////////////////////////////////////
// Function:    cron_SendSMS
// Purpose: send sms
////////////////////////////////////////////////////////////////////
function cron_SendSMS($to, $subject, $vBody, $from)
{
    global $mysms;
    $cnt = "";
    $cnt .= "\nDate Time :" . date("d M, Y  h:i:s");
    $cnt .= "\nTo : " . $to;
    $cnt .= "\From : " . $from;
    $cnt .= "\nSubject : " . $subject;
    $cnt .= "\nBody : \n" . $vBody . "\n";
    if (1) {
        //WriteLog($cnt);
    }
    $mstatus = true;
    $mysms->send($to, $from, $vBody);
    return $mstatus;
}

////////////////////////////////////////////////////////////////////
// Function:    cron_updateentry
// Purpose: update status yes if alert send to patient
////////////////////////////////////////////////////////////////////
function cron_updateentry($type, $pid, $pc_eid)
{
    $query = "UPDATE openemr_postcalendar_events SET ";
    if ($type == 'SMS') {
        $query .= " openemr_postcalendar_events.pc_sendalertsms='YES' ,  openemr_postcalendar_events.pc_apptstatus='SMS' ";
    } else {
        $query .= " openemr_postcalendar_events.pc_sendalertemail='YES' ,  openemr_postcalendar_events.pc_apptstatus='EMAIL' ";
    }
    $query .= " WHERE openemr_postcalendar_events.pc_pid=?
                AND openemr_postcalendar_events.pc_eid=? ";
    $db_sql = (sqlStatement($query, [$pid, $pc_eid]));
}

////////////////////////////////////////////////////////////////////
// Function:    cron_getAlertpatientData
// Purpose: get patient data for send to alert
////////////////////////////////////////////////////////////////////
function cron_getAlertpatientData($type)
{
    global $SMS_NOTIFICATION_HOUR, $EMAIL_NOTIFICATION_HOUR;

    if ($type == 'SMS') {
        $ssql = " AND pd.hipaa_allowsms='YES' AND pd.phone_cell<>'' AND ope.pc_sendalertsms='NO' ";
        $check_date = date("Y-m-d", mktime(date("h") + $SMS_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));
    } else {
        $ssql = " AND pd.hipaa_allowemail='YES' AND pd.email<>''  AND ope.pc_sendalertemail='NO' ";
        $check_date = date("Y-m-d", mktime(date("h") + $EMAIL_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));
    }
    $patient_field = "pd.pid,pd.title,pd.fname,pd.lname,pd.mname,pd.phone_cell,pd.email,pd.email_direct,pd.hipaa_allowsms,pd.hipaa_allowemail,";
    $ssql .= " AND (ope.pc_eventDate='" . add_escape_custom($check_date) . "')";
    $query = "SELECT $patient_field ope.pc_eid, ope.pc_pid, ope.pc_title,
                    ope.pc_hometext, ope.pc_eventDate, ope.pc_endDate,
                    ope.pc_duration, ope.pc_alldayevent, ope.pc_startTime, ope.pc_endTime,
                    CONCAT(u.fname, ' ', u.mname, ' ', u.lname) user_name, pte.pt_tracker_id,
                    pte.status, pt.lastseq
            FROM openemr_postcalendar_events AS ope
            LEFT OUTER JOIN patient_tracker AS pt ON pt.pid = ope.pc_pid AND pt.apptdate = ope.pc_eventDate AND pt.appttime = ope.pc_starttime
            AND pt.eid = ope.pc_eid
            LEFT OUTER JOIN patient_tracker_element AS pte ON pte.pt_tracker_id = pt.id AND pte.seq = pt.lastseq
            LEFT OUTER JOIN patient_data AS pd ON pd.pid = ope.pc_pid
            LEFT OUTER JOIN users AS u ON u.id = ope.pc_aid
            WHERE ope.pc_pid = pd.pid $ssql
            ORDER BY ope.pc_eventDate, ope.pc_startTime";

    $db_patient = (sqlStatement($query));
    $patient_array = array();
    $cnt = 0;
    while ($prow = sqlFetchArray($db_patient)) {
        $patient_array[$cnt] = $prow;
        $cnt++;
    }
    return $patient_array;
}

////////////////////////////////////////////////////////////////////
// Function:    cron_getNotificationData
// Purpose: get alert notification data
////////////////////////////////////////////////////////////////////
function cron_getNotificationData($type)
{
    $query = "SELECT * FROM automatic_notification WHERE type=? ";
    $db_email_msg = sqlFetchArray(sqlStatement($query, [$type]));
    return $db_email_msg;
}

////////////////////////////////////////////////////////////////////
// Function:    cron_InsertNotificationLogEntry
// Purpose: insert log entry in table
////////////////////////////////////////////////////////////////////
function cron_InsertNotificationLogEntry($type, $prow, $db_email_msg)
{
    global $SMS_GATEWAY_USENAME, $SMS_GATEWAY_PASSWORD, $SMS_GATEWAY_APIKEY;
    if ($type == 'SMS') {
        $smsgateway_info = $db_email_msg['sms_gateway_type'] . "|||" . $SMS_GATEWAY_USENAME . "|||" . $SMS_GATEWAY_PASSWORD . "|||" . $SMS_GATEWAY_APIKEY;
    } else {
        $smsgateway_info = $db_email_msg['email_sender'] . "|||" . $db_email_msg['email_subject'];
    }
    $patient_info = $prow['title'] . " " . $prow['fname'] . " " . $prow['mname'] . " " . $prow['lname'] . "|||" . $prow['phone_cell'] . "|||" . $prow['email'];
    $data_info = $prow['pc_eventDate'] . "|||" . $prow['pc_endDate'] . "|||" . $prow['pc_startTime'] . "|||" . $prow['pc_endTime'];
    $sql_loginsert = "INSERT INTO `notification_log` ( `iLogId` , `pid` , `pc_eid` , `sms_gateway_type` , `message` , `email_sender` , `email_subject` , `type` , `patient_info` , `smsgateway_info` , `pc_eventDate` , `pc_endDate` , `pc_startTime` , `pc_endTime` , `dSentDateTime` ) VALUES ";
    $sql_loginsert .= "(NULL , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $db_loginsert = (sqlStatement(
        $sql_loginsert,
        [
            $prow['pid'],
            $prow['pc_eid'],
            $db_email_msg['sms_gateway_type'],
            $db_email_msg['message'],
            $db_email_msg['email_sender'],
            $db_email_msg['email_subject'],
            $db_email_msg['type'],
            $patient_info,
            $smsgateway_info,
            $prow['pc_eventDate'],
            $prow['pc_endDate'],
            $prow['pc_startTime'],
            $prow['pc_endTime'],
            date("Y-m-d H:i:s")
        ]
        )
    );
}

////////////////////////////////////////////////////////////////////
// Function:    cron_setmessage
// Purpose: set the message
////////////////////////////////////////////////////////////////////
function cron_setmessage($prow, $db_email_msg)
{
    $NAME = $prow['title'] . " " . $prow['fname'] . " " . $prow['mname'] . " " . $prow['lname'];
    $PROVIDER = $prow['user_name'];
    $dtWrk = strtotime($prow['pc_eventDate'] . ' ' . $prow['pc_startTime']);
    $DATE = date('l, d M Y', $dtWrk);
    $STARTTIME = date("h:i A", $dtWrk);
    $ENDTIME = $prow['pc_endTime'];
    $find_array = array('***NAME***', '***PROVIDER***', '***DATE***', '***STARTTIME***', '***ENDTIME***');
    $replace_array = array($NAME, $PROVIDER, $DATE, $STARTTIME, $ENDTIME);
    $message = str_replace($find_array, $replace_array, $db_email_msg['message']);
    return $message;
}

////////////////////////////////////////////////////////////////////
// Function:    cron_GetNotificationSettings
// Purpose: get notification settings
////////////////////////////////////////////////////////////////////
function cron_GetNotificationSettings()
{
    $strQuery = "SELECT * FROM notification_settings WHERE type='SMS/Email Settings'";
    $vectNotificationSettings = sqlFetchArray(sqlStatement($strQuery));

    return ($vectNotificationSettings);
}
