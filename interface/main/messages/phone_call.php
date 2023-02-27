<?php
/** *****************************************************************************************
 *	phone_message.php
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

use OpenEMR\Core\Header;

// Determine processing mode
$form_id = trim(strip_tags($_REQUEST['id']));
$form_pid = trim(strip_tags($_REQUEST['pid']));
$form_mode = trim(strip_tags($_REQUEST['mode']));
$form_message = trim(strip_tags($_REQUEST['message']));
$form_topic = trim(strip_tags($_REQUEST['topic']));
$form_status = trim(strip_tags($_REQUEST['status']));
$form_date = trim(strip_tags($_REQUEST['date']));
$form_time = trim(strip_tags($_REQUEST['time']));
$form_userid = trim(strip_tags($_REQUEST['userid']));
$form_direction = trim(strip_tags($_REQUEST['direction']));

// Option lists
$subject_list = new wmt\Options('Portal_Subject');

// Retrieve user record
if (empty($form_userid)) $form_userid = $_SESSION['authUserID'];
$user = sqlQueryNoLog("SELECT `username`, CONCAT(LEFT(`fname`,1), '. ',`lname`) AS 'name' FROM `users` WHERE `id` = ?", array($form_userid));

// Validate Patient
$pat_name = '';
$pat_data = false;
$msg_data = false;

if (isset($form_id) && !empty($form_id) && $form_id != 'new') {
	$sql = "SELECT ml.*, ";
	$sql .= "IFNULL(ml.`delivered_status`, ml.`msg_status`) AS msg_status, ml.`direction`, ";
	$sql .= "IFNULL(ml.`delivered_time`, ml.`msg_time`) AS msg_time, ";
	$sql .= "CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name' ";
	$sql .= "FROM `message_log` ml ";
	$sql .= "LEFT JOIN `users` us ON ml.`userid` = us.`id` ";
	$sql .= "WHERE ml.`id` = ?";
	
	// Retrieve single message
	$msg_data = sqlQueryNoLog($sql, array($form_id));
	
	// Format date/time stamp
	$msg_time = 'UNKNOWN';
	if (strtotime($msg_data['msg_time']) !== false) {
		$msg_time = date('h:i A - M d, Y', strtotime($msg_data['msg_time']));
	}
	
	// Retrieve or create patient
	if (empty($msg_data['pid'])) {
		$pid = null;
		$pat_data = new wmt\Patient();
		$pat_name = '- Select Patient -';
	} else {
		$pid = $msg_data['pid'];
		$pat_data = wmt\Patient::getPidPatient($pid);
		$pat_name = $pat_data->format_name;
	}
	
} else {

	if ($form_id == 'new') {
		
		// New message patient unknown
		$pid = null;
		$form_id = '';
		$pat_data = new wmt\Patient();
		$pat_name = '- Select Patient -';
		
	} else {
		
		// New message patient known
		if (isset($form_pid) && !empty($form_pid)) $pid = $form_pid;
		if (empty($pid)) $pid = $_SESSION['pid'];
		
		// Retrieve patient
		$pat_data = wmt\Patient::getPidPatient($pid);
		$pat_name = $pat_data->format_name;
	}
	
}

// Figure out TO / FROM
$to = $from = '';
if ($form_direction == 'in') {
	$from = $pat_name;
	$to = (empty($user['name']))? 'CALL CENTER' : $user['name'];
} else {
	$to = $pat_name;
	$from = (empty($user['name']))? 'CALL CENTER' : $user['name'];
}
	
// Retrieve users
$query = "SELECT id, username, CONCAT(LEFT(`fname`,1), '. ',`lname`) AS 'name' FROM `users` ";
$query .= "WHERE `username` IS NOT NULL AND `username` != '' AND `active` = 1 ";
$query .= "ORDER BY `lname`";
$user_res = sqlStatementNoLog($query);

// Create user list
$user_list = array();
while ($user = sqlFetchArray($user_res)) $user_list[$user['id']] = $user['name'];

// Previous content
if ($msg_data['id']) {
	$msg_time = date('Y-m-d h:i a', strtotime($msg_data['msg_time']));
	$content = "Call From: " . text( $from ) . "\n";
	$content .= "Call To: " . text( $to ) . "\n";
	$content .= "Date/Time: " . text( $msg_time ) . "\n";
	$content .= "Call Topic: " . $subject_list->getItem($msg_data['event']) . "\n";
	$content .= text($msg_data['message']);
}

// Ajax transmit new message
if ($form_mode == 'record') {

	// Do processing
	$new_status = '';
	
	// Check for message
	if (!empty($form_message)) {
		
		// Get date
		$datetime = strtotime($form_date .' '. $form_time);
		if ($datetime === false) $datetime = strtotime('now');
		$msg_date = date('Y-m-d H:i:s', $datetime);
		
		// Create a new record
		if (empty($msg_data['id'])) {

			$newMsgId = strtotime('now');
			$msgConvId = $newMsgId;

			// Create log entry
			$binds = array();
			$binds[] = (empty($form_topic))? 'PHONE_CALL' : $form_topic;
			$binds[] = 'SFA Call Center';
			$binds[] = $form_direction;
			$binds[] = $form_status;
			$binds[] = $_SESSION['authUserID'];
			$binds[] = (empty($pid)) ? $_SESSION['pid'] : $pid;
			$binds[] = (empty($form_userid))? $_SESSION['authUserID'] : $form_userid;
			$binds[] = ($form_direction == 'in')? 'CLINIC' : 'PATIENT';
			$binds[] = ($form_direction == 'out')? 'CLINIC' : 'PATIENT';
			$binds[] = $msgConvId; // message id of original message
			$binds[] = $newMsgId; // message id of current message
			$binds[] = $msg_date;
			$binds[] = 'CALL_LOGGED';
			$binds[] = $form_message;
		
			// Store log record
			$sql = "INSERT INTO `message_log` SET ";
			$sql .= "`type`='PHONE', `event`=?, `gateway`=?, `direction`=?, `activity`=?, `userid`=?, `pid`=?, `assigned`=?, `msg_to`=?, `msg_from`=?, `msg_convid`=?, `msg_newid`=?, `msg_time`=?, `msg_status`=?, `message`=?";
			$form_id = sqlInsert($sql, $binds);
			
			// Store content
			$new_content = $form_message;

		} else {
			
			// Append old content to new message
			$form_message .= "\n\n---------------------------------------------------------\n";
			$form_message .= $content;
			
			$topic = (empty($subject_list->getItem($form_topic)))? 'Phone Call' : $subject_list->getItem($form_topic);
			$new_content = "Call From: ";
			$new_content .= ($form_direction == 'in')? $pat_name : $user_list[$form_userid]; 
			$new_content .= "\nCall To: ";
			$new_content .= ($form_direction == 'out')? $pat_name : $user_list[$form_userid]; 
			$new_content .= "\nDate/Time: $msg_date\n";
			$new_content .= "Call Topic: $topic\n\n";
			$new_content .= $form_message;
			
			// Update existing record
			$binds = array();
			$binds[] = (empty($form_topic))? 'PHONE_CALL' : $form_topic;
			$binds[] = $form_direction;
			$binds[] = $form_status;
			$binds[] = $_SESSION['authUserID'];
			$binds[] = (empty($form_userid))? $_SESSION['authUserID'] : $form_userid;
			$binds[] = ($form_direction == 'in')? 'CLINIC' : 'PATIENT';
			$binds[] = ($form_direction == 'out')? 'CLINIC' : 'PATIENT';
			$binds[] = $msg_date;
			$binds[] = 'CALL_LOGGED';
			$binds[] = $form_message;
			$binds[] = $msg_data['id'];
			
			// Write new record
			$sql = "UPDATE `message_log` SET ";
			$sql .= "`event`=?, `direction`=?, `activity`=?, `userid`=?, `assigned`=?, `msg_to`=?, `msg_from`=?, `msg_time`=?, `msg_status`=?, `message`=? ";
			$sql .= "WHERE `id` = ?";
			
			sqlStatement($sql, $binds);
			
			$form_id = $msg_data['id'];
		}
	
		// Return new messages
		echo json_encode(array('status'=>$new_status, 'id'=>$form_id, 'content'=>$new_content));

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

	<title>Call Log Manager</title>
	<meta name="author" content="Ron Criswell" />
	<meta name="description" content="Call Log Manager" />
	<meta name="copyright" content="&copy;<?php echo date('Y') ?> Williams Medical Technologies, Inc.  All rights reserved." />

	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<?php Header::setupHeader(['main-theme', 'opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'bootstrap']); ?>

	<link rel="shortcut icon" href="images/favicon.ico" />

	<!-- script type="text/javascript">
    var webroot_url = '<?php echo $GLOBALS['web_root']; ?>';
  </script  -->

	
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
		height: 77%;
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
		<table class="table-condensed">
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('Patient'); ?>:&nbsp;</b>
				</td>
				<td>
					<input id="pat_name" name="pat_name" class='form-control' type='text' value='<?php echo $pat_name ?>' readonly 
					<?php if (empty($pid)) echo "onclick='findPatient()' "; ?>
					/>
				</td>
				<td style='text-align:right'>
					<b>PID:&nbsp;</b>
				</td>
				<td colspan='3'>
					<input type='text' id='pid' name='pid' class='form-control' value='<?php echo $pid ?>' style='background-color:#eee;width:60px' readonly />					
				</td>
			</tr>
			<tr>
				<td style='text-align:right;min-width:60px'>
					<b><?php echo xlt('Direction'); ?>:&nbsp;</b>
				</td>
				<td>
					<select id="direction" name="direction" class="form-control">
						<option value='in'><?php echo xlt('Call Received by Clinic'); ?></option>
						<option value='out'><?php echo xlt('Initiated Call to Patient'); ?></option>
					</select>
				</td>
				<td style='text-align:right;padding-left:20px'>
					<b><?php echo xlt('Date'); ?>:&nbsp;</b>
				</td>
				<td>
					<input type='date' id='date' name='date' class='form-control' style="font-family:Arial" value='<?php echo date('Y-m-d') ?>' required />
				</td>
				<td style='text-align:right;padding-left:40px'>
					<b><?php echo xlt('Time'); ?>:&nbsp;</b>
				</td>
				<td>
					<select id='time' name='time' class='form-control'>
						<option value='Xam' <?php if (date('H') < '7') echo 'selected' ?>>Early Morning</option>
						<option value='7am' <?php if (date('H') == '7') echo 'selected' ?>>7:00 am</option>
						<option value='8am' <?php if (date('H') == '8') echo 'selected' ?>>8:00 am</option>
						<option value='9am' <?php if (date('H') == '9') echo 'selected' ?>>9:00 am</option>
						<option value='10am' <?php if (date('H') == '10') echo 'selected' ?>>10:00 am</option>
						<option value='11am' <?php if (date('H') == '11') echo 'selected' ?>>11:00 am</option>
						<option value='12pm' <?php if (date('H') == '12') echo 'selected' ?>>12:00 pm</option>
						<option value='1pm' <?php if (date('H') == '13') echo 'selected' ?>>1:00 pm</option>
						<option value='2pm' <?php if (date('H') == '14') echo 'selected' ?>>2:00 pm</option>
						<option value='3pm' <?php if (date('H') == '15') echo 'selected' ?>>3:00 pm</option>
						<option value='4pm' <?php if (date('H') == '16') echo 'selected' ?>>4:00 pm</option>
						<option value='5pm' <?php if (date('H') == '17') echo 'selected' ?>>5:00 pm</option>
						<option value='6pm' <?php if (date('H') == '18') echo 'selected' ?>>6:00 pm</option>
						<option value='7pm' <?php if (date('H') == '19') echo 'selected' ?>>7:00 pm</option>
						<option value='Xpm' <?php if (date('H') > '19') echo 'selected' ?>>Late Evening</option>
				</td>
			</tr>			
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('Assignment'); ?>:&nbsp;</b>
				</td>
				<td>
					<select id='userid' name='userid' required class='form-control'>
<?php 
foreach ($user_list AS $key => $name) {
	echo "<option value='$key'";
	if ($key == $_SESSION['authUserID']) echo " selected ";
	echo ">$name</option>\n";
}
?>
					</select>
				</td>
				<td style='text-align:right;min-width:60px'>
					<b><?php echo xlt('Status'); ?>:&nbsp;</b>
				</td>
				<td colspan='3'>
					<select id='status' name="status" class="form-control">
						<option value='1'><?php echo xlt('Active (Needs Follow Up)'); ?></option>
						<option value='0'><?php echo xlt('Closed (Issue Resolved)'); ?></option>
					</select>
				</td>
			</tr>			
			<tr>
				<td style='text-align:right;min-width:60px'>
					<b><?php echo xlt('Call Topic'); ?>:&nbsp;</b>
				</td>
				<td colspan='5'>
					<select id='topic' name="topic" required class="form-control">
						<?php $subject_list->showOptions($msg_data['event'], '-- Select Topic --') ?>
					</select>
				</td>
			</tr>
		</table>
	</header>

	<main>
		<textarea id="content" name="content" readonly ><?php echo $content ?></textarea>
	</main>

	<div id="send_spinner" class="notification" style="position:absolute;color:white;font-weight:bold;padding:20px;border-radius:10px;background-color:red;left:45%;top:40%;z-index:850;display:none;">
			Processing...
		</div>
					
	<footer>
		<form>
			<input type="hidden" id="mode" name="mode" value="" />
			<input type="hidden" id="event" name="event" value="<?php echo $form_event ?>" />
			<input type="hidden" id="id" name="id" value="<?php echo $form_id ?>" />

			<textarea id='message' name='message' style="width:70%;margin:10px;padding:10px;border:none;border-radius:5px;resize:vertical;background-color:#eee;vertical-align:middle;"></textarea>
			<div style="float:right;width:20%;text-align:right;margin-right:20px">
				<input id="send_phone" type="button" onclick="ajaxRecord('phone')" style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xla('APPEND NOTES'); ?>">
				<!-- br>
				<input id="store_note" type="button" onclick="ajaxRecord('NOTE')" style="margin:5px 0 10px;padding:5px 12px;" value="PRIVATE NOTE" -->
				<br>
				<input id="send_phone" type="button" onclick="ajaxRecord('phone', true)" style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xla('APPEND & CLOSE'); ?>">
<?php if (isset($form_id) && !empty($form_id) && $form_id != 'new') { ?>
				<br>
				<input id="cancel" type="button" onclick="window.location.assign('../../patient_file/summary/messages_full.php?mode=phone_update'); " style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xla('CANCEL'); ?>">
<?php } ?>
			</div>
		</form>
	</footer>
	
	<script>
		var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

		<?php include_once($GLOBALS['srcdir']."/restoreSession.php"); ?>
	
		<?php include_once($GLOBALS['srcdir']."/wmt-v2/ajax/init_ajax.inc.js"); ?> 
		
 		// background update process
		function ajaxRecord(type) {
			var close_this = false;
			if(arguments.length > 1) close_this = arguments[1];

			// validate patient
			if ($('#pid').val() == '') {
				alert ("Please select a patient before proceeding...");
				return false;
			}
			
			// validate topic
			if ($('#topic').val() == null) {
				alert ("Please select a topic before proceeding...");
				return false;
			}
			
			// validate message
			if ($('#message').val() == '') {
				alert ("Please enter content before proceeding...");
				return false;
			}
			
			top.restoreSession();

			// show spinner
			$('#send_spinner').show();
			
			// organize the data
			var data = [];
			data.push({name: "mode", value: 'record'});
			data.push({name: "direction", value: $('#direction').val()});
			data.push({name: "status", value: $('#status').val()});
			data.push({name: "topic", value: $('#topic').val()});
			data.push({name: "pid", value: $('#pid').val()});
			data.push({name: "userid", value: $('#userid').val()});
			data.push({name: "date", value: $('#date').val()});
			data.push({name: "time", value: $('#time').val()});
			data.push({name: "id", value: $('#id').val()});
			data.push({name: "message", value: $('#message').val()});

 			$.ajax ({
				type: "POST",
				url: "phone_call.php",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
					$('#send_spinner').hide();
		 			if (result.status == '') {
 	 					$('#id').val(result.id);
 	 					$('#content').val(result.content);
//						$('#content').animate({
//							scrollTop: 0
//						});

						opener.doRefresh();
	 				} else {
	 	 				alert('Send Failed...');
	 				}
	 				$('#message').val('');
				},
				error: function() {
					$('#send_spinner').hide();
					alert('Send Failed...');
				}, 	 					

				async:   false
			});

<?php if (isset($form_id) && !empty($form_id) && $form_id != 'new') { ?>
			if(close_this) window.location.assign('../../patient_file/summary/messages_full.php?mode=phone_update');
<?php } else { ?>
			if(close_this) dlgclose();
<?php } ?>

		}
		
		// This invokes the find-patient popup.
		function findPatient() {
			dlgopen('../calendar/find_patient_popup.php', '_blank', 500, 400);
		}

		// This is for callback by the find-patient popup.
		function setpatient(pid, lname, fname, dob) {
			$('#pid').val(pid);
			$('#pat_name').val(fname +' '+ lname);
		}
					
		// setup jquery exit check
		$(document).ready(function(){
			// scroll to bottom
			$('#content').animate({
				scrollTop: 0
			});

		});
		
	</script>
</body>

</html>
