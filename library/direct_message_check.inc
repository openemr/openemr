<?php
/**
 * Background receive function for phiMail Direct Messaging service.
 *
 * This script is called by the background service manager
 * at /library/ajax/execute_background_services.php
 *
 * Copyright (C) 2013 EMR Direct <http://www.emrdirect.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
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

require_once(dirname(__FILE__) . "/log.inc");
require_once(dirname(__FILE__) . "/sql.inc");
require_once(dirname(__FILE__) . "/pnotes.inc");
require_once(dirname(__FILE__) . "/../controllers/C_Document.class.php");
require_once(dirname(__FILE__) . "/documents.php");

/**
 * Connect to a phiMail Direct Messaging server and check for any incoming status
 * messages related to previously transmitted messages or any new messages received.
 */

function phimail_check() {
   if ($GLOBALS['phimail_enable']==false) return; //for safety

   $phimail_server=@parse_url($GLOBALS['phimail_server_address']);
   $phimail_username=$GLOBALS['phimail_username'];
   $phimail_password=$GLOBALS['phimail_password'];
   switch ($phimail_server['scheme']) {
       case "http": $server="tcp://".$phimail_server['host'];
               break;
       case "https": $server="ssl://".$phimail_server['host'];
               break;
       default: return;
   }
   $fp=@fsockopen($server,$phimail_server['port']);
   if ($fp===false) {
      phimail_logit(0,"could not connect to server");
      return;
   }

   $ret = phimail_write_expect_OK($fp,"AUTH $phimail_username $phimail_password\n");
   if($ret!==TRUE) return; //authentication error

   while (1) {
      phimail_write($fp,"CHECK\n");
      $ret=fgets($fp,512);

      if($ret=="NONE\n") { //nothing to process
          phimail_close($fp);
	  phimail_logit(1,"message check completed");
          return;
      }
      else if(substr($ret,0,6)=="STATUS") {
	  //Format STATUS message-id status-code [additional-information]
          $val=explode(" ",trim($ret),4);
	  $sql='SELECT * from direct_message_log WHERE msg_id = ?';
	  $res = sqlStatementNoLog($sql,array($val[1]));
	  if ($res===FALSE) { //database problem
	     phimail_close($fp);
	     phimail_logit(0,"database problem");
	     return;
	  }
	  if (($msg=sqlFetchArray($res))===FALSE) {
	     //no match, so log it and move on (should never happen)
	     phimail_logit(0,"NO MATCH: ".$ret);
	     $ret = phimail_write_expect_OK($fp,"OK\n"); 
	     if($ret!==TRUE) return; else continue; 
	  }

          //if we get here, $msg contains the matching outgoing message record
	  if($val[2]=='failed') {
	     $success=0;
	     $status='F';
 	  } else if ($val[2]=='dispatched') {
	     $success=1;
	     $status='D';
          } else {
	     //unrecognized status, log it and move on (should never happen)
	     $ret = "UNKNOWN STATUS: ".$ret;
	     $success=0;
	     $status='U';
          }

	  phimail_logit($success,$ret,$msg['patient_id']);

	  if (!isset($val[3])) $val[3]="";
	  $sql = "UPDATE direct_message_log SET status=?, status_ts=NOW(), status_info=? WHERE msg_type='S' AND msg_id=?";
	  $res = sqlStatementNoLog($sql,array($status,$val[3],$val[1]));
	  if ($res===FALSE) { //database problem
	     phimail_close($fp);
	     phimail_logit(0,"database problem updating: ".$val[1]);
	     return;
	  }

	  if(!$success) {
	     phimail_notify( xl('Direct Messaging Send Failure.'), $ret);
          }

	  //done with this status message
	  $ret = phimail_write_expect_OK($fp,"OK\n");
	  if($ret!==TRUE) {
             phimail_close($fp);
	     return;
          } 

      }

      else if(substr($ret,0,4)=="MAIL") {

         $val = explode(" ",trim($ret),5); // MAIL recipient sender #attachments msg-id
         $recipient=$val[1];
	 $sender=$val[2];
	 $att=(int)$val[3];
	 $msg_id=$val[4];

         //request main message
	 $ret2 = phimail_write_expect_OK($fp,"SHOW 0\n");
         if($ret2!==TRUE) {
            phimail_close($fp);
            return;
         }

	 //get message headers
         $hdrs="";
         while (($next_hdr = fgets($fp,1024)) != "\n") 
            $hdrs .= $next_hdr;

	 $mime_type=fgets($fp,512);
	 $mime_info=explode(";",$mime_type);
         $mime_type_main=strtolower($mime_info[0]);

	 //get main message body
         $body_len=fgets($fp,256);
         $body=phimail_read_blob($fp,$body_len);
	 if ($body===FALSE) {
	   phimail_close($fp);
	   return;
         }

         $att2=fgets($fp,256);
	 if($att2!=$att) { //safety for mismatch on attachments
	    phimail_close($fp);
            return;
         }

	 //get attachment info
	 if($att>0) {
	    for ($attnum=0;$attnum<$att;$attnum++) {
		if(  ($attinfo[$attnum]['name']=fgets($fp,1024)) === FALSE
		  || ($attinfo[$attnum]['mime']=fgets($fp,1024)) === FALSE
		  || ($attinfo[$attnum]['desc']=fgets($fp,1024)) === FALSE) {
		     phimail_close($fp);
		     return;
	  	}
 	     }
	  }

	  //main part gets stored as document if not plain text content
	  //(if plain text it will be the body of the final pnote)
	  $doc_id=0;
          $att_detail="";
	  if ($mime_type_main != "text/plain") {
	     $name = uniqid("dm-message-") . phimail_extension($mime_type_main);
             $doc_id = phimail_store($name,$mime_type_main,$body);
             if (!$doc_id) { 
		phimail_close($fp);
		return;
	     }
	     $idnum=$doc_id['doc_id']; 
	     $url=$doc_id['url']; 
	     $url=substr($url,strrpos($url,"/")+1);
             $att_detail = "\n" . xl ("Document") . " $idnum (\"$url\"; $mime_type_main; " . 
		filesize($body) . " bytes) Main message body";
	  }

	  //download and store attachments
          for($attnum=0;$attnum<$att;$attnum++) {
	     $ret2 = phimail_write_expect_OK($fp,"SHOW " . ($attnum+1) . "\n");
	     if ($ret2!==TRUE) {
		phimail_close($fp);
		return;
	     }

	     //we can ignore next two lines (repeat of name and mime-type)
	     if ( ($a1=fgets($fp,512))===FALSE || ($a2=fgets($fp,512))===FALSE ) {
		phimail_close($fp);
		return;
	     }

	     $att_len = fgets($fp,256); //length of file
	     $attdata = phimail_read_blob($fp,$att_len);
	     if ($attdata===FALSE) {
		phimail_close($fp);
		return;
	     }
             $attinfo[$attnum]['file']=$attdata;

	     $req_name = trim($attinfo[$attnum]['name']);
	     $req_name = (empty($req_name) ? $attdata : "dm-") . $req_name;
	     $attinfo[$attnum]['mime'] = explode(";",trim($attinfo[$attnum]['mime']));
	     $attmime = strtolower($attinfo[$attnum]['mime'][0]);
	     $att_doc_id = phimail_store($req_name, $attmime, $attdata);
             if (!$att_doc_id) { 
		phimail_close($fp);
		return;
	     }
	     $attinfo[$attnum]['doc_id']=$att_doc_id;
	     $idnum=$att_doc_id['doc_id']; 
	     $url=$att_doc_id['url']; 
	     $url=substr($url,strrpos($url,"/")+1);
             $att_detail = $att_detail . "\n" . xl ("Document") . " $idnum (\"$url\"; $attmime; " . 
		 filesize($attdata) . " bytes) " . trim($attinfo[$attnum]['desc']);
	 }

	 if ($att_detail != "") 
	   $att_detail = "\n\n" . xl("The following documents were attached to this Direct message:") . $att_detail;

	 $ret2 = phimail_write_expect_OK($fp,"DONE\n"); //we'll check for failure after logging.

	 //logging only after succesful download, storage, and acknowledgement of message
         $sql = "INSERT INTO direct_message_log (msg_type,msg_id,sender,recipient,status,status_ts,user_id) " .
            "VALUES ('R', ?, ?, ?, 'R', NOW(), ?)";
         $res = sqlStatementNoLog($sql,array($msg_id,$sender,$recipient,phimail_service_userID()));

	 phimail_logit(1,$ret);

	 if(!($notifyUsername = $GLOBALS['phimail_notify'])) $notifyUsername='admin'; //fallback
	 
	 //alert appointed user about new message
	 switch($mime_type_main) {

	   case "text/plain":
	     $body_text = @file_get_contents($body); //this was not uploaded as a document
	     unlink($body);
             $pnote_id = addPnote(0, xl("Direct Message Received.") . "\n$hdrs\n$body_text$att_detail",
               0, 1, "Unassigned", $notifyUsername, "", "New", "phimail-service");
	     break;

           default:
	     $note = xl("Direct Message Received.") . "\n$hdrs\n"
		. xl("Message content is not plain text so it has been stored as a document.") . $att_detail;
	     $pnote_id = addPnote(0, $note, 0, 1, "Unassigned", $notifyUsername, "", "New", "phimail-service");
	     break;

         }

	 if ($ret2!==TRUE) { 
	    phimail_close();
	    return; 
         }

      }
      else { //unrecognized or FAIL response
          phimail_close($fp);
          return;
      } 
   }
}

/**
 * Helper functions
 */
function phimail_write($fp,$text) {
   fwrite($fp,$text);
   fflush($fp);
}

function phimail_write_expect_OK($fp,$text) {
   phimail_write($fp,$text);
   $ret = fgets($fp,256);
   if($ret!="OK\n") { //unexpected error
      phimail_close($fp);
      return $ret;
   }
   return TRUE;
}

function phimail_close($fp) {
   fwrite($fp,"BYE\n");
   fflush($fp);
   fclose($fp);
}

function phimail_logit($success,$text,$pid=0,$event="direct-message-check") {
   newEvent($event,"phimail-service",0,$success,$text,$pid);
}

/**
 * Read a blob of data into a local temporary file
 * @param $len number of bytes to read
 * @return the temp filename, or FALSE if failure
 */
function phimail_read_blob($fp,$len) {

   $fpath=$GLOBALS['temporary_files_dir'];
   if(!@file_exists($fpath)) {
     return FALSE;
   }
   $name = uniqid("direct-");
   $fn = $fpath . "/" . $name . ".dat";
   $dup = 1;
   while(file_exists($fn)) {
     $fn = $fpath . "/" . $name . "." . $dup++ . ".dat";
   }

   $ff = @fopen ($fn, "w");
   if(!$ff) return FALSE;

   $bytes_left=$len;
   $chunk_size=1024;
   while (!feof($fp) && $bytes_left>0) {
      if ($bytes_left < $chunk_size ) $chunk_size = $bytes_left;
      $chunk = fread($fp,$chunk_size);
      if($chunk===FALSE || @fwrite($ff,$chunk)===FALSE) {
	 @fclose($ff);
	 @unlink($fn);
         return FALSE;
      }
      $bytes_left -= strlen($chunk);
   }
   @fclose($ff);
   return($fn);
}

/**
 * Return a suitable filename extension based on MIME-type
 * (very limited, default is .dat)
 */
function phimail_extension($mime) {
  $m=explode("/",$mime);
  switch($mime) {
	case 'text/plain': 
		return (".txt");
	default:
  }
  switch($m[1]) {
	case 'html':
	case 'xml':
	case 'pdf':
		return (".".$m[1]);
	default:
		return (".dat");
  }
}

function phimail_service_userID($name='phimail-service') {
   $sql = "SELECT id FROM users WHERE username=?";
   if (($r = sqlStatementNoLog($sql,array($name))) === FALSE ||
       ($u = sqlFetchArray($r)) === FALSE) {
      $user=1; //default if we don't have a service user
   } else {
      $user = $u['id'];
   }
   return ($user);
}


/**
 * Registers an attachment or non-text message file using the existing Document structure
 * @return Array(doc_id,URL) of the file as stored in documents table, false = failure
 */
function phimail_store($name,$mime_type,$fn) {

    // Collect phimail user id
    $user = phimail_service_userID();

    // Import the document
    $return = addNewDocument($name,$mime_type,$fn,0,filesize($fn),$user,'direct');

    // Remove the temporary file
    @unlink($fn);

    // Return the result
    return $return;
}

/**
 * Send an error notification or other alert to the notification address specified in globals.
 * (notification email function modified from interface/drugs/dispense_drug.php)
 * @return true if notificaiton successfully sent, false otherwise
 */
function phimail_notify($subj,$body) {
  $recipient = $GLOBALS['practice_return_email_path'];
  if (empty($recipient)) return false;
  $mail = new PHPMailer();
  $mail->SetLanguage("en", $GLOBALS['fileroot'] . "/library/" );
  $mail->From = $recipient;
  $mail->FromName = 'phiMail Gateway';
  $mail->isMail();
  $mail->Host = "localhost";
  $mail->Mailer = "mail";
  $mail->Body = $body;
  $mail->Subject = $subject;
  $mail->AddAddress($recipient);
  return ($mail->Send());
}


?>
