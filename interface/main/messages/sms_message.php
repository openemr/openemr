<?php
/** *****************************************************************************************
 *	sms_message.php
 *
 *	Copyright (c)2019 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage messages
 *  @version 2.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

// Sanitize escapes
$sanitize_all_escapes = true;

// Stop fake global registration
$fake_register_globals = false;

require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");

//Included EXT_Message File
include_once("$srcdir/OemrAD/oemrad.globals.php");


use OpenEMR\Core\Header;
use OpenEMR\OemrAd\Smslib;
use OpenEMR\OemrAd\Twiliolib;
use OpenEMR\OemrAd\EmailMessage;

// Set "sender" phone number
$send_phone = preg_replace('/[^0-9]/', '', Smslib::getDefaultFromNo());

// Determine processing mode
$form_id = trim(strip_tags($_REQUEST['id']));
$form_pid = trim(strip_tags($_REQUEST['pid']));
$form_mode = trim(strip_tags($_REQUEST['mode']));
$form_last = isset($_REQUEST['last']) && !empty($_REQUEST['last']) ? trim(strip_tags($_REQUEST['last'])) : 0;
$form_to_phone = trim(strip_tags($_REQUEST['phone']));
$form_message = trim(strip_tags($_REQUEST['message']));
$form_type = trim(strip_tags($_REQUEST['type']));
$form_convid = trim(strip_tags($_REQUEST['convid']));

$form_action = trim(strip_tags($_REQUEST['action']));
$form_msgId = trim(strip_tags($_REQUEST['msgId']));

$form_submode = trim(strip_tags($_REQUEST['submode']));
$form_message_tlp = trim(strip_tags($_REQUEST['message_tlp']));

$readonly = 0;
if($form_id) {
	$readonly = 1;
}

$requestStr = '';
if(!empty($form_action) && !empty($form_msgId)) {
	$requestStr = '?'.http_build_query(array('action' => $form_action, 'msgId' => $form_msgId));
}

//Defualt Data
$default_message = 'free_text';

// Option lists
$message_list = new wmt\Options('SMS_Messages');

// Sending phone override
if (isset($_REQUEST['from'])) {
	$from_phone = trim(strip_tags($_REQUEST['from']));
	$send_phone = preg_replace('/[^0-9]/', '', $from_phone);
}

// Validate sender
if (empty($send_phone)) {
	throw new \Exception("Missing required sender number!!!");
}

$smsType = 'send';
$isEnable = false;

// Validate Patient
if (isset($form_id) && !empty($form_id)) {
	$smsType = 'open';

	$msg_data = sqlQueryNoLog("SELECT * FROM `message_log` WHERE `id`=?", array($form_id));

	$msg_phone = "";
	$smsContent = "";
	if(!empty($msg_data)) {
		if($msg_data['direction'] == "in") {
			$msg_phone = $msg_data['msg_from'];
		} else if($msg_data['direction'] == "out") {
			$msg_phone = $msg_data['msg_to'];
		}

		$msgContent = EmailMessage::getSMSMsgContent($msg_data['message']);
		$smsContent = isset($msgContent['content']) ? $msgContent['content'] : "";
	}
	
	// Retrieve or create patient
	if (empty($msg_data['pid'])) {
		$pid = $msg_data['pid'];
		$pat_data = new wmt\Patient();
		$pat_phone = $msg_phone;
		$pat_name = 'UNKNOWN';
		$form_convid = $msg_data['msg_convid'];
		$isEnable = false;

	} else {
		$pid = $msg_data['pid'];
		$pat_data = wmt\Patient::getPidPatient($pid);
		$pat_phone = preg_replace('/[^0-9]/', '', $msg_phone);
		$pat_name = $pat_data->format_name .' (' . $pid . ')';
		$form_convid = $msg_data['msg_convid'];

		$isEnable = $pat_data->hipaa_allowsms != 'YES' || empty($pat_data->phone_cell) ? true : false;
	}
	
} else if (isset($form_msgId) && !empty($form_msgId) && $form_action == "reply") {
	$smsType = $form_action;
	$pid = $form_pid;

	$msg_data = sqlQuery("SELECT * FROM `message_log` WHERE `id`=?", array($form_msgId));

	$msg_phone = "";
	$smsContent = "";
	if(!empty($msg_data)) {
		if($msg_data['direction'] == "in") {
			$msg_phone = $msg_data['msg_from'];
		} else if($msg_data['direction'] == "out") {
			$msg_phone = $msg_data['msg_to'];
		}

		$msgContent = EmailMessage::getSMSMsgContent($msg_data['message']);
		$smsContent = isset($msgContent['content']) ? $msgContent['content'] : "";
	}

	if (empty($pid)) {
		$pid = null;
		$pat_data = new wmt\Patient();
		$pat_phone = $msg_phone;
		$pat_name = 'UNKNOWN';
		$isEnable = false;
		
	} else {
		$pat_data = wmt\Patient::getPidPatient($pid);
		$pat_phone = preg_replace('/[^0-9]/', '', $msg_phone);
		$pat_name = $pat_data->format_name .' (' . $pid . ')';

		$isEnable = $pat_data->hipaa_allowsms != 'YES' || empty($pat_data->phone_cell) ? true : false;
	}

} else if (isset($form_msgId) && !empty($form_msgId) && $form_action == "resend") {
	$smsType = $form_action;
	$pid = $form_pid;

	$msg_data = sqlQuery("SELECT * FROM `message_log` WHERE `id`=?", array($form_msgId));

	$variableList = array(
		'default_pid' => 'pid',
		'default_message' => 'message_tlp',
		'default_phone' => 'phone',
	);
	if(!empty($msg_data['raw_data'])) {
		$previousData = json_decode($msg_data['raw_data'], true);
	}
	extract(EmailMessage::extractVariable($previousData, $variableList));

	$pat_phone = "";
	$smsContent = "";
	if(!empty($msg_data)) {
		
		if($default_pid == $pid) {
			if($msg_data['direction'] == "in") {
				$pat_phone = $msg_data['msg_from'];
			} else if($msg_data['direction'] == "out") {
				$pat_phone = $msg_data['msg_to'];
			}
		}

		$msgContent = EmailMessage::getSMSMsgContent($msg_data['message']);
		$smsContent = isset($msgContent['content']) ? $msgContent['content'] : "";
	}

	$pat_data = new stdClass();
	$pat_phones = array();
	$pat_name = "";
	$pat_phones_list = array();

	if(!empty($pid)) {
		// Retrieve patient
		$pat_data = wmt\Patient::getPidPatient($pid);
		$pre_pat_phone = isset($pat_data->phone_cell) && !empty($pat_data->phone_cell) ? preg_replace('/[^0-9]/', '', $pat_data->phone_cell) : "";
		$pat_name = $pat_data->format_name .' (' . $pid . ')';

		if(!empty($pre_pat_phone)) {
			$pat_phones_list[] = $pre_pat_phone;
		}

		if(!empty($pat_data->secondary_phone_cell)) {
			$pat_phones_list = array_merge($pat_phones_list, explode(",",$pat_data->secondary_phone_cell));
		}
	}

	$pat_phones = array();
	foreach ($pat_phones_list as $key => $value) {
		$tmpPhone = preg_replace('/[^0-9]/', '', $value);
		$responce = getPhoneNumbers($tmpPhone);
		$pat_phones[] = isset($responce) ? $responce : array();
	}

	$isEnable = $pat_data->hipaa_allowsms != 'YES' || empty($pat_data->phone_cell) ? true : false;

} else {
	$smsType = 'send';
	if (isset($form_pid) && !empty($form_pid)) $pid = $form_pid;
	if (empty($pid)) $pid = $_SESSION['pid'];

	$pat_data = new stdClass();
	$pat_phones = array();
	$pat_name = "";
	$pat_phones_list = array();
	
	if(!empty($pid)) {
		// Retrieve patient
		$pat_data = wmt\Patient::getPidPatient($pid);
		$pat_phone = isset($pat_data->phone_cell) && !empty($pat_data->phone_cell) ? preg_replace('/[^0-9]/', '', $pat_data->phone_cell) : "";
		$pat_name = $pat_data->format_name .' (' . $pid . ')';

		if(!empty($pat_phone)) {
			$pat_phones_list[] = $pat_phone;
		}

		if(!empty($pat_data->secondary_phone_cell)) {
			$pat_phones_list = array_merge($pat_phones_list, explode(",",$pat_data->secondary_phone_cell));
		}
	}

	$pat_phones = array();
	foreach ($pat_phones_list as $key => $value) {
		$tmpPhone = preg_replace('/[^0-9]/', '', $value);
		$responce = getPhoneNumbers($tmpPhone);
		$pat_phones[] = isset($responce) ? $responce : array();
	}

	$isEnable = $pat_data->hipaa_allowsms != 'YES' || empty($pat_data->phone_cell) ? true : false;
}

$msg_phone = "";
$selected_phone = $form_to_phone;

//Manage Type
if($smsType == "send") {
	if(!empty($pat_phones) && empty($form_to_phone)) {
		$selected_phone = isset($pat_phones[0]['msg_phone']) ? $pat_phones[0]['msg_phone'] : "";
	}

	if(!empty($pat_phones)) {
		$msg_phone = $selected_phone;
	}
} else if($smsType == "reply" || $smsType == "resend") {
	//print_r(getPhoneNumbers($pat_phone));
	$patPhoneData = getPhoneNumbers($pat_phone);
	$pat_phone = $patPhoneData['pat_phone'];

	if(!empty($patPhoneData) && empty($form_to_phone)) {
		$selected_phone = isset($patPhoneData['msg_phone']) ? $patPhoneData['msg_phone'] : "";
	}

	if(!empty($pat_phone)) {
		$msg_phone = $selected_phone;
	}
} else if (isset($form_id) && !empty($form_id)) {
	//print_r(getPhoneNumbers($pat_phone));
	$patPhoneData = getPhoneNumbers($pat_phone);
	$pat_phone = $patPhoneData['pat_phone'];

	if(!empty($patPhoneData) && empty($form_to_phone)) {
		$selected_phone = isset($patPhoneData['msg_phone']) ? $patPhoneData['msg_phone'] : "";
	}

	if(!empty($pat_phone)) {
		$msg_phone = $selected_phone;
	}
}

//Get phone numbers
function getPhoneNumbers($pat_phone) {
	// Get phone numbers
	$msg_phone = $pat_phone;
	if(strlen($msg_phone) != 12) {
	  $msg_phone = formattedPhoneNo($msg_phone);
	  
	  $pat_phone = getPhoneNoText($pat_phone);
	  if (strlen($pat_phone) > 10) $pat_phone = substr($pat_phone,0,10);
	  $pat_phone = substr($pat_phone,0,3) ."-". substr($pat_phone,3,3) ."-". substr($pat_phone,6,4);
	}
	return array('msg_phone' => $msg_phone, 'pat_phone' => $pat_phone);
}

function formattedPhoneNo($pat_phone) {
	// Get phone numbers
	$msg_phone = $pat_phone;
	if(strlen($msg_phone) <= 10) {
		if (substr($msg_phone,0,1) != '1') $msg_phone = "1" . $msg_phone;
	}
	return $msg_phone;
}

function getPhoneNoText($pat_phone) {
	// Get phone numbers
	$msg_phone = $pat_phone;
	if(strlen($msg_phone) > 10 && strlen($msg_phone) == 12) {
		$msg_phone = substr($msg_phone,2,12);
	} else if(strlen($msg_phone) > 10 && strlen($msg_phone) == 11) {
		$msg_phone = substr($msg_phone,1,11);
	}
	return $msg_phone;
}

function getVals($value) {
	return isset($value) ? $value : "";
}

function getPhoneNo($fromNumber = '') {
	if(strlen($fromNumber) > 10) {
		$fromNumber = preg_replace("/[^0-9]/", "", $fromNumber);
		$fromNumber = substr($fromNumber,1,10);
	}

	$fromNumber = '(^|,)(1)?('.$fromNumber.')(,|$)';

	return $fromNumber;
}

// Ajax check for new messages 
if ($form_mode == 'retrieve') {

	$content = "";
	if($form_message_tlp != 'free_text') {
		// Retrieve content
		try {

			// Get message template
			$template = wmt\Template::Lookup($form_message_tlp, getVals($pat_data->language));
			
			// Fetch merge data
			$data = new wmt\Grab(getVals($pat_data->language));
			$data->loadData(getVals($pat_data->pid), $_SESSION['authId']);
			
			// Perform data merge
			$template->Merge($data->getData());
			$content = $template->text_merged;
			$content_html = $template->html_merged;
			
			// Deal with imbedded links
			$content = str_replace('<br>', "\n", $content);
			$content = str_replace('http:', 'https:', $content);
			$content = str_replace('target="_blank"', '', $content);
			$content = str_replace("target='_blank'", '', $content);

		} catch (Exception $e) {
			$content = $e->getMessage();
		}
	}

	// Return new messages
	echo json_encode(array('content' => $content));
	
	// Done with ajax
	exit();
}

// Ajax transmit new message
if ($form_mode == 'transmit') {
	$new_type = 'SMS';
	if ($form_type == 'NOTE') $new_type = 'NOTE';
	
	// Do processing
	$new_status = '';
	
	// Send SMS message
//	$sms = new wmt\SMS();
	//$sms = new wmt\Nexmo($send_phone);
	$sms = Smslib::getSmsObj($send_phone);
	$sms->pid = $pid;
	
	if ($new_type == 'SMS') {
		if (empty($form_to_phone)) {
			$new_status = 'Missing phone number error!!';
		} else {
			// Check for message
			if (!empty($form_message)) {
				$result = $sms->smsTransmit($form_to_phone, $form_message, 'text');
				$msgId = $result['msgid'];
				$msgStatus = isset($result['msgStatus']) ? $result['msgStatus'] : 'MESSAGE_SENT';

				// Check if sent properly
				if (empty($msgId)) {
					$new_status = 'Message delivery failure!!';
				} else {

					$raw_data = json_encode(EmailMessage::includeRequest($_REQUEST, array(
						'pid',
						'message_tlp', 
						'phone'
					)));

					// Log the message
					$datetime = strtotime('now');
					$msg_date = date('Y-m-d H:i:s', $datetime);
					$sms->logSMS('SMS_MESSAGE', $form_to_phone, $send_phone, $pid, $msgId, $msg_date, $msgStatus, $form_message, 'out', false, $raw_data);

					/*Update Status of Message*/
					if($form_action == "reply" && !empty($form_msgId)) {
						EmailMessage::updateStatusOfMsg($form_msgId, false);
					}

					// Buffer output
					ob_start();
					$msg_time = date('h:i A - M d, Y', $datetime);
?>
		<div class="sms_send" >
			<div class="sms_send_stamp">YOU: <?php echo $msg_time ?></div>
			<div class="sms_send_text"><?php echo $form_message ?></div>
		</div>
<?php 
					// Close buffer
					$new_content = ob_get_clean();
				}
			}
		}
	}
	
	if ($new_type == 'NOTE') {
		// Check for message
		if (!empty($form_message)) {
			// Log the message
			$datetime = strtotime('now');
			$msg_date = date('Y-m-d H:i:s', $datetime);
			$sms->logSMS('PRIVATE_NOTE', null, null, $pid, $form_convid, null, null, $msg_date, 'PRIVATE_NOTE', $form_message);

			// Buffer output
			ob_start();
			$msg_time = date('h:i A - M d, Y', $datetime);
?>
		<div class="sms_note" >
			<div class="sms_note_stamp">YOU: <?php echo $msg_time ?></div>
			<div class="sms_note_text"><?php echo $form_message ?></div>
		</div>
<?php 
			// Close buffer
			$new_content = ob_get_clean();
		}
	}
	
	// Return new messages
	echo json_encode(array('status'=>$new_status, 'content'=>$new_content));
	
	// Done with ajax
	exit();
}

// Ajax check for new messages 
if ($form_mode == 'refresh') {
	$new_content = '';
	$new_last = $form_last;

	$binds = array();
	$result = array();

	if(isset($selected_phone) && !empty($selected_phone)) {
	// Find new messages for this PID
	$sql = "SELECT ml.*, ";
	$sql .= "IFNULL(ml.`delivered_status`, ml.`msg_status`) AS msg_status, ml.`direction`, ";
	$sql .= "IFNULL(ml.`delivered_time`, ml.`msg_time`) AS msg_time, ";
	$sql .= "CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name' ";
	$sql .= "FROM `message_log` ml ";
	$sql .= "LEFT JOIN `users` us ON ml.`userid` = us.`id` ";
	
	$sql .= "WHERE ml.`type` LIKE 'SMS' ";
	if($smsType == "send") {
		$sql .= "AND ml.`type` LIKE 'SMS' AND ml.`pid` = ? AND ( (ml.`direction` = 'out' AND ml.`msg_to` = '$selected_phone' ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
		$binds[] = $pid;

	} else if($smsType == "reply" || $smsType == "resend") {
		if(empty($pid)) {
			$sql .= "AND (ml.`pid` IS NULL OR ml.`pid` = '') AND ( (ml.`direction` = 'out' AND ml.`msg_to` = $selected_phone ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
		} else {
			$sql .= "AND ml.`pid` = ? AND ( (ml.`direction` = 'out' AND ml.`msg_to` = $selected_phone ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
			$binds[] = $pid;
		}
	} else if($smsType == "open") {
		if(isset($_REQUEST['onlymsg']) && $_REQUEST['onlymsg'] == '1') {
			$sql .= "AND ml.`id` = ? ";
			$binds[] = $form_id;
		}

		$sql .= "AND ml.`pid` = ? AND ( (ml.`direction` = 'out' AND ml.`msg_to` = $selected_phone ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
		$binds[] = $pid;
	}
		
	$sql .= "AND ml.`id` > ? AND ml.`type` LIKE 'SMS' ORDER BY `id`";
	$binds[] = $form_last;

	// Retrieve message records
	$result = sqlStatementNoLog($sql, $binds);

	}
		
	// Buffer output
	ob_start();

	// Print new message content
	while ($msg_data = sqlFetchArray($result)) {
		// Save last record
		$new_last = $msg_data['id'];

		// Only SMS for now
		if ($msg_data['type'] != 'SMS') continue;
		
		// Format date/time stamp
		$msg_time = 'UNKNOWN';
		if (strtotime($msg_data['msg_time']) !== false) {
			$msg_time = date('h:i A - M d, Y', strtotime($msg_data['msg_time']));
		}
		
		if($form_submode == "refresh") {
				// Message from patient
				if ($msg_data['direction'] == 'in') { // inbound phone number
				?>
					<div class="sms_from" >
						<div class="sms_from_stamp">SMS: <?php echo $msg_time ?></div>
						<div class="sms_from_text"><?php echo $msg_data['message']?></div>
					</div>
				<?php 
				}

				// Message to patient
				if ($msg_data['direction'] == 'out') { // outbound phone number
				?>
						<div class="sms_send" >
							<div class="sms_send_stamp"><?php echo (empty($msg_data['user_name']))? 'AUTOMATED' : $msg_data['user_name'] ?>: <?php echo $msg_time ?></div>
							<div class="sms_send_text"><?php echo $msg_data['message']?></div>
						</div>
				<?php 
				}
					
				// Private Note
				if (empty($msg_data['msg_to']) && empty($msg_data['msg_from'])) {
				?>
						<div class="sms_note" >
							<div class="sms_note_stamp"><?php echo $msg_data['user_name'] ?>: <?php echo $msg_time ?></div>
							<div class="sms_note_text"><?php echo $msg_data['message']?></div>
						</div>
				<?php 
				} 

		} else {		
		// Message from patient
				if ($msg_data['direction'] == 'in') { // inbound phone number
		?>
				<div class="sms_from" >
					<div class="sms_from_stamp">SMS: <?php echo $msg_time ?></div>
					<div class="sms_from_text"><?php echo $msg_data['message']?></div>
				</div>
		<?php 
				}
		}
	}
	
	// Close buffer
	$new_content = ob_get_clean();
	
	// Return new messages
	echo json_encode(array('last'=>$new_last, 'content'=>$new_content));
	
	// Done with ajax
	exit();
}

// Retrieve messages
$msg_list = array();
$result = array();
		
if(isset($selected_phone) && !empty($selected_phone)) {

// Find conversations for this PID and/or convid
$sql = "SELECT ml.*, ";
$sql .= "IFNULL(ml.`delivered_status`, ml.`msg_status`) AS msg_status, ml.`direction`, ";
$sql .= "IFNULL(ml.`delivered_time`, ml.`msg_time`) AS msg_time, ";
$sql .= "CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name' ";
$sql .= "FROM `message_log` ml ";
$sql .= "LEFT JOIN `users` us ON ml.`userid` = us.`id` ";
$sql .= "WHERE ml.`type` LIKE 'SMS' ";

if($smsType == "send") {
	$sql .= "AND ml.`pid` = ? AND ( (ml.`direction` = 'out' AND ml.`msg_to` = $selected_phone ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
	$binds[] = $pid;
} else if($smsType == "reply" || $smsType == "resend") {
	if(empty($pid)) {
		$sql .= "AND (ml.`pid` IS NULL OR ml.`pid` = '') AND ( (ml.`direction` = 'out' AND ml.`msg_to` = $selected_phone ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
	} else {
		$sql .= "AND ml.`pid` = ? AND ( (ml.`direction` = 'out' AND ml.`msg_to` = $selected_phone ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
		$binds[] = $pid;
	}
} else if($smsType == "open") {
	if(isset($_REQUEST['onlymsg']) && $_REQUEST['onlymsg'] == '1') {
		$sql .= "AND ml.`id` = ? ";
		$binds[] = $form_id;
	}

	$sql .= "AND ml.`pid` = ? AND ( (ml.`direction` = 'out' AND ml.`msg_to` = $selected_phone ) OR ( ml.`direction` = 'in' AND ml.`msg_from` = $selected_phone ) ) ";
	$binds[] = $pid;
}

$sql .= "AND ml.`id` > ? AND ml.`type` LIKE 'SMS' ORDER BY `id`";
$binds[] = $form_last;
	
// Retrieve message records
$result = sqlStatementNoLog($sql, $binds);

}

?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>	<html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>	<html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />

	<title>SMS Chat Client</title>
	<meta name="author" content="Ron Criswell" />
	<meta name="description" content="SMS Chat Client" />
	<meta name="copyright" content="&copy;<?php echo date('Y') ?> Williams Medical Technologies, Inc.  All rights reserved." />

	<meta name="viewport" content="width=device-width,initial-scale=1" />

	<link rel="shortcut icon" href="images/favicon.ico" />

	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'main-theme', 'fontawesome', 'jquery-ui-base']); ?>

	<style>
	html, body {
		margin: 0;
		height: 100vh;
		min-height: 100vh;
	}
	body {
		margin: 0 10px;
		display: flex;
		flex-direction: column;
		align-items: center;
		overflow-y: hidden;
	}
	header {
		order: 1;
	    flex-shrink: 0;
	    flex-basis: 40px;
	    width: 100%;
	    min-width: 600px;
	}
	main {
		order: 2;
		flex-grow: 1;
		overflow-y: scroll;
	    width: 100%;
	    min-width: 600px;
	    padding: 10px 0;
	    margin-left: 0px !important;
	}
	footer {
		order: 3;
		flex-shrink: 0;
		flex-basis: 50px;
	    width: 100%;
	    min-width: 600px;
	    margin-top: 10px;
	    margin-bottom: 10px;
	    border: 1px solid #ccc;
	    white-space: nowrap;
	}
	.sms_from {
		max-width: 70%;
		width: auto;
		float: left;
		clear: both;
		padding: 0 10px;
		margin-bottom: 5px;
	}
	.sms_from_stamp {
		color: #888;
		padding: 3px 20px 0 3px;
		text-align: left;
	}
	.sms_from_text {
		padding: 6px 12px;
		line-height: 24px;
		border-radius: 0 10px 10px 10px;
		background-color: #ff06;
		white-space: pre-wrap;
	}
	.sms_send {
		max-width: 70%;
		width: auto;
		float: right;
		clear: both;
		padding: 0 10px;
		margin-bottom: 5px;
	}
	.sms_send_stamp {
		color: #888;
		padding: 3px 3px 0 20px;
		text-align: right;
	}
	.sms_send_text {
		padding: 6px 12px;
		line-height: 24px;
		border-radius: 10px 10px 0 10px;
		background-color: #eee;
		white-space: pre-wrap;
		text-align: right;
	}

	.sms_note {
		max-width: 60%;
		width: auto;
		margin: auto;
		clear: both;
		padding: 0 10px;
		margin-bottom: 5px;
	}
	.sms_note_stamp {
		color: #888;
		padding: 3px 3px 0 20px;
		text-align: center;
	}
	.sms_note_text {
		padding: 6px 12px;
		line-height: 24px;
		border-radius: 10px 10px 10px 10px;
		background-color: #ff000030;
		white-space: pre-wrap;
		text-align: center;
	}

	.select_phone_container {
		margin-top: 15px;
	    margin-bottom: 10px;
	} 

	.select_phone_container .select_phone {
		font-size: 30px;
	    padding-left: 10px;
	    padding-right: 10px;
	    padding-top: 4px;
	    padding-bottom: 4px;
	}

	.select_phone_container .select_phone > option {
		font-size: 15px;
	}

	.smsSendContainer {
		padding: 10px;
		display: grid;
		grid-template-columns: 1fr minmax(auto, 250px);
		grid-gap: 10px;
	}

	.patientChange {
		width: auto;
	    height: auto;
	    float: left;
	    margin-top: 15px!important;
	    margin-bottom: 10px!important;
	    font-size: 22px!important;
	    padding-left: 10px!important;
	    padding-right: 10px!important;
	    padding-top: 4px!important;
	    padding-bottom: 4px!important;
	    background-color: #fff!important;
	}

	</style>
</head>
<body>
	<header>
		<?php if($smsType == "resend") { ?>
			<input type='text' class='form-control readonlyInput patientChange' value='<?php echo text($pat_name); ?>' onClick="selectPatientButton()" readonly />
		<?php } else { ?>
			<h2 style="float:left"><?php echo text($pat_name); ?></h2>
		<?php } ?>

		<?php if(($smsType == "resend" || $smsType == "send") && !empty($pat_phones)) { ?>
			<div class="select_phone_container" style="float:right">
			<select id="select_phone_no" class="select_phone">
				<?php
					foreach ($pat_phones as $key => $phoneItem) {
						$selectedItem = ($phoneItem['msg_phone'] == $selected_phone) ? 'selected="selected"' : ''
						?>
							<option value="<?php echo $phoneItem['msg_phone'] ?>" <?php echo $selectedItem; ?>><?php echo $phoneItem['pat_phone'] ?></option>
						<?php
					}
				?>	
			</select>
			</div>
		<?php } else if($smsType == "reply") { ?>
			<h2 style="float:right"><?php echo text($pat_phone); ?></h2>
		<?php } else {
			?>
			<h2 style="float:right"><?php echo text($pat_phone); ?></h2>
			<?php
		} ?>
	</header>

	<main id="content" style="border:1px solid #ccc; min-height: 300px;">

<?php 
// Display messages in chronological order 
while ($msg_data = sqlFetchArray($result)) {
	
	// Save last record
	$last_id = $msg_data['id'];
	
	// Format date/time stamp
	$msg_time = 'UNKNOWN';
	if (strtotime($msg_data['msg_time']) !== false) {
		$msg_time = date('h:i A - M d, Y', strtotime($msg_data['msg_time']));
	}
	
	// Message from patient
	if ($msg_data['direction'] == 'in') { // inbound phone number
?>
		<div class="sms_from" >
			<div class="sms_from_stamp"><?php echo xlt('SMS'); ?>:&nbsp;<?php echo $msg_time ?></div>
			<div class="sms_from_text"><?php echo text($msg_data['message']); ?></div>
		</div>
<?php 
	} 

	// Message to patient
	if ($msg_data['direction'] == 'out') { // outbound phone number
?>
		<div class="sms_send" >
			<div class="sms_send_stamp"><?php echo (empty($msg_data['user_name']))? 'AUTOMATED' : $msg_data['user_name'] ?>: <?php echo $msg_time ?></div>
			<div class="sms_send_text"><?php echo $msg_data['message']?></div>
		</div>
<?php 
	}
	
	// Private Note
	if (empty($msg_data['msg_to']) && empty($msg_data['msg_from'])) {
?>
		<div class="sms_note" >
			<div class="sms_note_stamp"><?php echo $msg_data['user_name'] ?>: <?php echo $msg_time ?></div>
			<div class="sms_note_text"><?php echo $msg_data['message']?></div>
		</div>
<?php 
	} 

}
?>
		<div id="send_spinner" class="notification" style="position:absolute;color:white;font-weight:bold;padding:20px;border-radius:10px;background-color:red;left:45%;top:40%;z-index:850;display:none;">
			Processing...
		</div>
					
	</main>
	
	<footer>
		<form id="resend_form">
			<input type="hidden" id="re_msgId" name="msgId" value="<?php echo $form_msgId ?>" />
			<input type="hidden" id="re_pid" name="pid" value="<?php echo $form_pid ?>" />
			<input type="hidden" id="re_form_action" name="action" value="<?php echo $form_action ?>" />
		</form>
		<form>
			<input type="hidden" id="mode" name="mode" value="" />
			<input type="hidden" id="last" name="last" value="<?php echo $last_id ?>" />
			<input type="hidden" id="phone" name="phone" value="<?php echo $msg_phone ?>" />
			<input type="hidden" id="convid" name="convid" value="<?php echo $form_convid ?>" />
			<input type="hidden" id="id" name="id" value="<?php echo $form_id ?>" />
			<input type="hidden" id="pid" name="pid" value="<?php echo $pid; ?>" />

			<input type="hidden" id="msgId" name="msgId" value="<?php echo $form_msgId; ?>" />
			<input type="hidden" id="action" name="action" value="<?php echo $form_action; ?>" />

<?php if ($isEnable == true) { ?>
			<div style="text-align:center;width:100%;padding:20px 0;">
				<span style="color:red;font-weight:bold">This patient has not approved SMS messages or no mobile phone number is present, messaging is disabled!</span>
			</div>
<?php } else { ?>
			
			<div class="smsSendContainer">
			<?php if($form_action == 'resend') { ?>
				<textarea id='message' name='message' style="margin:10;padding:10px;border:none;border-radius:5px;resize:none;background-color:#eee;vertical-align:middle;"><?php echo $smsContent; ?></textarea>
			<?php } else { ?>
				<textarea id='message' name='message' style="padding:10px;border:none;border-radius:5px;resize:vertical;background-color:#eee;vertical-align:middle;"></textarea>
			<?php } ?>

			<div style="text-align:right;">
				<?php if(empty($form_id)) {
					$sendBtnTitle = "SEND TO PATIENT";
					if ($form_action == 'resend') $sendBtnTitle = "RESEND TO PATIENT"; 
				?>

				<?php if($readonly === 1) { ?>
					<select id="message_tlp" name="message_tlp" class='form-control' disabled>
						<?php $message_list->showOptions($default_message) ?>
					</select>
				<?php } else { ?>
					<select id="message_tlp" name="message_tlp" class='form-control'>
						<?php $message_list->showOptions($default_message) ?>
					</select>
				<?php } ?>

				<input id="send_sms" type="button" class="btn btn-primary" onclick="ajaxTransmit('SMS')" style="padding:5px 12px; margin-top: 5px;" value="<?php echo $sendBtnTitle; ?>">
				<?php } ?>
				<!-- br>
				<input id="store_note" type="button" onclick="ajaxTransmit('NOTE')" style="margin:5px 0 10px;padding:5px 12px;" value="PRIVATE NOTE" -->
			</div>
			</div>
<?php } ?>
		</form>
	</footer>
	
	<script>
		var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
		var refreshTimer = null;

		<?php include_once($GLOBALS['srcdir']."/restoreSession.php"); ?>
	
		<?php include_once($GLOBALS['srcdir']."/wmt-v2/ajax/init_ajax.inc.js"); ?>
		
 		// background refresh process
		function ajaxRefresh(type = 'fetch') {
			top.restoreSession();

			// organize the data
			var data = [];

			if(type == 'refresh') {
				var lastId = '';
			} else {
				var lastId = $('#last').val();
			}

			data.push({name: "mode", value: "refresh"});
			data.push({name: "submode", value: type});
			data.push({name: "last", value: lastId});
			data.push({name: "phone", value: $('#phone').val()});
			data.push({name: "id", value: $('#id').val()});

			data.push({name: "pid", value: $('#pid').val()});
			data.push({name: "msgId", value: $('#msgId').val()});
			data.push({name: "action", value: $('#action').val()});

 			$.ajax ({
				type: "POST",
				url: "sms_message.php<?php echo $requestStr; ?>",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
		 			if (result.last == 'error') {
	 				    clearInterval(refreshTimer);
	 	 				alert('Refresh Failed...');
	 				} else {
 	 					
 	 					$('#last').val(result.last);

 	 					if(type == 'refresh') {
 	 						$('#content').html(result.content);
 	 					} else {
 	 						$('#content').append(result.content);
 	 					}
//						$('#content').animate({
//							scrollTop: $('#content')[0].scrollHeight
//						});
	 				}
				},
				error: function() {
			    	clearInterval(refreshTimer);
					alert('Refresh Failed...');
				}, 	 					

				async:   true
			});
		}

 		// background refresh process
		function ajaxTransmit(type) {
			top.restoreSession();

			// show spinner
			$('#send_spinner').show();
			
			// organize the data
			var data = [];
			data.push({name: "mode", value: 'transmit'});
			data.push({name: "type", value: type});
			data.push({name: "phone", value: $('#phone').val()});
			data.push({name: "message", value: $('#message').val()});
			data.push({name: "pid", value: $('#pid').val()});

			data.push({name: "msgId", value: $('#msgId').val()});
			data.push({name: "action", value: $('#action').val()});
			data.push({name: "message_tlp", value: $('#message_tlp').val()});

 			$.ajax ({
				type: "POST",
				url: "sms_message.php<?php echo $requestStr; ?>",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
					$('#send_spinner').hide();
		 			if (result.status == '') {
 	 					$('#content').append(result.content);
						$('#content').animate({
							scrollTop: $('#content')[0].scrollHeight
						});
		 				$('#message').val('');
		 				$('#message_tlp').val('free_text');

		 				<?php if($form_action == "reply" || $form_action == "resend") { ?>
		 					// Close window and refresh
			 				opener.doRefresh('<?php echo $form_action ?>');
							dlgclose();
		 				<?php } ?>

	 				} else {
	 				    clearInterval(refreshTimer);
	 				    var errorMsg = result.status ? result.status : JSON.stringify(result);
	 	 				alert('Send Failed... \nError: ' + errorMsg);
	 				}
				},
				error: function(result) {
					$('#send_spinner').hide();
					var errorMsg = result ? JSON.stringify(result) : 'Something went wrong.';
					alert('Send Failed... \nError: ' + errorMsg);
				}, 	 					

				async:   true
			});
		}

		// background refresh process
		function ajaxChange() {
			top.restoreSession();
			
			// organize the data
			var data = [];
			data.push({name: "mode", value: 'retrieve'});
			data.push({name: "pid", value: $('#pid').val()});
			data.push({name: "message_tlp", value: $('#message_tlp').val()});

 			$.ajax ({
				type: "POST",
				url: "sms_message.php<?php echo $requestStr; ?>",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
					if (result.content == 'error') {
	 	 				alert('Retrieve Failed...');
	 				} else {
 	 					$('#message').val(result.content);
	 				}
				},
				error: function() {
					alert('Retrieve Failed...');
				}, 	 					

				async:   true
			});
		}

		//Open popup for patient selection
		function selectPatientButton() {
			dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 800, 400);
		}

		// This is for callback by the find-patient popup.
		function setpatient(pid, lname, fname, dob) {
			var form_action = $('#re_form_action').val();
			if(form_action == "resend") {
				$('#re_pid').val(pid);
				$('#resend_form').submit();
			}
		}

		<?php if($readonly === 0) { ?>
			// setup jquery exit check
			$(document).ready(function(){
				//Init load
				ajaxChange();

				// message selection
				$('#message_tlp').change(ajaxChange);

			});
		<?php } ?>

		// setup on change
		$(document).ready(function(){
			$('#select_phone_no').change(function() {
				var cVal = $(this).val();
				$('#phone').val(cVal);
				
				<?php //if(isset($_REQUEST['onlymsg']) && $_REQUEST['onlymsg'] == '1') { ?>
					ajaxRefresh('refresh');
				<?php //} ?>
			});
		});

		// setup jquery exit check
		$(document).ready(function(){
			// scroll to bottom
			$('#content').animate({
				scrollTop: $('#content')[0].scrollHeight
			});

			// start refresh timer
			refreshTimer = setInterval(ajaxRefresh, 30000);
		});
		
	</script>
</body>

</html>

