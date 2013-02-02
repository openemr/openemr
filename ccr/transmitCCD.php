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

/*
 * Connect to a phiMail Direct Messaging server and transmit
 * a CCD document to the specified recipient. If the message is accepted by the
 * server, the script will return "SUCCESS", otherwise it will return an error msg. 
 * @param DOMDocument ccd the xml data to transmit, a CCDA document is assumed
 * @param string recipient the Direct Address of the recipient
 * @param string requested_by user | patient
 * @return string result of operation
 */

function transmitCCD($ccd,$recipient,$requested_by) {
   global $pid;

   $config_err = xl("Direct messaging is currently unavailable.")." EC:";
   if ($GLOBALS['phimail_enable']==false) return("$config_err 1");
   $phimail_server=@parse_url($GLOBALS['phimail_server_address']);
   $phimail_username=$GLOBALS['phimail_username'];
   $phimail_password=$GLOBALS['phimail_password'];
   switch ($phimail_server['scheme']) {
       case "http": $server="tcp://".$phimail_server['host'];
	       break;
       case "https": $server="ssl://".$phimail_server['host'];
	       break;
       default: return("$config_err 2");
   }
   $fp=@fsockopen($server,$phimail_server['port']);
   if ($fp===false) return("$config_err 3");
   @fwrite($fp,"AUTH $phimail_username $phimail_password\n");
   @fflush($fp);
   $ret=@fgets($fp,256);
   if($ret!="OK\n") {
       @fwrite($fp,"BYE\n");
       @fclose($fp);
       return("$config_err 4");
   }
   @fwrite($fp,"TO $recipient\n");
   @fflush($fp);
   $ret=@fgets($fp,256);
   if($ret!="OK\n") {
       @fwrite($fp,"BYE\n");
       @fclose($fp);
       return( xl("Delivery is not currently permitted to the specified Direct Address.") );
   }
   $ret=@fgets($fp,1024); //ignore extra server data

   if($requested_by=="patient")
	$text_out = xl("Delivery of the attached clinical document was requested by the patient.");
   else
	$text_out = xl("A clinical document is attached.");

   $text_len=strlen($text_out);
   @fwrite($fp,"TEXT $text_len\n");
   @fflush($fp);
   $ret=@fgets($fp,256);
   if($ret!="BEGIN\n") {
       @fwrite($fp,"BYE\n");
       @fclose($fp);
       return("$config_err 5");
   }
   @fwrite($fp,$text_out);
   @fflush($fp);
   $ret=@fgets($fp,256);
   if($ret!="OK\n") {
       @fwrite($fp,"BYE\n");
       @fclose($fp);
       return("$config_err 6");
   }

   $ccd_out=$ccd->saveXml();
   $ccd_len=strlen($ccd_out);

   @fwrite($fp,"CDA $ccd_len\n");
   @fflush($fp);
   $ret=@fgets($fp,256);
   if($ret!="BEGIN\n") {
       @fwrite($fp,"BYE\n");
       @fclose($fp);
       return("$config_err 7");
   }
   @fwrite($fp,$ccd_out);
   @fflush($fp);
   $ret=@fgets($fp,256);
   if($ret!="OK\n") {
       @fwrite($fp,"BYE\n");
       @fclose($fp);
       return("$config_err 8");
   }
   @fwrite($fp,"SEND\n");
   @fflush($fp);
   $ret=@fgets($fp,256);
   @fwrite($fp,"BYE\n");
   @fclose($fp);

   if(substr($ret,5)=="ERROR")
       return( xl("The message could not be sent at this time."));

   /**
    * If we get here, the message was successfully sent and the return
    * value $ret is of the form "QUEUED recipient message-id" which
    * is suitable for logging. 
    * 
    */
   if($requested_by=="patient")
     newEvent("transmit-ccd","portal-user",$_SESSION['authProvider'],1,$ret,$pid);
   else
     newEvent("transmit-ccd",$_SESSION['authUser'],$_SESSION['authProvider'],1,$ret,$pid);
   return("SUCCESS");
}

?>
