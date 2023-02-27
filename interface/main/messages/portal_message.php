<?php
/** *****************************************************************************************
 *	portal_message.php
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
 *  @version 1.0.0
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
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\EmailMessage;

// Determine processing mode
$form_id = trim(strip_tags($_REQUEST['id']));
$form_pid = trim(strip_tags($_REQUEST['pid']));
$form_mode = trim(strip_tags($_REQUEST['mode']));
$form_message = trim(strip_tags($_REQUEST['message']));
$form_event = trim(strip_tags($_REQUEST['event']));
$form_last = trim(strip_tags($_REQUEST['last']));

$form_action = trim(strip_tags($_REQUEST['action']));
$form_msgId = trim(strip_tags($_REQUEST['msgId']));

$requestStr = '';
if(!empty($form_action) && !empty($form_msgId)) {
	$requestStr = '?'.http_build_query(array('action' => $form_action, 'msgId' => $form_msgId));
}

// Option lists
$subject_list = new wmt\Options('Portal_Subject');

	
// Validate Patient
$content = '';
$pat_name = '';
$pat_data = false;
$msg_data = false;
if (isset($form_id) && !empty($form_id)) {
	$sql = "SELECT ml.*, ";
	$sql .= "IFNULL(ml.`delivered_status`, ml.`msg_status`) AS msg_status, ml.`direction`, ";
	$sql .= "IFNULL(ml.`delivered_time`, ml.`msg_time`) AS msg_time, ";
	$sql .= "CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name' ";
	$sql .= "FROM `message_log` ml ";
	$sql .= "LEFT JOIN `users` us ON ml.`userid` = us.`id` ";
	$sql .= "WHERE ml.`id` = ? AND ml.`date` >= ?";
	
	// Retrieve single message
	$msg_data = sqlQueryNoLog($sql, array($form_id, $form_last));
	
	// Format date/time stamp
	$msg_time = 'UNKNOWN';
	if (strtotime($msg_data['msg_time']) !== false) {
		$msg_time = date('h:i A - M d, Y', strtotime($msg_data['msg_time']));
	}
	
	// Retrieve or create patient
	if (empty($msg_data['pid'])) {
		$pid = null;
		$pat_data = new wmt\Patient();
		$pat_name = 'UNKNOWN';
	} else {
		$pid = $msg_data['pid'];
		$pat_data = wmt\Patient::getPidPatient($pid);
		$pat_name = $pat_data->format_name;
	}
	
	// Figure out TO / FROM
	$to = $from = '';
	if ($msg_data['msg_to'] == 'PATIENT') {
		$to = $pat_name;
		$from = (empty($msg_data['user_name']))? 'PORTAL SUPPORT' : $msg_data['user_name'];
	} elseif ($msg_data['msg_from'] == 'PATIENT') {
		$from = $pat_name;
		$to = (empty($msg_data['user_name']))? 'PORTAL SUPPORT' : $msg_data['user_name'];
	}
	
} else {

	if (isset($form_pid) && !empty($form_pid)) $pid = $form_pid;
	if (empty($pid)) $pid = $_SESSION['pid'];
	
	// Retrieve patient
	$pat_data = wmt\Patient::getPidPatient($pid);
	$pat_name = $pat_data->format_name;
	
	// Set TO/FROM
	$to = $pat_name;
	$from = (empty($msg_data['user_name']))? 'PORTAL SUPPORT' : $msg_data['user_name'];
}

// Retrieve user
$user_data = sqlQueryNoLog("SELECT CONCAT(LEFT(`fname`,1), '. ',`lname`) AS 'name' FROM `users` WHERE `id` = ?", array($_SESSION['authUserID']));
$user_name = (empty($user_data['name']))? 'PORTAL SUPPORT' : $user_data['name'];

// Previous content
if ($msg_data['id']) {
	$content = "From: " . text( $from ) . "\n";
	$content .= "To: " . text( $to ) . "\n";
	$content .= "Date: " . text( $msg_data['msg_time'] ) . "\n";
	$content .= "Topic: " . $subject_list->getItem($msg_data['event']) . "\n\n";
	$content .= text($msg_data['message']);
	
	// Save current pointer
	$form_last = $msg_data['date'];
}

// Deal with imbedded links
$content = str_replace('http:', 'https:', $content);
$content = str_replace('target="_blank"', '', $content);
$content = str_replace("target='_blank'", '', $content);

// Ajax check for new messages 
if ($form_mode == 'refresh') {
	
	// Return new messages
	echo json_encode(array('last'=>$form_last, 'content'=>$content));
	
	// Done with ajax
	exit();
}
	
// Ajax transmit new message
if ($form_mode == 'transmit') {

	// Do processing
	$new_status = '';
	
	// Check for message
	if (!empty($form_message)) {
		
		// Get date
		$datetime = strtotime('now');
		$msg_date = date('Y-m-d H:i:s', $datetime);
		
		// Create a new record
		if (empty($msg_data['id'])) {

			$newMsgId = strtotime('now');
			$msgConvId = $newMsgId;

			// Create log entry
			$binds = array();
			$binds[] = 'PORTAL_OUTPUT';
			$binds[] = 'SFA Patient Portal';
			$binds[] = $_SESSION['authUserID'];
			$binds[] = (empty($pid)) ? $_SESSION['pid'] : $pid;
			$binds[] = 'PATIENT';
			$binds[] = 'CLINIC';
			$binds[] = $msgConvId; // message id of original message
			$binds[] = $newMsgId; // message id of current message
			$binds[] = $msg_date;
			$binds[] = 'MESSAGE_SENT';
			$binds[] = $form_message;
		
			// Store log record
			$sql = "INSERT INTO `message_log` SET ";
			$sql .= "`type`='PORTAL', `event`=?, `gateway`=?, `direction`='out', `activity`='0', `userid`=?, `pid`=?, `msg_to`=?, `msg_from`=?, `msg_convid`=?, `msg_newid`=?, `msg_time`=?, `msg_status`=?, `message`=?";
			sqlStatementNoLog($sql, $binds);

			if($form_action == "reply" && !empty($form_msgId)) {
				EmailMessage::updateStatusOfMsg($form_msgId);
			}

		} else {
			
			// Append old content to new message
			$form_message .= "\n\n---------------------------------------------------------\n";
			$form_message .= $content;
			
			$new_content = "From: $user_name\n";
			$new_content .= "To: $pat_name\n";
			$new_content .= "Date: $msg_date\n";
			$new_content .= "Topic: " . $subject_list->getItem($msg_data['event']) . "\n\n";
			$new_content .= $form_message;
			
			// Update existing record
			$binds = array();
			$binds[] = $form_event;
			$binds[] = $_SESSION['authUserID'];
			$binds[] = 'PATIENT';
			$binds[] = 'CLINIC';
			$binds[] = $msg_date;
			$binds[] = 'MESSAGE_SENT';
			$binds[] = $form_message;
			$binds[] = $msg_data['id'];
			
			// Write new record
			$sql = "UPDATE `message_log` SET ";
			$sql .= "`event`=?, `direction`='out', `userid`=?, `msg_to`=?, `msg_from`=?, `msg_time`=?, `msg_status`=?, `message`=? ";
			$sql .= "WHERE `id` = ?";
			
			sqlStatement($sql, $binds);
		}
	
		// Return new messages
		echo json_encode(array('status'=>$new_status, 'last'=>$msg_date, 'content'=>$new_content));

	}
	
	// Done with ajax
	exit();
}



?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>	<html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>	<html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />

	<title>Portal Message</title>
	<meta name="author" content="Ron Criswell" />
	<meta name="description" content="Portal Message" />
	<meta name="copyright" content="&copy;<?php echo date('Y') ?> Williams Medical Technologies, Inc.  All rights reserved." />

	<meta name="viewport" content="width=device-width,initial-scale=1" />

	<link rel="shortcut icon" href="images/favicon.ico" />

	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'main-theme', 'fontawesome', 'jquery-ui-base' ]); ?>

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
	    width: 95vw;
	    min-width: 600px;
	    margin-bottom: 10px;
	}
	main {
		order: 2;
		flex-grow: 1;
	    width: 97vw;
	    min-width: 600px;
	    border: 1px solid #ccc;
	}
	footer {
		order: 3;
		flex-shrink: 0;
		flex-basis: 50px;
	    width: 97vw;
	    min-width: 600px;
	    margin-top: 10px;
	    margin-bottom: 10px;
	    border: 1px solid #ccc;
	    white-space: nowrap;
	}
	#content {
		line-height: 24px;
		white-space: pre-wrap;
		width: 94%;
		height: 86%;
		margin: 10px;
		padding: 10px;
		border: none;
		border-radius: 5px;
		resize: vertical;
		background-color: transparent;
		vertical-align: middle;
	}
	</style>
</head>
<body>
	<header style="width:95vw;margin-top:20px">
		<table>
			<tr>
				<td style='text-align:right;min-width:60px'>
					<b><?php echo xlt('Topic'); ?>:&nbsp;</b>
				</td>
				<td>
					<select id="event" name="event" class="form-control">
						<?php $subject_list->showOptions($msg_data['event'], '-- Select Topic --') ?>
					</select>
				</td>
			</tr>
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('Patient'); ?>:&nbsp;</b>
				</td>
				<td>
					<input type='text' class='form-control' value='<?php echo $pat_data->format_name ?>' disabled />
				</td>
			</tr>
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('From'); ?>:&nbsp;</b>
				</td>
				<td>
					<input type='text' class='form-control' value='<?php echo $user_name ?>' disabled />
				</td>
			</tr>			
		</table>
	</header>

	<main>
		<textarea id="content" name="content" class='form-control' readonly ><?php echo $content ?></textarea>
	</main>

	<div id="send_spinner" class="notification" style="position:absolute;color:white;font-weight:bold;padding:20px;border-radius:10px;background-color:red;left:45%;top:40%;z-index:850;display:none;">
			Processing...
		</div>
					
	<footer>
		<form>
			<input type="hidden" id="mode" name="mode" value="" />
			<input type="hidden" id="event" name="event" value="<?php echo $form_event ?>" />
			<input type="hidden" id="last" name="last" value="<?php echo $form_last ?>" />
			<input type="hidden" id="id" name="id" value="<?php echo $form_id ?>" />
			<input type="hidden" id="pid" name="pid" value="<?php echo $form_pid ?>" />

<?php if ($pat_data->allow_patient_portal != 'YES') { ?>
			<div style="text-align:center;width:100%;padding:20px 0;">
				<span style="color:red;font-weight:bold"><?php echo xlt('This patient has not approved use of the patient portal, portal messaging is disabled!'); ?></span>
			</div>
<?php } else { ?>
			<textarea id='message' name='message' style="width:70%;margin:10px;padding:10px;border:none;border-radius:5px;resize:vertical;background-color:#eee;vertical-align:middle;"></textarea>
			<div style="float:right;width:20%;text-align:right;margin-right:20px">
				<?php if($form_id) { ?>
				<?php } else { ?>
				<input id="send_portal" type="button" onclick="ajaxTransmit('portal')" style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xlt('SEND TO PORTAL'); ?>">
				<?php } ?>
				<!-- br>
				<input id="store_note" type="button" onclick="ajaxTransmit('NOTE')" style="margin:5px 0 10px;padding:5px 12px;" value="PRIVATE NOTE" -->
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
		function ajaxRefresh() {
			top.restoreSession();

			// organize the data
			var data = [];
			data.push({name: "mode", value: "refresh"});
			data.push({name: "last", value: $('#last').val()});			
			data.push({name: "id", value: $('#id').val()});

 			$.ajax ({
				type: "POST",
				url: "portal_message.php<?php echo $requestStr; ?>",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
		 			if (result.last == 'error') {
	 				    clearInterval(refreshTimer);
	 	 				alert('Refresh Failed...');
	 				} else {
 	 					$('#last').val(result.last);
 	 					$('#content').val(result.content);
						$('#content').animate({
							scrollTop: 0
						});
	 				}
				},
				error: function() {
			    	clearInterval(refreshTimer);
					alert('Refresh Failed...');
				}, 	 					

				async:   true
			});
		}

 		// background update process
		function ajaxTransmit(type) {
			top.restoreSession();

			// show spinner
			$('#send_spinner').show();
			
			// organize the data
			var data = [];
			data.push({name: "mode", value: 'transmit'});
			data.push({name: "event", value: $('#event').val()});
			data.push({name: "id", value: $('#id').val()});
			data.push({name: "pid", value: $('#pid').val()});
			data.push({name: "message", value: $('#message').val()});

 			$.ajax ({
				type: "POST",
				url: "portal_message.php<?php echo $requestStr; ?>",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
					$('#send_spinner').hide();
		 			if (result.status == '') {
 	 					$('#content').val(result.content);
//						$('#content').animate({
//							scrollTop: 0
//						});
	 				} else {
	 	 				alert('Send Failed...');
	 				}
	 				$('#message').val('');
					opener.doRefresh();
					dlgclose();
				},
				error: function() {
					$('#send_spinner').hide();
					alert('Send Failed...');
				}, 	 					

				async:   true
			});
		}

		// setup jquery exit check
		$(document).ready(function(){
			// scroll to bottom
			$('#content').animate({
				scrollTop: 0
			});

			// start refresh timer
			refreshTimer = setInterval(ajaxRefresh, 30000);
		});
		
	</script>
</body>

</html>
