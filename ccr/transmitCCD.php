<?php

/**
 * Functions to transmit a CCD as a Direct Protocol Message
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 *
 * Use of these functions requires an active phiMail Direct messaging
 * account with EMR Direct.  For information regarding this service,
 * please visit http://www.emrdirect.com or email support@emrdirect.com
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__) . "/../library/patient.inc");
require_once(dirname(__FILE__) . "/../library/direct_message_check.inc");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\DirectMessaging\ErrorConstants;

/*
 * Connect to a phiMail Direct Messaging server and transmit
 * a message to the specified recipient. If the message is accepted by the
 * server, the script will return "SUCCESS", otherwise it will return an error msg.
 * @param string message The message to send via Direct
 * @param string recipient the Direct Address of the recipient
 * @param bool Whether to force receipt confirmation that the message was delivered.  Can cause message delivery failures if recipient system does not support the option.
 * @return string result of operation
 */
function transmitMessage($message, $recipient, $verifyFinalDelivery = false)
{

    $reqBy = $_SESSION['authUser'];
    $reqID = $_SESSION['authUserID'];

    $config_err = xl(ErrorConstants::MESSAGING_DISABLED) . " " . ErrorConstants::ERROR_CODE_ABBREVIATION . ":";
    if ($GLOBALS['phimail_enable'] == false) {
        return("$config_err " . ErrorConstants::ERROR_CODE_MESSAGING_DISABLED);
    }

    $fp = phimail_connect($err);
    if ($fp === false) {
        return("$config_err $err");
    }

    $phimail_username = $GLOBALS['phimail_username'];
    $cryptoGen = new CryptoGen();
    $phimail_password = $cryptoGen->decryptStandard($GLOBALS['phimail_password']);
    $ret = phimail_write_expect_OK($fp, "AUTH $phimail_username $phimail_password\n");
    if ($ret !== true) {
        return("$config_err " . ErrorConstants::ERROR_CODE_AUTH_FAILED);
    }

    $ret = phimail_write_expect_OK($fp, "TO $recipient\n");
    if ($ret !== true) {
        return( xl(ErrorConstants::RECIPIENT_NOT_ALLOWED) . " " . $ret );
    }

    $ret = fgets($fp, 1024); //ignore extra server data

    $text_out = $message;

    $text_len = strlen($text_out);
    phimail_write($fp, "TEXT $text_len\n");
    $ret = @fgets($fp, 256);
    if ($ret != "BEGIN\n") {
        phimail_close($fp);
        return("$config_err " . ErrorConstants::ERROR_CODE_MESSAGE_BEGIN_FAILED);
    }

    $ret = phimail_write_expect_OK($fp, $text_out);
    if ($ret !== true) {
        return("$config_err " . ErrorConstants::ERROR_CODE_MESSAGE_BEGIN_OK_FAILED);
    }

    if ($verifyFinalDelivery) {
        $ret = phimail_write_expect_OK($fp, "SET FINAL 1\n");
        if ($ret !== true) {
            return( xl(ErrorConstants::ERROR_MESSAGE_SET_DISPOSITION_NOTIFICATION_FAILED) . " " . $ret );
        }
    } else {
        $ret = phimail_write_expect_OK($fp, "SET FINAL 0\n");
        if ($ret !== true) {
            return( xl(ErrorConstants::ERROR_MESSAGE_SET_DISPOSITION_NOTIFICATION_FAILED) . " " . $ret );
        }
    }

    phimail_write($fp, "SEND\n");
    $ret = fgets($fp);
    phimail_write($fp, "OK\n");
    phimail_close($fp);


    if (substr($ret, 5) == "ERROR") {
        //log the failure

        EventAuditLogger::instance()->newEvent("transmit-ccd", $reqBy, $_SESSION['authProvider'], 0, $ret);
        return( xl(ErrorConstants::ERROR_MESSAGE_FILE_SEND_FAILED));
    }

    /**
     * If we get here, the message was successfully sent and the return
     * value $ret is of the form "QUEUED recipient message-id" which
     * is suitable for logging.
     */
    $msg_id = explode(" ", trim($ret), 4);
    if ($msg_id[0] != "QUEUED" || !isset($msg_id[2])) { //unexpected response
        $ret = "UNEXPECTED RESPONSE: " . $ret;
        EventAuditLogger::instance()->newEvent("transmit-message", $reqBy, $_SESSION['authProvider'], 0, $ret);
        return( xl(ErrorConstants::ERROR_MESSAGE_UNEXPECTED_RESPONSE));
    }

    EventAuditLogger::instance()->newEvent("transmit-message", $reqBy, $_SESSION['authProvider'], 1, $ret);
    $adodb = $GLOBALS['adodb']['db'];
    $sql = "INSERT INTO direct_message_log (msg_type,msg_id,sender,recipient,status,status_ts,user_id) " .
        "VALUES ('S', ?, ?, ?, 'S', NOW(), ?)";
    $res = @sqlStatementNoLog($sql, array($msg_id[2],$phimail_username,$recipient,$reqID));

    return("SUCCESS");
}

/*
 * Connect to a phiMail Direct Messaging server and transmit
 * a CCD document to the specified recipient. If the message is accepted by the
 * server, the script will return "SUCCESS", otherwise it will return an error msg.
 * @param number The patient pid that we are sending a CCDA doc for
 * @param string ccd the data to transmit, a CCDA document is assumed
 * @param string recipient the Direct Address of the recipient
 * @param string requested_by user | patient
 * @param string The format that the document is in (pdf, xml, html)
 * @param string The message body the clinician wants to send
 * @param string The filename to use as the name of the attachment (must included file extension as part of filename)
 * @param bool Whether to force receipt confirmation that the message was delivered.  Can cause message delivery failures if recipient system does not support the option.
 * @return string result of operation
 */
function transmitCCD($pid, $ccd_out, $recipient, $requested_by, $xml_type = "CCD", $format_type = 'xml', $message = '', $filename = '', $verifyFinalDelivery = false): string
{
    //get patient name in Last_First format (used for CCDA filename) and
    //First Last for the message text.
    $patientData = getPatientPID(array("pid" => $pid));
    if (empty($patientData)) { // shouldn't ever happen but we need to check anyways
        return( xl(ErrorConstants::ERROR_MESSAGE_UNEXPECTED_RESPONSE));
    }
    $patientName2 = "";
    $att_filename = "";

    // TODO: do we want to throw an error if we can't get patient data?  Probably.

    if (!empty($patientData[0]['lname'])) {
        $patientName2 = trim($patientData[0]['fname'] . " " . $patientData[0]['lname']);
    }

    if (!empty($filename)) {
        // if we have a filename from our database, we want to send that
        $att_filename = $filename;
        $extension = ""; // no extension needed
    } else if (!empty($patientName2)) {
        //spaces are the argument delimiter for the phiMail API calls and must be removed
        // CCDA format requires patient name in last, first format
        $att_filename = str_replace(" ", "_", $xml_type . "_" . $patientData[0]['lname']
            . "_" . $patientData[0]['fname']);
        $extension = "." . $format_type;
    }

    $config_err = xl(ErrorConstants::MESSAGING_DISABLED) . " " . ErrorConstants::ERROR_CODE_ABBREVIATION . ":";
    if ($GLOBALS['phimail_enable'] == false) {
        return("$config_err " . ErrorConstants::ERROR_CODE_MESSAGING_DISABLED);
    }

    $fp = phimail_connect($err);
    if ($fp === false) {
        return("$config_err $err");
    }

    $phimail_username = $GLOBALS['phimail_username'];
    $cryptoGen = new CryptoGen();
    $phimail_password = $cryptoGen->decryptStandard($GLOBALS['phimail_password']);
    $ret = phimail_write_expect_OK($fp, "AUTH $phimail_username $phimail_password\n");
    if ($ret !== true) {
        return("$config_err " . ErrorConstants::ERROR_CODE_AUTH_FAILED);
    }

    $ret = phimail_write_expect_OK($fp, "TO $recipient\n");
    if ($ret !== true) {
        return( xl(ErrorConstants::RECIPIENT_NOT_ALLOWED) . " " . $ret );
    }

    $ret = fgets($fp, 1024); //ignore extra server data

    // add whatever the clinican added as a message to be sent.
    if (is_string($message) && trim($message) != "") {
        $text_out = $message . "\n";
    }

    if ($requested_by == "patient") {
        $text_out .= xl("Delivery of the attached clinical document was requested by the patient") .
            ($patientName2 == "" ? "." : ", " . $patientName2 . ".");
    } else {
        $text_out .= xl("A clinical document is attached") .
            ($patientName2 == "" ? "." : " " . xl("for patient") . " " . $patientName2 . ".");
    }


    $text_len = strlen($text_out);
    phimail_write($fp, "TEXT $text_len\n");
    $ret = @fgets($fp, 256);
    if ($ret != "BEGIN\n") {
        phimail_close($fp);
        return("$config_err " . ErrorConstants::ERROR_CODE_MESSAGE_BEGIN_FAILED);
    }

    $ret = phimail_write_expect_OK($fp, $text_out);
    if ($ret !== true) {
        return("$config_err " . ErrorConstants::ERROR_CODE_MESSAGE_BEGIN_OK_FAILED);
    }

    // MU2 CareCoordination added the need to send CCDAs formatted as html,pdf, or xml
    if ($format_type == 'html') {
        $add_type = "TEXT";
    } else if ($format_type == 'pdf') {
        $add_type = "RAW";
    } else if ($format_type == 'xml') {
        $add_type = $xml_type == "CCR" ? "CCR" : "CDA";
    } else {
        // unsupported format
        return ("$config_err " . ErrorConstants::ERROR_CODE_INVALID_FORMAT_TYPE);
    }

    $ccd_len = strlen($ccd_out);

    phimail_write($fp, "ADD " . $add_type . " " . $ccd_len . " " . $att_filename . $extension . "\n");
    $ret = fgets($fp, 256);
    if ($ret != "BEGIN\n") {
        phimail_close($fp);
        return("$config_err " . ErrorConstants::ERROR_CODE_ADD_FILE_FAILED);
    }

    $ret = phimail_write_expect_OK($fp, $ccd_out);
    if ($ret !== true) {
        return("$config_err " . ErrorConstants::ERROR_CODE_ADD_FILE_CONFIRM_FAILED);
    }

    if ($verifyFinalDelivery) {
        $ret = phimail_write_expect_OK($fp, "SET FINAL 1\n");
        if ($ret !== true) {
            return( xl(ErrorConstants::ERROR_MESSAGE_SET_DISPOSITION_NOTIFICATION_FAILED) . " " . $ret );
        }
    } else {
        $ret = phimail_write_expect_OK($fp, "SET FINAL 0\n");
        if ($ret !== true) {
            return( xl(ErrorConstants::ERROR_MESSAGE_SET_DISPOSITION_NOTIFICATION_FAILED) . " " . $ret );
        }
    }

    phimail_write($fp, "SEND\n");
    $ret = fgets($fp);
    phimail_write($fp, "OK\n");
    phimail_close($fp);

    if ($requested_by == "patient") {
        $reqBy = "portal-user";
        $sql = "SELECT id FROM users WHERE username='portal-user'";
        if (
            ($r = sqlStatementNoLog($sql)) === false ||
            ($u = sqlFetchArray($r)) === false
        ) {
             $reqID = 1; //default if we don't have a service user
        } else {
            $reqID = $u['id'];
        }
    } else {
        $reqBy = $_SESSION['authUser'];
        $reqID = $_SESSION['authUserID'];
    }

    if (substr($ret, 5) == "ERROR") {
        //log the failure

        EventAuditLogger::instance()->newEvent("transmit-ccd", $reqBy, $_SESSION['authProvider'], 0, $ret, $pid);
        return( xl(ErrorConstants::ERROR_MESSAGE_FILE_SEND_FAILED));
    }

   /**
    * If we get here, the message was successfully sent and the return
    * value $ret is of the form "QUEUED recipient message-id" which
    * is suitable for logging.
    */
    $msg_id = explode(" ", trim($ret), 4);
    if ($msg_id[0] != "QUEUED" || !isset($msg_id[2])) { //unexpected response
        $ret = "UNEXPECTED RESPONSE: " . $ret;
        EventAuditLogger::instance()->newEvent("transmit-ccd", $reqBy, $_SESSION['authProvider'], 0, $ret, $pid);
        return( xl(ErrorConstants::ERROR_MESSAGE_UNEXPECTED_RESPONSE));
    }

    EventAuditLogger::instance()->newEvent("transmit-" . $xml_type, $reqBy, $_SESSION['authProvider'], 1, $ret, $pid);
    $adodb = $GLOBALS['adodb']['db'];
    $sql = "INSERT INTO direct_message_log (msg_type,msg_id,sender,recipient,status,status_ts,patient_id,user_id) " .
    "VALUES ('S', ?, ?, ?, 'S', NOW(), ?, ?)";
    $res = @sqlStatementNoLog($sql, array($msg_id[2],$phimail_username,$recipient,$pid,$reqID));

    return("SUCCESS");
}
