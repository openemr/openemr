<?php

require_once("../../../globals.php");
require_once ($GLOBALS['srcdir'] . "/wmt-v3/wmt.globals.php");

// Include required globals
require_once ($GLOBALS['srcdir'] . '/patient.inc');
require_once ($GLOBALS['srcdir'] . '/options.inc.php');
require_once ($GLOBALS['srcdir'] . '/classes/Document.class.php');
require_once ($GLOBALS['srcdir'] . '/gprelations.inc.php');
require_once ($GLOBALS['srcdir'] . '/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$form_set_id = isset($_REQUEST['set_id']) ? $_REQUEST['set_id'] : '';

$pres = getPatientData($pid, "lname, fname");
$patientname = $pres['lname'] . ", " . $pres['fname'];

if(!empty($mode)) {
	$noteid = isset($_REQUEST['noteid']) ? $_REQUEST['noteid'] : '';
	$form_set_mpid = $_REQUEST['set_mpid'];
	$form_set_note_type = $_REQUEST['set_note_type'];
	$form_set_uid = $_REQUEST['set_uid'];
	$form_set_group = $_REQUEST['set_group'];
	$form_set_username = $_REQUEST['set_username'];
}

$page_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if(!empty($page_action)) {
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value

	$filterVal = isset($_POST['filterVal']) ? $_POST['filterVal'] : array(); // Filter value
	$colList = isset($_POST['columnList']) ? $_POST['columnList'] : array(); // Column List value
}


function generateNoteAssignEle($type,$row) {
	if (!empty($row['pid'])) { 
    if (empty($row['assigned_to']) && (AclMain::aclCheckCore('admin', 'super', '', 'write') || AclMain::aclCheckCore('patients', 'assignmsg', '', 'write'))) { 
    ?> 
		<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
			id="<?php echo "doassigninput".$row['id'] ?>"
			title='<?php echo htmlspecialchars(xl($type), ENT_QUOTES) ?>' onClick="doAssignAjax('assign','<?php echo $type; ?>','<?php echo $row['id']; ?>', '<?php echo $row['pid'] ?>')">
				<span><?php echo htmlspecialchars(xl('Assign'), ENT_NOQUOTES) ?></span>
		</a> 
	<?php 
	} else if((AclMain::aclCheckCore('admin', 'super', '', 'write') || AclMain::aclCheckCore('patients', 'unassignmsg', '', 'write'))) { 
	?> 
		<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
			id="<?php echo "doassigninput".$row['id'] ?>"
			title='<?php echo htmlspecialchars(xl($type), ENT_QUOTES) ?>' onClick="doAssignAjax('release','<?php echo $type; ?>','<?php echo $row['id']; ?>', '<?php echo $row['pid'] ?>')">
				<span><?php echo htmlspecialchars(xl('Unassign'), ENT_NOQUOTES) ?></span>
		</a>
	<?php
	}
	}
}

function generateAssignEle($type,$row) {
	if (!empty($row['pid'])) { 
    if (empty($row['assigned']) && empty($row['assign_group']) && ($row['direction'] == "in" || ($row['direction'] == "out" && $row['activity'] == "1")) && (AclMain::aclCheckCore('admin', 'super', '', 'write') || AclMain::aclCheckCore('patients', 'assignmsg', '', 'write'))) { 
    ?> 
		<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
			id="<?php echo "doassigninput".$row['id'] ?>"
			title='<?php echo htmlspecialchars(xl($type), ENT_QUOTES) ?>' onClick="doAssignAjax('assign','<?php echo $type; ?>','<?php echo $row['id']; ?>', '<?php echo $row['pid'] ?>')">
				<span><?php echo htmlspecialchars(xl('Assign'), ENT_NOQUOTES) ?></span>
		</a> 
	<?php 
	} else if(($row['direction'] == "in" || ($row['direction'] == "out" && $row['activity'] == "1") ) && (AclMain::aclCheckCore('admin', 'super', '', 'write') || AclMain::aclCheckCore('patients', 'unassignmsg', '', 'write'))) { 
	?> 
		<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
			id="<?php echo "doassigninput".$row['id'] ?>"
			title='<?php echo htmlspecialchars(xl($type), ENT_QUOTES) ?>' onClick="doAssignAjax('release','<?php echo $type; ?>','<?php echo $row['id']; ?>', '<?php echo $row['pid'] ?>')">
				<span><?php echo htmlspecialchars(xl('Unassign'), ENT_NOQUOTES) ?></span>
		</a>
	<?php
	}
	}
}

function checkMessageIsFailed($type, $data) {
	$isFailed = false;

	// if($type == 'email') {
	// 	$isFailed = EmailMessage::isFailedToSend($data);
	// } else if($type == 'sms') {
	// 	$isFailed = EmailMessage::isFailedToSMSSend($data);
	// } else if($type == 'fax') {
	// 	$isFailed = FaxMessage::isFailedToSend($data);
	// } else if($type == 'postal_letter') {
	// 	$isFailed = PostalLetter::isFailedToSend($data);
	// }

	if($data['direction'] == "out") {
		$isFailed = true;		
	}

	if($isFailed === true) {
		echo generateResend($type, $data);
	}
}

function generateResend($type, $row) {
	?>
	<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
		id="<?php echo "replyinput".$row['id'] ?>"
		title='<?php echo htmlspecialchars(xl($type), ENT_QUOTES) ?>' onClick="resendButton(event,'<?php echo $type; ?>','<?php echo $row['id']; ?>')">
			<span><?php echo htmlspecialchars(xl('Resend'), ENT_NOQUOTES) ?></span>
	</a>
	<?php
}

function getNoteTypeGroup($row) {
	$notelist_options = sqlQueryNoLog( "SELECT * FROM list_options WHERE (? = list_options.`option_id` AND list_options.`list_id` = 'Messaging_Groups') ", array($row['assign_group']));

	if(!empty($notelist_options['title'])) {
		return $notelist_options['title'];
	}

	return '';
}

function getassigned($type, $row) {
	if (!empty($row['assigned']) || $row['assigned'] != 0) {
		return $row['assign_user_name'] ." ";
	} else if ($row['assign_group']) {
		return "<span>" . getNoteTypeGroup($row) . "</span>";
	} else {
		return "<span style='color:red'>Unassigned</span>";
	}
}

function getInternalMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal) {
	global $patientname;

	## Search 
	$searchQuery = array();
	if($searchValue != ''){
	   $searchQuery = array();
	}

	sleep(5);

	if(!empty($filterVal)) {
		if(isset($filterVal['active']) && $filterVal['active'] == "1") $searchQuery[] = "p.message_status != 'Done'";
		if(isset($filterVal['active']) && $filterVal['active'] == "0") $searchQuery[] = "p.message_status = 'Done'";
	}

	$searchQuery = implode(" AND ", $searchQuery);
	if(!empty($searchQuery)) $searchQuery = " AND " . $searchQuery;

	if(empty($columnSortOrder)) $columnSortOrder = 'desc';

	$ordercolumnName = "p.date";
	if($columnName == "active") $ordercolumnName = 'FIELD(p.message_status, "Done")';
	if($columnName == "date_time") $ordercolumnName = 'p.date';

	## Total number of records without filtering
	$records = sqlQuery("SELECT count(p.id) allcount FROM pnotes AS p where p.pid = ? and p.deleted != 1", array($pid));
	$totalRecords = $records['allcount'];

	## Total number of record with filtering
	$records = sqlQuery("SELECT count(p.id) allcount FROM pnotes AS p where p.pid = ? and p.deleted != 1 " .$searchQuery, array($pid));
	$totalRecordwithFilter = $records['allcount'];

	## Fetch records
	$msgQuery = "SELECT id, date, body as content, user, activity, title, assigned_to, message_status, pid FROM pnotes AS p where p.pid = ? and p.deleted != 1 ".$searchQuery." order by ".$ordercolumnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

	$msgRecords = sqlStatement($msgQuery, array($pid));
	$data = array();

	while ($note_data = sqlFetchArray($msgRecords)) {
		$row_note_id = $note_data['id'];

		$msg_date = $note_data['date'];
		$datetime = strtotime($note_data['date']);
		if ($datetime) $msg_date = date('Y-m-d h:ia', $datetime);

		$body = $note_data['content'];
		if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
			$body = nl2br(oeFormatPatientNote($body));
		} else {
			$body = htmlspecialchars(oeFormatSDFT(strtotime($note_data['date'])) . date(' H:i', strtotime($note_data['date'])), ENT_NOQUOTES) . ' (' . htmlspecialchars($note_data['user'], ENT_NOQUOTES) . ') ' .'<br>'. nl2br(oeFormatPatientNote($body));
		}
		$body = preg_replace('/(\sto\s)-patient-(\))/', '${1}' . $patientname . '${2}', $body);

		if (($note_data["activity"]) && ($note_data['message_status'] != "Done")) {
			$checked = "checked";
		} else {
			$checked = "";
		}

		// get assigned_to name
		$user = sqlQueryNoLog( "SELECT CONCAT(LEFT(`fname`,1), '. ',`lname`) AS 'name' FROM `users` WHERE `username` = ? ", array($note_data['assigned_to']) );

		$note_name = isset($user['name']) ? $user['name'] : "";

		if(empty($note_name)) {
			$notelist_options = sqlQueryNoLog( " SELECT * FROM list_options WHERE (SUBSTRING(?,5) = list_options.`option_id` AND list_options.`list_id` = 'Messaging_Groups') ", array($note_data['assigned_to']));
			if(!empty($notelist_options['title'])) {
				$note_name = $notelist_options['title'];
			}
		}

		$nType = generate_display_field (
				array (
					'data_type' => '1',
					'list_id' => 'note_type' 
				), 
				$note_data['title']
			);

		ob_start();
		?>
		<div class="btn-group" role="group" aria-label="Basic example">
			<a href='<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/internal_note.php?mode=edit&noteid=<?php echo $row_note_id ?>&tabmode=true' class='btn btn-secondary btn-sm notes_link' 
				title='<?php echo htmlspecialchars(xl('Internal Notes'), ENT_QUOTES) ?>'>
					<span><?php echo htmlspecialchars(xl('Edit'), ENT_NOQUOTES) ?></span>
			</a>
			<?php 
			// if the user is an admin or if they are the author of the note, they can delete it
			if (AclMain::aclCheckCore('admin', 'delete_internal_notes', '', 'write') || AclMain::aclCheckCore('admin', 'super', '', 'write')) {
			?>
			<a href='#' class='btn btn-secondary btn-sm notes_link' onclick="doDeleteAjax('<?php echo $row_note_id ?>')" 
				title='<?php echo htmlspecialchars(xl('Delete this note'), ENT_QUOTES) ?>'>
					<span><?php echo htmlspecialchars(xl('Delete'), ENT_NOQUOTES) ?></span>
			</a>
			<?php } ?>
			<?php if(!empty($note_data['pid'])) { ?>
			<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
				id="<?php echo "unlinkinput".$note_data['id'] ?>"
				title='<?php echo htmlspecialchars(xl('Notes'), ENT_QUOTES) ?>' onClick="unlinkButtonAjax('notes','<?php echo $note_data['id']; ?>')">
					<span><?php echo htmlspecialchars(xl('Unlink'), ENT_NOQUOTES) ?></span>
			</a>	
			<?php } ?>
			<?php echo generateNoteAssignEle('notes', $note_data); ?>

		</div>
		<?php
		$actionhtml = ob_get_clean();

		$formatedMessage = MessagesLib::displayIframeMsg($body, 'text');
		ob_start();
		?>
		<div class="iframe_message_content">
		<iframe scrolling="no" data-id="<?php echo $note_data['id']; ?>" class="contentiFrame" srcdoc="<?php echo htmlentities($formatedMessage) ?>"></iframe>
		</div>
		<?php
		$bodyContent = ob_get_clean();

		$data[] = array(
			'id' => $row_note_id,
			'dt_control' => '',
			'action' => $actionhtml,
			'active' => $checked,
			'date_time' => $msg_date,
			'assigned' => $note_name,
			'type' => '<b>'. $nType .'</b>',
			'content' => $bodyContent ,
			'activity' => $note_data["activity"],
			'activity' => $note_data['message_status']
		);
		//$data[] = $row_item;
	}

	//Active Notes count
	$activenotes = getPnotesByDate("", '1', 'id,date,body,user,activity,title,assigned_to,message_status', $pid, 'all', '', '', 0, '', 0);

	//Inactive Notes count
	$inActivenotes = getPnotesByDate("", '0', 'id,date,body,user,activity,title,assigned_to,message_status', $pid, 'all', '', '', 0, '', 0);

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data,
	  "otherData" => array(
	  	"activeCount" => count($activenotes),
	  	"inActiveCount" => count($inActivenotes)
	  )
	);

	return $response;
}

function getEmailMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal) {
	## Search 
	$searchQuery = array();
	if($searchValue != ''){
	   $searchQuery = array();
	}

	if(!empty($filterVal)) {
		if(isset($filterVal['active']) && $filterVal['active'] == "1") $searchQuery[] = "ml.activity = '1'";
		if(isset($filterVal['active']) && $filterVal['active'] == "0") $searchQuery[] = "ml.activity != '1'";
	}

	$searchQuery = implode(" AND ", $searchQuery);
	if(!empty($searchQuery)) $searchQuery = " AND " . $searchQuery;

	if(empty($columnSortOrder)) $columnSortOrder = 'desc';

	$ordercolumnName = "ml.`msg_time`";
	if($columnName == "active") $ordercolumnName = 'ml.activity';
	if($columnName == "date_time") $ordercolumnName = 'ml.`msg_time`';

	## Total number of records without filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `patient_data` pd ON ml.`pid` = pd.`id` WHERE ml.`pid` = ? and ml.type = 'EMAIL'", array($pid));
	$totalRecords = $records['allcount'];

	## Total number of record with filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `patient_data` pd ON ml.`pid` = pd.`id` WHERE ml.`pid` = ? and ml.type = 'EMAIL' " .$searchQuery, array($pid));
	$totalRecordwithFilter = $records['allcount'];

	## Fetch records
	$msgQuery = "SELECT ml.*, CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name', CONCAT(LEFT(pd.`fname`,1), '. ',pd.`lname`) AS 'patient_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' , (SELECT COUNT(*) FROM message_log mlp WHERE mlp.`direction` = 'in' AND mlp.`activity` = '1' AND mlp.`msg_from` = ml.`msg_from` AND NOT mlp.`id` = ml.`id`) as count_similer_active_records, raw_data FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `patient_data` pd ON ml.`pid` = pd.`id` WHERE ml.`pid` = ? and ml.type = 'EMAIL' ".$searchQuery." order by ".$ordercolumnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

	$msgRecords = sqlStatement($msgQuery, array($pid));
	$data = array();

	while ($email_data = sqlFetchArray($msgRecords)) {

		$row_email_id = $email_data['id'];

		$checked = ($email_data['activity'] == '1') ? "checked" : "";
		//$email_checked = in_array($row_email_id, $form_selected_email_obj) ? "checked" : "";
		$email_checked = "";
		
		$user_name = $email_data['user_name'];
		if (empty($email_data['userid']) && $email_data['msg_from'] == 'PATIENT') $user_name = '[&nbsp;PATIENT&nbsp;]';
		if (empty($user_name) && $email_data['msg_from'] == 'CLINIC') $user_name = '[&nbsp;SYSTEM&nbsp;]';
		
		$msg_date = $email_data['msg_time'];
		$datetime = strtotime($email_data['msg_time']);

		/*Email Direction & username*/
		$emailDirection = '';
		$u_name = $user_name;
		if(isset($email_data['direction']) && $email_data['direction'] == "out") {
			$u_name = $email_data['user_name'];
			$emailDirection = 'Outbound';
		} else if(isset($email_data['direction']) && $email_data['direction'] == "in") {
			$emailDirection = 'Inbound';
			$u_name = $email_data['patient_name'];
		}

		$toFrom = "";
		if($email_data['direction'] == 'in') {
			$receiverName = isset($email_data['receivers_name']) && !empty($email_data['receivers_name']) ? $email_data['receivers_name'] : '';
			$toFrom = $receiverName . ' (' . $email_data['msg_from'] . ') ';
		} else if($email_data['direction'] == 'out'){
			$toFrom = $email_data['msg_to'];
		}

		if ($datetime) $msg_date = date('Y-m-d h:ia', $datetime);

		ob_start();
		?>
		<div class="btn-group" role="group" aria-label="Basic example">
			<?php //if($emailDirection == "Inbound") { ?>
			<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
				data-count="<?php echo $email_data['count_similer_active_records'] ?>"
				id="<?php echo "replyinput".$email_data['id'] ?>"
				title='<?php echo htmlspecialchars(xl('Email Message'), ENT_QUOTES) ?>' onClick="replyEmailButton(event,'<?php echo $email_data['id']; ?>')">
					<span><?php echo htmlspecialchars(xl('Reply'), ENT_NOQUOTES) ?></span>
			</a>
			<?php //} ?>
			<?php if(!empty($email_data['pid'])) { ?>
			<a href="javascript:;" class='btn btn-secondary btn-sm' 
				id="<?php echo "unlinkinput".$email_data['id'] ?>"
				title='<?php echo htmlspecialchars(xl('Email Message'), ENT_QUOTES) ?>' onClick="unlinkButtonAjax('email','<?php echo $email_data['id']; ?>')">
					<span><?php echo htmlspecialchars(xl('Unlink'), ENT_NOQUOTES) ?></span>
			</a>	
			<?php } ?>
			<?php echo generateAssignEle('email', $email_data); ?>
			<?php echo checkMessageIsFailed('email', $email_data); ?>

		</div>
		<?php
		$actionhtml = ob_get_clean();

		// Message Content
		$message = isset($email_data['message'])  ? $email_data['message'] : "";
		$formatedMessage = MessagesLib::displayIframeMsg($message, 'text');

		ob_start();
		?>
		<?php //MessagesLib::filterMsg($email_data); ?>
		<iframe scrolling="no" data-id="<?php echo $email_data['id']; ?>" class="contentiFrame" srcdoc="<?php echo htmlentities($formatedMessage) ?>"></iframe>
		<?php echo MessagesLib::displayAttachment($email_data['type'], $email_data['id'], $email_data); ?>
		<?php
		$bodyContent = ob_get_clean();

		ob_start();
		?>
		<?php echo $toFrom; ?>
		<?php 
			// if($email_data['type'] == "EMAIL" && !empty($email_data['message_subject'])) {
			// 	echo isset($email_data['message_subject']) && !empty($email_data['message_subject']) ? "<br/><br/>Subject: ".$email_data['message_subject'] . "<br/>" : "";
			// }
		?>
		<?php
		$toFromhtml = ob_get_clean();

		$raw_data = !empty($email_data['raw_data'])  ? json_decode($email_data['raw_data'], true) : array();
		$email_subject = "";

		if($email_data['type'] == "EMAIL" && !empty($email_data['message_subject'])) {
			$email_subject = isset($email_data['message_subject']) && !empty($email_data['message_subject']) ? $email_data['message_subject'] : "";
		} else if($email_data['type'] == "EMAIL" && !empty($raw_data['subject'])) {
			$email_subject = isset($raw_data['subject']) && !empty($raw_data['subject']) ? $raw_data['subject'] : "";
		}

		$data[] = array(
			'id' => $row_email_id,
			'dt_control' => '',
			'select' => $email_checked,
			'action' => $actionhtml,
			'active' => $checked,
			'date_time' => $msg_date,
			'assignment' => getassigned('email', $email_data),
			'direction' => $emailDirection,
			'author' => $u_name,
			'to_from' => $toFromhtml,
			'status' => $email_data['msg_status'],
			'content' => $bodyContent,
			'subject' => $email_subject
		);
	}

	//Count Active/Inactive Emails
	$emailCount = sqlQuery("SELECT SUM(CASE WHEN activity = 1 THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN activity = 0 THEN 1 ELSE 0 END) AS inactive FROM `message_log` ml WHERE ml.`pid` = ? AND ml.`type` LIKE 'EMAIL' " , array($pid));

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data,
	  "otherData" => array(
	  	"activeCount" => $emailCount['active'],
	  	"inActiveCount" => $emailCount['inactive']
	  )
	);

	return $response;
}

function getSMSMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal) {
	## Search 
	$searchQuery = array();
	if($searchValue != ''){
	   $searchQuery = array();
	}

	if(!empty($filterVal)) {
		if(isset($filterVal['active']) && $filterVal['active'] == "1") $searchQuery[] = "ml.activity = '1'";
		if(isset($filterVal['active']) && $filterVal['active'] == "0") $searchQuery[] = "ml.activity != '1'";
	}

	$searchQuery = implode(" AND ", $searchQuery);
	if(!empty($searchQuery)) $searchQuery = " AND " . $searchQuery;

	if(empty($columnSortOrder)) $columnSortOrder = 'desc';

	$ordercolumnName = "ml.`msg_time`";
	if($columnName == "active") $ordercolumnName = 'ml.activity';
	if($columnName == "date_time") $ordercolumnName = 'ml.`msg_time`';

	## Total number of records without filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` WHERE ml.`pid` = ? and ml.type = 'SMS'", array($pid));
	$totalRecords = $records['allcount'];

	## Total number of record with filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` WHERE ml.`pid` = ? and ml.type = 'SMS' " .$searchQuery, array($pid));
	$totalRecordwithFilter = $records['allcount'];

	## Fetch records
	$msgQuery = "SELECT ml.*, CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' , (SELECT COUNT(*) FROM message_log mlp WHERE mlp.`direction` = 'in' AND mlp.`activity` = '1' AND mlp.`msg_from` = ml.`msg_from` AND NOT mlp.`id` = ml.`id`) as count_similer_active_records FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` WHERE ml.`pid` = ? and ml.type = 'SMS' ".$searchQuery." order by ".$ordercolumnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

	$msgRecords = sqlStatement($msgQuery, array($pid));
	$data = array();

	while ($sms_data = sqlFetchArray($msgRecords)) {

		$row_sms_id = $sms_data['id'];
		//$sms_checked = in_array($row_sms_id, $form_selected_sms_obj) ? "checked" : "";
		$sms_checked = "";
		
		$checked = ($sms_data['activity'] == '1') ? "checked" : "";

		$toFrom = "";
		if($sms_data['direction'] == 'in') {
			$toFrom = $sms_data['msg_from'];
		} else if($sms_data['direction'] == 'out'){
			$toFrom = $sms_data['msg_to'];
		}		
		
		$user_name = $sms_data['user_name'];
		if (empty($sms_data['userid']) && $sms_data['direction'] == 'in') $user_name = '[ PATIENT ]';
		if (empty($user_name) && $sms_data['direction'] == 'out') $user_name = '[ SYSTEM ]';
		
		$msg_date = $sms_data['msg_time'];
		$datetime = strtotime($sms_data['msg_time']);
		if ($datetime) $msg_date = date('Y-m-d h:ia', $datetime);

		ob_start();
		?>
		<div class="btn-group" role="group" aria-label="Basic example">
			<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
				data-count="<?php echo $sms_data['count_similer_active_records'] ?>"
				id="<?php echo "replyinput".$sms_data['id'] ?>"
				title='<?php echo htmlspecialchars(xl('SMS Message'), ENT_QUOTES) ?>' onClick="replySMSButton(event,'<?php echo $sms_data['id']; ?>')">
					<span><?php echo htmlspecialchars(xl('Reply'), ENT_NOQUOTES) ?></span>
			</a>
			<?php if(!empty($sms_data['pid'])) { ?>
			<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
				id="<?php echo "unlinkinput".$sms_data['id'] ?>"
				title='<?php echo htmlspecialchars(xl('SMS Message'), ENT_QUOTES) ?>' onClick="unlinkButtonAjax('sms','<?php echo $sms_data['id']; ?>')">
					<span><?php echo htmlspecialchars(xl('Unlink'), ENT_NOQUOTES) ?></span>
			</a>	
			<?php } ?>
			<?php echo generateAssignEle('sms', $sms_data); ?>
			<?php echo checkMessageIsFailed('sms', $sms_data); ?>

		</div>
		<?php
		$actionhtml = ob_get_clean();

		// Message Content
		$message = isset($sms_data['message'])  ? $sms_data['message'] : "";
		$formatedMessage = MessagesLib::displayIframeMsg($message, 'text');

		ob_start();
		?>
		<iframe scrolling="no" data-id="<?php echo $sms_data['id']; ?>" class="contentiFrame" srcdoc="<?php echo htmlentities($formatedMessage) ?>"></iframe>
		<?php echo MessagesLib::displayAttachment($sms_data['type'], $sms_data['id'], $sms_data); ?>
		<?php
		$bodyContent = ob_get_clean();

		$data[] = array(
			'id' => $row_sms_id,
			'dt_control' => '',
			'select' => $sms_checked,
			'action' => $actionhtml,
			'active' => $checked,
			'date_time' => $msg_date,
			'assignment' => getassigned('sms', $sms_data),
			'author' => $user_name,
			'to_from' => $toFrom,
			'status' => $sms_data['msg_status'],
			'content' => $bodyContent
		);
	}

	
	//Count Active/Inactive Emails
	$smsCount = sqlQuery("SELECT SUM(CASE WHEN activity = 1 THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN activity = 0 THEN 1 ELSE 0 END) AS inactive FROM `message_log` ml WHERE ml.`pid` = ? AND ml.`type` = 'SMS' " , array($pid));

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data,
	  "otherData" => array(
	  	"activeCount" => $smsCount['active'],
	  	"inActiveCount" => $smsCount['inactive']
	  )
	);

	return $response;
}

function getFaxMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal) {
	## Search 
	$searchQuery = array();
	if($searchValue != ''){
	   $searchQuery = array();
	}

	if(!empty($filterVal)) {
		if(isset($filterVal['active']) && $filterVal['active'] == "1") $searchQuery[] = "ml.activity = '1'";
		if(isset($filterVal['active']) && $filterVal['active'] == "0") $searchQuery[] = "ml.activity != '1'";
	}

	$searchQuery = implode(" AND ", $searchQuery);
	if(!empty($searchQuery)) $searchQuery = " AND " . $searchQuery;

	if(empty($columnSortOrder)) $columnSortOrder = 'desc';

	$ordercolumnName = "ml.`msg_time`";
	if($columnName == "active") $ordercolumnName = 'ml.activity';
	if($columnName == "date_time") $ordercolumnName = 'ml.`msg_time`';

	## Total number of records without filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `fax_messages` fm ON ml.`id` = fm.`message_id` WHERE ml.`pid` = ? and ml.type = 'FAX'", array($pid));
	$totalRecords = $records['allcount'];

	## Total number of record with filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `fax_messages` fm ON ml.`id` = fm.`message_id` WHERE ml.`pid` = ? and ml.type = 'FAX' " .$searchQuery, array($pid));
	$totalRecordwithFilter = $records['allcount'];

	## Fetch records
	$msgQuery = "SELECT ml.*, fm.`status_code`, fm.`description`, fm.`file_name`, fm.`url`, fm.`receivers_name`, CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `fax_messages` fm ON ml.`id` = fm.`message_id` WHERE ml.`pid` = ? and ml.type = 'FAX' ".$searchQuery." order by ".$ordercolumnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

	$msgRecords = sqlStatement($msgQuery, array($pid));
	$data = array();

	while ($fax_data = sqlFetchArray($msgRecords)) {
		$row_fax_id = $fax_data['id'];
		//$fax_checked = in_array($row_fax_id, $form_selected_fax_obj) ? "checked" : "";
		$fax_checked = "";
		
		$checked = ($fax_data['activity'] == '1') ? "checked" : "";
		
		$user_name = $fax_data['user_name'];
		if (empty($fax_data['userid']) && $fax_data['direction'] == 'in') $user_name = '[ PATIENT ]';
		if (empty($user_name) && $fax_data['direction'] == 'out') $user_name = '[ SYSTEM ]';
		
		$msg_date = $fax_data['msg_time'];
		$datetime = strtotime($fax_data['msg_time']);
		if ($datetime) $msg_date = date('Y-m-d h:ia', $datetime);

		ob_start();
		?>
		<div class="btn-group" role="group" aria-label="Basic example">
			<?php if(!empty($fax_data['pid'])) { ?>
			<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
				id="<?php echo "unlinkinput".$fax_data['id'] ?>"
				title='<?php echo htmlspecialchars(xl('Fax Message'), ENT_QUOTES) ?>' onClick="unlinkButtonAjax('fax','<?php echo $fax_data['id']; ?>')">
					<span><?php echo htmlspecialchars(xl('Unlink'), ENT_NOQUOTES) ?></span>
			</a>	
			<?php } ?>

			<?php echo generateAssignEle('fax', $fax_data); ?>
			<?php echo checkMessageIsFailed('fax', $fax_data); ?>

		</div>
		<?php
		$actionhtml = ob_get_clean();

		// Message Content
		$message = isset($fax_data['message'])  ? $fax_data['message'] : "";
		$formatedMessage = MessagesLib::displayIframeMsg($message, 'text');

		ob_start();
		?>
		<iframe scrolling="no" data-id="<?php echo $fax_data['id']; ?>" class="contentiFrame" srcdoc="<?php echo htmlentities($formatedMessage) ?>"></iframe>
		<?php echo MessagesLib::displayAttachment($fax_data['type'], $fax_data['id'], $fax_data); ?>
		<?php
		$bodyContent = ob_get_clean();

		$toFrom = $fax_data['receivers_name'] ." (". $fax_data['msg_to'] .")";

		$data[] = array(
			'id' => $row_fax_id,
			'dt_control' => '',
			'select' => $fax_checked,
			'action' => $actionhtml,
			'active' => $checked,
			'date_time' => $msg_date,
			'assignment' => getassigned('fax', $fax_data),
			'author' => $user_name,
			'to_from' => $toFrom,
			'status' => $fax_data['description'],
			'content' => $bodyContent
		);
	}
	
	//Count Active/Inactive Emails
	$faxCount = sqlQuery("SELECT SUM(CASE WHEN activity = 1 THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN activity = 0 THEN 1 ELSE 0 END) AS inactive FROM `message_log` ml WHERE ml.`pid` = ? AND ml.`type` LIKE 'FAX' " , array($pid));

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data,
	  "otherData" => array(
	  	"activeCount" => $faxCount['active'],
	  	"inActiveCount" => $faxCount['inactive']
	  )
	);

	return $response;
}

function getPostalLetterMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal) {
	## Search 
	$searchQuery = array();
	if($searchValue != ''){
	   $searchQuery = array();
	}

	if(!empty($filterVal)) {
		if(isset($filterVal['active']) && $filterVal['active'] == "1") $searchQuery[] = "ml.activity = '1'";
		if(isset($filterVal['active']) && $filterVal['active'] == "0") $searchQuery[] = "ml.activity != '1'";
	}

	$searchQuery = implode(" AND ", $searchQuery);
	if(!empty($searchQuery)) $searchQuery = " AND " . $searchQuery;

	if(empty($columnSortOrder)) $columnSortOrder = 'desc';

	$ordercolumnName = "ml.`msg_time`";
	if($columnName == "active") $ordercolumnName = 'ml.activity';
	if($columnName == "date_time") $ordercolumnName = 'ml.`msg_time`';

	## Total number of records without filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `postal_letters` pl ON ml.`id` = pl.`message_id` WHERE ml.`pid` = ? and ml.type = 'P_LETTER'", array($pid));
	$totalRecords = $records['allcount'];

	## Total number of record with filtering
	$records = sqlQuery("SELECT count(ml.`id`) as allcount FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `postal_letters` pl ON ml.`id` = pl.`message_id` WHERE ml.`pid` = ? and ml.type = 'P_LETTER' " .$searchQuery, array($pid));
	$totalRecordwithFilter = $records['allcount'];

	## Fetch records
	$msgQuery = "SELECT ml.*, pl.`status_code`, pl.`description`, pl.`file_name`, pl.`url`, CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `postal_letters` pl ON ml.`id` = pl.`message_id` WHERE ml.`pid` = ? and ml.type = 'P_LETTER' ".$searchQuery." order by ".$ordercolumnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

	$msgRecords = sqlStatement($msgQuery, array($pid));
	$data = array();

	while ($postal_letter_data = sqlFetchArray($msgRecords)) {
		$row_postal_letter_id = $postal_letter_data['id'];
		
		//$postal_letter_checked = in_array($row_postal_letter_id, $form_selected_postal_letter_obj) ? "checked" : "";
		$postal_letter_checked = "";
		$checked = ($postal_letter_data['activity'] == '1') ? "checked" : "";
		
		$user_name = $postal_letter_data['user_name'];
		if (empty($postal_letter_data['userid']) && $postal_letter_data['direction'] == 'in') $user_name = '[&nbsp;PATIENT&nbsp;]';
		if (empty($user_name) && $postal_letter_data['direction'] == 'out') $user_name = '[&nbsp;SYSTEM&nbsp;]';
		
		$msg_date = $postal_letter_data['msg_time'];
		$datetime = strtotime($postal_letter_data['msg_time']);
		if ($datetime) $msg_date = date('Y-m-d h:ia', $datetime);

		ob_start();
		?>
		<div class="btn-group" role="group" aria-label="Basic example">
			<?php if(!empty($postal_letter_data['pid'])) { ?>
			<a href="javascript:;" class='btn btn-secondary btn-sm notes_link' 
				id="<?php echo "unlinkinput".$postal_letter_data['id'] ?>"
				title='<?php echo htmlspecialchars(xl('Postal Letter Message'), ENT_QUOTES) ?>' onClick="unlinkButtonAjax('postal_letter','<?php echo $postal_letter_data['id']; ?>')">
					<span><?php echo htmlspecialchars(xl('Unlink'), ENT_NOQUOTES) ?></span>
			</a>	
			<?php } ?>
			<?php echo generateAssignEle('postal_letter', $postal_letter_data); ?>
			<?php echo checkMessageIsFailed('postal_letter', $postal_letter_data); ?>

		</div>
		<?php
		$actionhtml = ob_get_clean();

		// Message Content
		$message = isset($postal_letter_data['message'])  ? $postal_letter_data['message'] : "";
		$formatedMessage = MessagesLib::displayIframeMsg($message, 'text');

		ob_start();
		?>
		<iframe scrolling="no" data-id="<?php echo $postal_letter_data['id']; ?>" class="contentiFrame" srcdoc="<?php echo htmlentities($formatedMessage) ?>"></iframe>
		<?php echo MessagesLib::displayAttachment($postal_letter_data['type'], $postal_letter_data['id'], $postal_letter_data); ?>
		<?php
		$bodyContent = ob_get_clean();

		$data[] = array(
			'id' => $row_postal_letter_id,
			'dt_control' => '',
			'select' => $postal_letter_checked,
			'action' => $actionhtml,
			'active' => $checked,
			'date_time' => $msg_date,
			'assignment' => getassigned('postal_letter', $postal_letter_data),
			'author' => $user_name,
			'to_from' => PostalLetter::getPostalLetterAddr($postal_letter_data),
			'status' => $postal_letter_data['description'],
			'content' => $bodyContent
		);
	}
	
	//Count Active/Inactive Emails
	$pLetterCount = sqlQuery("SELECT SUM(CASE WHEN activity = 1 THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN activity = 0 THEN 1 ELSE 0 END) AS inactive FROM `message_log` ml WHERE ml.`pid` = ? AND ml.`type` LIKE 'P_LETTER' " , array($pid));

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data,
	  "otherData" => array(
	  	"activeCount" => $pLetterCount['active'],
	  	"inActiveCount" => $pLetterCount['inactive']
	  )
	);

	return $response;
}

// Fetch Table Data
if($page_action == "internal_msg") {
	echo json_encode(getInternalMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal));
} else if($page_action == "email_msg") {
	echo json_encode(getEmailMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal));
} else if($page_action == "sms_msg") {
	echo json_encode(getSMSMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal));
} else if($page_action == "fax_msg") {
	echo json_encode(getFaxMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal));
} else if($page_action == "postal_letter_msg") {
	echo json_encode(getPostalLetterMsg($pid, $draw, $row, $rowperpage, $searchValue, $columnName, $columnSortOrder, $filterVal));
}


// Action Modes
if($mode == "notes_update") {
	$notes_act = $_POST['notes_act'];
	$notes_chk = $_POST['notes_chk'];
	foreach ($notes_act AS $key => $val) {
		if ($notes_chk[$key] == 'on') {
			sqlStatementNoLog("UPDATE `pnotes` SET activity = 1, `message_status` = IF(`message_status` = 'Done', 'New', `message_status`) WHERE id = ?", array($key));
		} else {
			sqlStatementNoLog("UPDATE `pnotes` SET `activity` = 0, `message_status` = 'Done' WHERE `id` = ?", array($key));
		}
	}
} elseif ($mode == "note_delete") {
	if ($noteid) {
		deletePnote($noteid);
		EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "pnotes: id " . $noteid);
	}
	$noteid = '';
} elseif ($form_set_id && $mode == 'note_assign' || $mode == 'note_release') {

	$assigned_to = $form_set_username;
	if(isset($form_set_group) && !empty($form_set_group)) {
		$assigned_to = 'GRP:'.$form_set_group;
	}

	$binds = array();
	$binds[] = $assigned_to;
	$binds[] = $form_set_id;

	sqlStatementNoLog("UPDATE `pnotes` SET `assigned_to` = ? WHERE `id` = ?", $binds);
} elseif(isset($form_set_id) && isset($form_set_mpid) && $mode == 'note_unlink') {
	sqlStatementNoLog("UPDATE `pnotes` SET `pid` = ? WHERE `id` = ?", array($form_set_mpid, $form_set_id));
} elseif ($mode == 'phone_update') {
	$phone_act = $_POST['phone_act'];
	$phone_chk = $_POST['phone_chk'];
	foreach ($phone_act AS $key => $val) {
		if ($phone_chk[$key] == 'on') {
			sqlStatementNoLog("UPDATE `message_log` SET activity = '1' WHERE id = ?", array($key));
		} else {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 0 WHERE id = ?", array($key));
		}
	}
} elseif ($form_set_id && $mode == 'assign') {
	$binds = array();
	$binds[] = ($mode == 'assign')? $form_set_uid : '';
	$binds[] = ($mode == 'assign' && !empty($form_set_group))? $form_set_group : '';
	$binds[] = $form_set_id;

	sqlStatementNoLog("UPDATE `message_log` SET `assigned` = ?, `assign_group` = ? WHERE `id` = ?", $binds);
} elseif ($mode == 'email_update') {
	$email_act = $_POST['email_act'];
	$email_chk = $_POST['email_chk'];
	foreach ($email_act AS $key => $val) {
		if ($email_chk[$key] == 'on') {
			sqlStatementNoLog("UPDATE `message_log` SET activity = '1' WHERE id = ?", array($key));
		} else {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 0 WHERE id = ?", array($key));
		}
	}
} elseif ($mode == 'sms_update') {
	
	$sms_act = $_POST['sms_act'];
	$sms_chk = $_POST['sms_chk'];
	foreach ($sms_act AS $key => $val) {
		if ($sms_chk[$key] == 'on') {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 1 WHERE `id` = ?", array($key));
		} else {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 0 WHERE `id`= ?", array($key));
		}
	}

} elseif ($mode == 'fax_update') {
	
	$fax_act = $_POST['fax_act'];
	$fax_chk = $_POST['fax_chk'];
	foreach ($fax_act AS $key => $val) {
		if ($fax_chk[$key] == 'on') {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 1 WHERE `id` = ?", array($key));
		} else {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 0 WHERE `id`= ?", array($key));
		}
	}

} elseif ($mode == 'postal_letter_update') {
	
	$postal_letter_act = $_POST['postal_letter_act'];
	$postal_letter_chk = $_POST['postal_letter_chk'];
	foreach ($postal_letter_act AS $key => $val) {
		if ($postal_letter_chk[$key] == 'on') {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 1 WHERE `id` = ?", array($key));
		} else {
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = 0 WHERE `id`= ?", array($key));
		}
	}

} elseif ($mode == 'postal_letter_refresh') {
	$statusResponce = PostalLetter::getLatestLetterStatus('', $pid);
	if($statusResponce['status'] === true) {
		echo json_encode(array('status' => true, 'message' => 'Success'));
	} else {
		//echo json_encode(array('status' => true, 'message' => 'Something went wrong'));
	}
} elseif ($mode == 'fax_status_refresh') {
	$statusResponce = FaxMessage::getLatestFaxStatus('', $pid);
	if($statusResponce['status'] === true) {
		echo json_encode(array('status' => true, 'message' => 'Success'));
	} else {
		echo json_encode(array('status' => true, 'message' => 'Something went wrong'));
	}
} elseif(isset($form_set_id) && $mode == 'makeallinactive') {
	EmailMessage::updateStatusOfMsg($form_set_id, true);
} elseif(isset($form_set_id) && isset($form_set_mpid) && $mode == 'unlink') {
	sqlStatementNoLog("UPDATE `message_log` SET `pid` = ? WHERE `id` = ?", array($form_set_mpid, $form_set_id));
} elseif (in_array($mode, array('email_adddoc', 'sms_adddoc', 'fax_adddoc', 'postal_letter_adddoc'))) {
	$active_tab = $_POST['active_tab'];
	$form_doc_destination_name = isset($_REQUEST['doc_destination_name']) ? $_REQUEST['doc_destination_name'] : "";
	$form_doc_category = isset($_REQUEST['doc_category']) ? $_REQUEST['doc_category'] : "";
	$form_docdate = isset($_REQUEST['form_docdate']) ? $_REQUEST['form_docdate'] : date("Y-m-d");

	$cType = array('email', 'sms', 'fax', 'postal_letter');
	if(in_array($active_tab, $cType)) {
		$tItem = $active_tab;

		${"form_selected_".$tItem."_act"} = isset($_REQUEST['selected_'.$tItem.'_act']) ? $_REQUEST['selected_'.$tItem.'_act'] : array();
		${"form_selected_".$tItem."_chk"} = isset($_REQUEST['selected_'.$tItem.'_chk']) ? $_REQUEST['selected_'.$tItem.'_chk'] : array();
		${"form_selected_".$tItem."_list_data"} = $_REQUEST['selected_'.$tItem.'_list_data'];
		
		${"form_selected_".$tItem."_obj"} = array();
		if(isset(${"form_selected_".$tItem."_list_data"}) && !empty(${"form_selected_".$tItem."_list_data"})) {
			$tmp_obj = json_decode(${"form_selected_".$tItem."_list_data"}, true);
			if(is_array($tmp_obj)) {
				${"form_selected_".$tItem."_obj"} = $tmp_obj;
			}
		}


		if($mode == $tItem."_adddoc") {
			foreach (${"form_selected_".$tItem."_act"} AS $key => $val) {
				$checkInArray = in_array($key, ${"form_selected_".$tItem."_obj"});
				if (isset(${"form_selected_".$tItem."_chk"}[$key]) && ${"form_selected_".$tItem."_chk"}[$key] == 'on') {
					if (!$checkInArray) {
						${"form_selected_".$tItem."_obj"}[] = $key;
					}
				} else {
					if ($checkInArray) {
						$item_index = array_search($key, ${"form_selected_".$tItem."_obj"});
						unset(${"form_selected_".$tItem."_obj"}[$item_index]);
					}
				}
			}

			${"form_selected_".$tItem."_list_data"} = ${"form_selected_".$tItem."_obj"};
		}

		if($mode == $tItem."_adddoc" && !empty($form_doc_destination_name) && !empty($form_doc_category)) {

			// Add Documents
			$docResponce = MessagesLib::addDocuments($tItem, ${"form_selected_".$tItem."_list_data"}, $form_doc_destination_name, $pid, $form_doc_category, $form_docdate);

			$error_msg = '';
			if($docResponce === false) {
				$error_msg = xl('Something went wrong.');
			} else if(isset($docResponce['error']) && !empty($docResponce['error'])) {
				$error_msg = $docResponce['error'];
			}

			if(isset($docResponce['message']) && !empty($docResponce['message'])) {
				${"form_selected_".$tItem."_list_data"} = array();
				${"form_selected_".$tItem."_obj"} = array();

				echo json_encode(array('status' => true, 'message' => $docResponce['message']));
				exit();
			}

			if(!empty($error_msg)) {
				echo json_encode(array('status' => false, 'message' => $error_msg));
				exit();
			}
		} else {
			echo json_encode(array('status' => false, 'message' => xl('Something went wrong.')));
			exit();
		}
	}
}