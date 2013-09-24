<?php
/**
 * Functions to transmit a CCD as a Direct Protocol Message
 *
 * Copyright (C) 2013 EMR Direct <http://www.emrdirect.com/>
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
 * @author  EMR Direct <http://www.emrdirect.com/>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__) . "/../library/log.inc");
require_once(dirname(__FILE__) . "/../library/sql.inc");
require_once(dirname(__FILE__) . "/../library/patient.inc");
require_once(dirname(__FILE__) . "/../library/direct_message_check.inc");

/*
 * Connect to a phiMail Direct Messaging server and transmit
 * a CCD document to the specified recipient. If the message is accepted by the
 * server, the script will return "SUCCESS", otherwise it will return an error msg. 
 * @param DOMDocument ccd the xml data to transmit, a CCDA document is assumed
 * @param string recipient the Direct Address of the recipient
 * @param string requested_by user | patient
 * @return string result of operation
 */

function transmitCCD($ccd,$recipient,$requested_by,$xml_type="CCD") {
   global $pid;

   //get patient name in Last_First format (used for CCDA filename) and
   //First Last for the message text.
   $patientData = getPatientPID(array("pid"=>$pid));
   if (empty($patientData[0]['lname'])) {
      $att_filename = "";
      $patientName2 = "";
   } else {
      //spaces are the argument delimiter for the phiMail API calls and must be removed
      $att_filename = " " . 
         str_replace(" ", "_", $xml_type . "_" . $patientData[0]['lname'] 
         . "_" . $patientData[0]['fname']) . ".xml";
      $patientName2 = $patientData[0]['fname'] . " " . $patientData[0]['lname'];
   }

   $config_err = xl("Direct messaging is currently unavailable.")." EC:";
   if ($GLOBALS['phimail_enable']==false) return("$config_err 1");

   $fp = phimail_connect($err);
   if ($fp===false) return("$config_err $err");

   $phimail_username = $GLOBALS['phimail_username'];
   $phimail_password = $GLOBALS['phimail_password'];
   $ret = phimail_write_expect_OK($fp,"AUTH $phimail_username $phimail_password\n");
   if($ret!==TRUE) return("$config_err 4");

   $ret = phimail_write_expect_OK($fp,"TO $recipient\n");
   if($ret!==TRUE) return( xl("Delivery is not allowed to the specified Direct Address.") );
   
   $ret=fgets($fp,1024); //ignore extra server data

   if($requested_by=="patient")
	$text_out = xl("Delivery of the attached clinical document was requested by the patient") . 
            ($patientName2=="" ? "." : ", " . $patientName2 . ".");
   else
	$text_out = xl("A clinical document is attached") . 
            ($patientName2=="" ? "." : " " . xl("for patient") . " " . $patientName2 . ".");

   $text_len=strlen($text_out);
   phimail_write($fp,"TEXT $text_len\n");
   $ret=@fgets($fp,256);
   if($ret!="BEGIN\n") {
       phimail_close($fp);
       return("$config_err 5");
   }
   $ret=phimail_write_expect_OK($fp,$text_out);
   if($ret!==TRUE) return("$config_err 6");

   $ccd_out=$ccd->saveXml();
   $ccd_len=strlen($ccd_out);

   phimail_write($fp,"ADD " . ($xml_type=="CCR" ? "CCR " : "CDA ") . $ccd_len . $att_filename . "\n");
   $ret=fgets($fp,256);
   if($ret!="BEGIN\n") {
       phimail_close($fp);
       return("$config_err 7");
   }
   $ret=phimail_write_expect_OK($fp,$ccd_out);
   if($ret!==TRUE) return("$config_err 8");

   phimail_write($fp,"SEND\n");
   $ret=fgets($fp,256);
   phimail_close($fp);

   if($requested_by=="patient")  {
	$reqBy="portal-user";
	$sql = "SELECT id FROM users WHERE username='portal-user'";
	if (($r = sqlStatementNoLog($sql)) === FALSE ||
	    ($u = sqlFetchArray($r)) === FALSE) {
	    $reqID = 1; //default if we don't have a service user
	} else {
	    $reqID = $u['id'];
	}

   } else {
	$reqBy=$_SESSION['authUser'];
        $reqID=$_SESSION['authUserID'];
   }

   if(substr($ret,5)=="ERROR") {
       //log the failure
       newEvent("transmit-ccd",$reqBy,$_SESSION['authProvider'],0,$ret,$pid);
       return( xl("The message could not be sent at this time."));
   }

   /**
    * If we get here, the message was successfully sent and the return
    * value $ret is of the form "QUEUED recipient message-id" which
    * is suitable for logging. 
    */
   $msg_id=explode(" ",trim($ret),4);
   if($msg_id[0]!="QUEUED" || !isset($msg_id[2])) { //unexpected response
	$ret = "UNEXPECTED RESPONSE: " . $ret;
	newEvent("transmit-ccd",$reqBy,$_SESSION['authProvider'],0,$ret,$pid);
	return( xl("There was a problem sending the message."));
   }
   newEvent("transmit-".$xml_type,$reqBy,$_SESSION['authProvider'],1,$ret,$pid);
   $adodb=$GLOBALS['adodb']['db'];
   $sql="INSERT INTO direct_message_log (msg_type,msg_id,sender,recipient,status,status_ts,patient_id,user_id) " .
	"VALUES ('S', ?, ?, ?, 'S', NOW(), ?, ?)";
   $res=@sqlStatementNoLog($sql,array($msg_id[2],$phimail_username,$recipient,$pid,$reqID));

   return("SUCCESS");
}

?>
