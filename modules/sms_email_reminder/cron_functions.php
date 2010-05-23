<? 
////////////////////////////////////////////////////////////////////
// CRON FUNCTIONS - to use with cron_sms and cron_email backend
// scripts to notify events
////////////////////////////////////////////////////////////////////

// larry :: somne global to be defined here
global $smsgateway_info;
global $patient_info;
global $data_info;

global $SMS_NOTIFICATION_HOUR;
global $EMAIL_NOTIFICATION_HOUR;

////////////////////////////////////////////////////////////////////
// Function:	cron_SendMail
// Purpose:	send mail
// Input:	to, subject, email body and from 
// Output:	status - if sent or not
////////////////////////////////////////////////////////////////////
function cron_SendMail( $to, $subject, $vBody, $from )
{
	// check if smtp globals set 
	if( $GLOBALS['smtp_host_name'] == '' )
	{
		// larry :: debug
		//echo "\nDEBUG :: use mail method\n";	
	
		// larry :: add cc/bcc - bot used ?
		$cc = "";
		$bcc = "";
		$format = 0;
	
		//echo "function called";exit;
		if( strlen( $format )==0 )	$format="text/html";
		$headers  = "MIME-Version: 1.0\r\n"; 
		$headers .= "Content-type: ". $format ."; charset=iso-8859-1\r\n"; 
		
		// additional headers 
		$headers .= "From: $from\r\n"; 
		if( strlen($cc)>5 ) $headers .= "Cc: $cc\r\n"; 
		if( strlen($bcc)>5 ) $headers .= "Bcc: $bcc\r\n"; 
		$cnt = "";
		$cnt .= "\nHeaders : ".$headers;
		$cnt .= "\nDate Time :". date("d M, Y  h:i:s");
		$cnt .= "\nTo : ".$to;
		$cnt .= "\nSubject : ".$subject;
		$cnt .= "\nBody : \n".$vBody."\n";
		
		if(1)
		{
			//WriteLog($cnt);
		}
		$mstatus = true;
		$mstatus = @mail( $to, $subject, $vBody, $headers );
		// larry :: debug
		//echo "\nDEBUG :email: send email from=".$from." to=".$to." sbj=".$subject." body=".$vBody." head=".$headers."\n";
		//echo "\nDEBUG :email: send status=".$mstatus."\n";
	} else
	{
		// larry :: debug
		//echo "\nDEBUG :: use smtp method\n";	
		
		if( !class_exists( "smtp_class" ) )
		{
			include("../../library/classes/smtp/smtp.php");
			include("../../library/classes/smtp/sasl.php");
		}
		
		$strFrom = $from;
		$sender_line=__LINE__;
		$strTo = $to;
		$recipient_line=__LINE__;
		if( strlen( $strFrom ) == 0 ) return( false );
		if( strlen( $strTo ) == 0 ) return( false );
		
		//if( !$smtp ) 
		$smtp=new smtp_class;
		
		$smtp->host_name = $GLOBALS['smtp_host_name'];
		$smtp->host_port = $GLOBALS['smtp_host_port'];
		$smtp->ssl = $GLOBALS['smtp_use_ssl'];
		$smtp->localhost = $GLOBALS['smtp_localhost'];
		$smtp->direct_delivery = 0;
		$smtp->timeout = 10;
		$smtp->data_timeout = 0;
		
		$smtp->debug = 1;
		$smtp->html_debug = 0;
		$smtp->pop3_auth_host = "";
		
		$smtp->user = $GLOBALS['smtp_auth_user'];
		$smtp->password = $GLOBALS['smtp_auth_pass'];
		
		$smtp->realm = "";
		// Workstation name for NTLM authentication
		$smtp->workstation = "";
		// Specify a SASL authentication method like LOGIN, PLAIN, CRAM-MD5, NTLM, etc..
		// Leave it empty to make the class negotiate if necessary 
		$smtp->authentication_mechanism = "";
		
		// If you need to use the direct delivery mode and this is running under
		// Windows or any other platform
		if($smtp->direct_delivery)
		{
			if(!function_exists("GetMXRR"))
			{
				$_NAMESERVERS=array();
				include("getmxrr.php");
			}
		}
		
		if( $smtp->SendMessage(
			$strFrom,
			array( $strTo ),
			array(
				"From: $strFrom",
				"To: $strTo",
				"Subject: $subject",
				"Date Time :". date("d M, Y  h:i:s")
				),
			$vBody ) )	
		{
			echo "Message sent to $to OK.\n";
			$mstatus = true;
		} else
		{
			 echo "Cound not send the message to $to.\nError: ".$smtp->error."\n";
			 $mstatus = false;
		}
		
		unset( $smtp );	
	}		
	
	return $mstatus;
}

////////////////////////////////////////////////////////////////////
// Function:	WriteLog
// Purpose:	written log into file
////////////////////////////////////////////////////////////////////
function WriteLog( $data )
{
	global $log_folder_path;
	
	$filename = $log_folder_path . "/cronlog_".date("Ymd").".html"; 
	//echo $filename;exit;
   	if (!$fp = fopen($filename, 'a'))
   	{ 
        	print "Cannot open file ($filename)"; 
        	exit; 
   	}
   	
	$sdata = "\n====================================================================\n";	
   	
   	if (!fwrite($fp, $sdata.$data.$sdata))
   	{ 
   		print "Cannot write to file ($filename)"; 
		exit; 
	}
	
   	fclose($fp);
}

////////////////////////////////////////////////////////////////////
// define my_print_r - used for debuging - if not defined 
////////////////////////////////////////////////////////////////////
if( !function_exists( 'my_print_r' ) )
{
	function my_print_r($data)
	{
		echo "<pre>";print_r($data);echo "</pre>";
	}
}

////////////////////////////////////////////////////////////////////
// Function:	cron_SendSMS
// Purpose:	send sms
////////////////////////////////////////////////////////////////////
function cron_SendSMS( $to, $subject, $vBody, $from )
{
	global $mysms;
	$cnt = "";
	$cnt .= "\nDate Time :". date("d M, Y  h:i:s");
	$cnt .= "\nTo : ".$to;
	$cnt .= "\From : ".$from;
	$cnt .= "\nSubject : ".$subject;
	$cnt .= "\nBody : \n".$vBody."\n";
	if(1)
	{
		//WriteLog($cnt);
	}
	$mstatus = true;
	// larry :: todo - find out about the billing inclusion ?
	// $mysms->getbalance();
	// $mysms->token_pay("1234567890123456"); //spend voucher with SMS credits
	$mysms->send( $to, $from, $vBody );
	return $mstatus;
}

////////////////////////////////////////////////////////////////////
// Function:	cron_updateentry
// Purpose:	update status yes if alert send to patient
////////////////////////////////////////////////////////////////////
function cron_updateentry($type,$pid,$pc_eid)
{
	// larry :: this was commented - i remove comment - what it means * in this field ?
	//$set = " pc_apptstatus='*',"; - in this prev version there was a comma - somthing to follow ?
	//$set = " pc_apptstatus='*' ";
	
	//$query="update openemr_postcalendar_events set $set ";
	$query = "update openemr_postcalendar_events set ";
	
	// larry :: and here again same story - this time for sms pc_sendalertsms - no such field in the table
	if($type=='SMS')
		$query.=" pc_sendalertsms='YES' ";
	else
		$query.=" pc_sendalertemail='YES' ";
		
	$query .=" where pc_pid='$pid' and pc_eid='$pc_eid' ";
	//echo "<br>".$query;
	$db_sql = (sqlStatement($query));
}

////////////////////////////////////////////////////////////////////
// Function:	cron_getAlertpatientData
// Purpose:	get patient data for send to alert
////////////////////////////////////////////////////////////////////
function cron_getAlertpatientData( $type )
{
	// larry :: move this at the top - not in the function body
	global $SMS_NOTIFICATION_HOUR,$EMAIL_NOTIFICATION_HOUR;
	// larry :: end commment
	
	
	//$ssql .= " and ((ope.pc_eventDate='$check_date') OR ('$check_date' BETWEEN ope.pc_eventDate AND ope.pc_endDate)) ";
	if($type=='SMS')
	{
		// larry :: remove ope.pc_sendalertemail='No' - nothing like it in the calendar
		$ssql = " and pd.hipaa_allowsms='YES' and pd.phone_cell<>'' and ope.pc_sendalertsms='NO' ";
		// $ssql = " and pd.hipaa_allowsms='YES' and pd.phone_cell<>'' ";
		
		$check_date = date("Y-m-d", mktime(date("h")+$SMS_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));
	}else
	{
		// larry :: remove ope.pc_sendalertemail='No' - nothing like it in the calendar 
		$ssql = " and pd.hipaa_allowemail='YES' and pd.email<>''  and ope.pc_sendalertemail='NO' ";
		//$ssql = " and pd.hipaa_allowemail='YES' and pd.email<>'' ";
		
		$check_date = date("Y-m-d", mktime(date("h")+$EMAIL_NOTIFICATION_HOUR, 0, 0, date("m"), date("d"), date("Y")));
	}
	
	$patient_field = "pd.pid,pd.title,pd.fname,pd.lname,pd.mname,pd.phone_cell,pd.email,pd.hipaa_allowsms,pd.hipaa_allowemail,";
	$ssql .= " and (ope.pc_eventDate='$check_date')";
	// larry :: add condition if remnder was already sent
	// $ssql .= " and (ope.pc_apptstatus != '*' ) ";
	
	$query = "select $patient_field pd.pid,ope.pc_eid,ope.pc_pid,ope.pc_title,
			ope.pc_hometext,ope.pc_eventDate,ope.pc_endDate,
			ope.pc_duration,ope.pc_alldayevent,ope.pc_startTime,ope.pc_endTime
		from 
			openemr_postcalendar_events as ope ,patient_data as pd 
		where 
			ope.pc_pid=pd.pid $ssql 
		order by 
			ope.pc_eventDate,ope.pc_endDate,pd.pid";
			
	//echo "<br>".$query;
	
	$db_patient = (sqlStatement($query));
	$patient_array = array();
	$cnt=0;
	while ($prow = sqlFetchArray($db_patient)) 
	{
		$patient_array[$cnt] = $prow;
		$cnt++;
	}
	return $patient_array;
}

////////////////////////////////////////////////////////////////////
// Function:	cron_getNotificationData
// Purpose:	get alert notification data
////////////////////////////////////////////////////////////////////
function cron_getNotificationData($type)
{
	// larry :: pre populate array fields
	//$db_email_msg['notification_id'] = '';	
	//$db_email_msg['sms_gateway_type'] = '';
	
	$query = "select * from automatic_notification where type='$type' ";
	//echo "<br>".$query;
	$db_email_msg = sqlFetchArray(sqlStatement($query));
	return $db_email_msg;
}

////////////////////////////////////////////////////////////////////
// Function:	cron_InsertNotificationLogEntry
// Purpose:	insert log entry in table
////////////////////////////////////////////////////////////////////
function cron_InsertNotificationLogEntry($type,$prow,$db_email_msg)
{
	global $SMS_GATEWAY_USENAME,$SMS_GATEWAY_PASSWORD,$SMS_GATEWAY_APIKEY;
	if( $type=='SMS' )
		$smsgateway_info = $db_email_msg['sms_gateway_type']."|||".$SMS_GATEWAY_USENAME."|||".$SMS_GATEWAY_PASSWORD."|||".$SMS_GATEWAY_APIKEY;
	else
		$smsgateway_info = $db_email_msg['email_sender']."|||".$db_email_msg['email_subject'];

	$patient_info = $prow['title']." ".$prow['fname']." ".$prow['mname']." ".$prow['lname']."|||".$prow['phone_cell']."|||".$prow['email'];
	$data_info = $prow['pc_eventDate']."|||".$prow['pc_endDate']."|||".$prow['pc_startTime']."|||".$prow['pc_endTime'];

	$sql_loginsert = "INSERT INTO `notification_log` ( `iLogId` , `pid` , `pc_eid` , `sms_gateway_type` , `message` , `email_sender` , `email_subject` , `type` , `patient_info` , `smsgateway_info` , `pc_eventDate` , `pc_endDate` , `pc_startTime` , `pc_endTime` , `dSentDateTime` ) VALUES ";
	$sql_loginsert .= "(NULL , '$prow[pid]', '$prow[pc_eid]', '$db_email_msg[sms_gateway_type]', '$db_email_msg[message]', '$db_email_msg[email_sender]', '$db_email_msg[email_subject]', '$db_email_msg[type]', '$patient_info', '$smsgateway_info', '$prow[pc_eventDate]', '$prow[pc_endDate]', '$prow[pc_startTime]', '$prow[pc_endTime]', '".date("Y-m-d H:i:s")."')";
	$db_loginsert = ( sqlStatement( $sql_loginsert ) );
}

////////////////////////////////////////////////////////////////////
// Function:	cron_setmessage
// Purpose:	set the message
////////////////////////////////////////////////////////////////////
function cron_setmessage($prow,$db_email_msg)
{
	// larry :: debug
	//echo "\nDEBUG :cron_setmessage: set message ".$prow['title']." ".$prow['fname']." ".$prow['mname']." ".$prow['lname']."\n";
	
	$NAME = $prow['title']." ".$prow['fname']." ".$prow['mname']." ".$prow['lname'];
	//echo "DEBUG :1: name=".$NAME."\n";
	
	$PROVIDER = $db_email_msg['provider_name'];
	$DATE = $prow['pc_eventDate'];
	$STARTTIME = $prow['pc_startTime'];
	$ENDTIME = $prow['pc_endTime'];
	$find_array = array("***NAME***","***PROVIDER***","***DATE***","***STARTTIME***","***ENDTIME***");
	$replare_array = array($NAME,$PROVIDER,$DATE,$STARTTIME,$ENDTIME);
	$message = str_replace($find_array,$replare_array,$db_email_msg['message']);
	// larry :: debug
	//echo "DEBUG :2: msg=".$message."\n";
	
	return $message;
}

////////////////////////////////////////////////////////////////////
// Function:	cron_GetNotificationSettings
// Purpose:	get notification settings
////////////////////////////////////////////////////////////////////
function cron_GetNotificationSettings( )
{
	$strQuery = "select * from notification_settings where type='SMS/Email Settings'";
	$vectNotificationSettings = sqlFetchArray( sqlStatement( $strQuery ) );

	return( $vectNotificationSettings );
}

?>
