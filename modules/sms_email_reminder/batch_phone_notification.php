<?php
// Copyright (C) 2010 Maviq <info@maviq.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
////////////////////////////////////////////////////////////////////
// Package:	cron_phone_notification
// Purpose:	to be run by cron every hour, look for appointments
//		in the pre-notification period and send an phone reminder
//		Based on cron_email_notification by Larry Lart
// Created by:
// Updated by:	Maviq on 01/12/2010
////////////////////////////////////////////////////////////////////

$backpic = "";
//phone notification
$ignoreAuth=1;

//Set the working directory to the path of the file
$current_dir = dirname($_SERVER['SCRIPT_FILENAME']);
chdir($current_dir);

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../interface/globals.php");
require_once("$srcdir/maviq_phone_api.php");
require_once("$srcdir/formdata.inc.php");

$type = "Phone";
$before_trigger_hours = 72;  // 3 days is default
//Get the values from Global
$before_trigger_hours = $GLOBALS['phone_notification_hour'];
//set up the phone notification settings for external phone service
$phone_url =  $GLOBALS['phone_gateway_url'] ;
$phone_id = $GLOBALS['phone_gateway_username'];
$phone_token = $GLOBALS['phone_gateway_password'];
$phone_time_range = $GLOBALS['phone_time_range'];

//get the facility_id-message map
$facilities = cron_getFacilitiesMap();
//print_r($facilities);
$fac_phone_map = $facilities['phone_map'];
$fac_msg_map = $facilities['msg_map'];

// get patient data for send alert
$db_patient = cron_getPhoneAlertpatientData($type, $before_trigger_hours);
echo "<br>" . htmlspecialchars( xl("Total Records Found") . ": " . count($db_patient), ENT_QUOTES);

//Create a new instance of the phone service client
$client = new MaviqClient($phone_id, $phone_token, $phone_url);

for($p=0;$p<count($db_patient);$p++)
{  
	$prow =$db_patient[$p];
	
	//Get the apptDate and apptTime
	$p_date = $prow['pc_eventDate'];
	//Need to format date to m/d/Y for Maviq API
	$pieces = explode("-",$p_date);
	$appt_date = date("m/d/Y", mktime( 0,0,0,$pieces[1],$pieces[2],$pieces[0]));
	$appt_time = $prow['pc_startTime'];
	//get the greeting
	$greeting = $fac_msg_map[$prow['pc_facility']];
	if ($greeting == null) {
	    //Use the default when the message is not found
	    $greeting = $GLOBALS['phone_appt_message']['Default'];
	}
	//Set up the parameters for the call
	$data = array(
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
		);
	
	//Make the call
	$response = $client->sendRequest("appointment", "POST", $data); 
    
    // check response for success or error
    if($response->IsError) {
  	  $strMsg =  "Error starting phone call for {$prow['fname']} | {$prow['lname']} | {$prow['phone_home']} | {$appt_date} | {$appt_time} | {$response->ErrorMessage}\n"; 	  	
    }
    else {
    	$strMsg = "\n========================".$type." || ".date("Y-m-d H:i:s")."=========================";
		$strMsg .= "\nPhone reminder sent successfully: {$prow['fname']} | {$prow['lname']} |  | {$prow['phone_home']} | {$appt_date} | {$appt_time} ";
		// insert entry in notification_log table
		cron_InsertNotificationLogEntry($prow,$greeting,$phone_url);
	
	//update entry >> pc_sendalertsms='Yes'
	cron_updateentry($type,$prow['pid'],$prow['pc_eid']);
		
    }
		
	//echo $strMsg;
	WriteLog( $strMsg );

}

sqlClose();

////////////////////////////////////////////////////////////////////
// Function:	cron_updateentry
// Purpose:	update status yes if alert send to patient
////////////////////////////////////////////////////////////////////
function cron_updateentry($type,$pid,$pc_eid)
{

	$query = "update openemr_postcalendar_events set ";
	
	// larry :: and here again same story - this time for sms pc_sendalertsms - no such field in the table
	if($type=='SMS')
		$query.=" pc_sendalertsms='YES' ";
	elseif ($type=='Email')
		$query.=" pc_sendalertemail='YES' ";
	//Added by Yijin for phone reminder.. Uses the same field as SMS.
	elseif($type=='Phone')
		$query.=" pc_sendalertsms='YES' ";
		
	$query .=" where pc_pid=? and pc_eid=? ";
	//echo "<br>".$query;
	$db_sql = (sqlStatement($query, array($pid, $pc_eid)));
}

////////////////////////////////////////////////////////////////////
// Function:	cron_getPhoneAlertpatientData
// Purpose:	get patient data for send to alert
////////////////////////////////////////////////////////////////////
function cron_getPhoneAlertpatientData( $type, $trigger_hours )
{
	
	//Added by Yijin 1/12/10 to handle phone reminders. Patient needs to have hipaa Voice flag set to yes and a home phone
	if($type=='Phone'){
		$ssql = " and pd.hipaa_voice='YES' and pd.phone_home<>''  and ope.pc_sendalertsms='NO' and ope.pc_apptstatus != '*' ";
		
		$check_date = date("Y-m-d", mktime(date("H")+$trigger_hours, 0, 0, date("m"), date("d"), date("Y")));
		
	}
	
	$patient_field = "pd.pid,pd.title,pd.fname,pd.lname,pd.mname,pd.phone_cell,pd.email,pd.hipaa_allowsms,pd.hipaa_allowemail,pd.phone_home,pd.hipaa_voice,";
	$ssql .= " and (ope.pc_eventDate=?)";
	
	$query = "select $patient_field pd.pid,ope.pc_eid,ope.pc_pid,ope.pc_title,
			ope.pc_hometext,ope.pc_eventDate,ope.pc_endDate,
			ope.pc_duration,ope.pc_alldayevent,ope.pc_startTime,ope.pc_endTime,ope.pc_facility
		from 
			openemr_postcalendar_events as ope ,patient_data as pd 
		where 
			ope.pc_pid=pd.pid $ssql 
		order by 
			ope.pc_eventDate,ope.pc_endDate,pd.pid";
	
	$db_patient = (sqlStatement($query, array($check_date)));
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
// Function:	cron_InsertNotificationLogEntry
// Purpose:	insert log entry in table
////////////////////////////////////////////////////////////////////
function cron_InsertNotificationLogEntry($prow,$phone_msg,$phone_gateway)
{
	$patient_info = $prow['title']." ".$prow['fname']." ".$prow['mname']." ".$prow['lname']."|||".$prow['phone_home'];
	
	$message = $phone_msg;
	
	$sql_loginsert = "INSERT INTO `notification_log` ( `iLogId` , `pid` , `pc_eid` , `message`, `type` , `patient_info` , `smsgateway_info` , `pc_eventDate` , `pc_endDate` , `pc_startTime` , `pc_endTime` , `dSentDateTime` ) VALUES ";
	$sql_loginsert .= "(NULL , ?, ?, ?, 'Phone', ?, ?, ?, ?, ?, ?, ?)";
	$db_loginsert = ( sqlStatement( $sql_loginsert, array($prow[pid], $prow[pc_eid], $message, $patient_info, $phone_gateway, $prow[pc_eventDate], $prow[pc_endDate], $prow[pc_startTime], $prow[pc_endTime], date("Y-m-d H:i:s"))));
}

////////////////////////////////////////////////////////////////////
// Function:	WriteLog
// Purpose:	written log into file
////////////////////////////////////////////////////////////////////
function WriteLog( $data )
{
	$log_file = $GLOBALS['phone_reminder_log_dir'];

	if ($log_file != null) {
	
		$filename = $log_file . "/"."phone_reminder_cronlog_".date("Ymd").".html"; 

	   	if (!$fp = fopen($filename, 'a'))
	   	{ 
			print "Cannot open file ($filename)"; 
	     
	   	}else { 
	   	
			$sdata = "\n====================================================================\n";	
		   	
		   	if (!fwrite($fp, $data.$sdata))
		   	{ 
		   		print "Cannot write to file ($filename)"; 
			}
	
		   	fclose($fp);
		}
	}
}
////////////////////////////////////////////////////////////////////
// Function:	cron_getFacilities
// Purpose:	get facilities data once and store in map
////////////////////////////////////////////////////////////////////
function cron_getFacilitiesMap()
{
	//get the facility_name-message map from Globals
	$message_map = $GLOBALS['phone_appt_message'];
	//create a new array to store facility_id to message map
	$facility_msg_map = array();
	$facility_phone_map = array();
	//get facilities from the database
	$query = "select fac.id, fac.name, fac.phone from facility as fac";
	$db_res = (sqlStatement($query));
	while ($prow = sqlFetchArray($db_res)) 
	{
		$facility_msg_map[$prow['id']] = $message_map[$prow['name']];
		$facility_phone_map[$prow['id']] = $prow['phone'];
	}
	
	$facility_map = array(
				'msg_map' => $facility_msg_map,
		 		'phone_map' => $facility_phone_map
			      );

	return $facility_map;

}
?>

