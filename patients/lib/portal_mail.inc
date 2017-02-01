<?php
/**
 * This file contains functions for handling on-site portal notes attached to patient files.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 */

function addPortalMailboxMail($owner, $newtext, $authorized = '0', $activity = '1',
		$title='Unassigned', $assigned_to = '', $datetime = '', $message_status = "New",$master_note = '0', $sid='', $sn='', $rid='', $rn='')
{
	if (empty($datetime)) $datetime = date('Y-m-d H:i:s');
	$user = $_SESSION['portal_username'] ? $_SESSION['portal_username'] : $_SESSION['authUser'];
	// make inactive if set as Done
	if ($message_status == "Done") $activity = 0;
	
	$body = $newtext;

	return sqlInsert("INSERT INTO onsite_mail (date, body, owner, user, groupname, " .
			"authorized, activity, title, assigned_to, message_status, mail_chain, sender_id, sender_name, recipient_id, recipient_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
			array($datetime, $body, $owner, $user, 'Default', $authorized, $activity, $title, $assigned_to, $message_status,$master_note,$sid,$sn,$rid,$rn) );
}
function getPortalPatientDeleted($owner = '', $limit = '', $offset = 0, $search = '')
{
	if($limit){
		$limit = "LIMIT ".escape_limit($offset).", ".escape_limit($limit);
	}
	$sql = "
	SELECT
	p.id,
	p.date,
	p.owner,
	p.user,
	p.title,
	p.body AS body,
	p.message_status,
	'Message' as `type`,
	p.sender_id,
	p.sender_name,
	p.recipient_id,
	p.recipient_name,
	p.mail_chain
	FROM
	onsite_mail AS p
	WHERE p.deleted != 0 AND p.owner != ? AND p.recipient_id = ?
	$search
	ORDER BY `date` asc
	$limit
	";
	$all = $row = array();
	$data = array($owner,$owner);
	if($search) $data = array($owner,$owner,$owner,$owner);
	$res = sqlStatement($sql, $data);
	for($iter = 0;$row = sqlFetchArray($res);$iter++){
		$all[$iter] = $row;
	}
	return $all;
}

// received notes
function getPortalPatientNotes($owner = '', $limit = '', $offset = 0, $search = '')
{
	if($limit){
		$limit = "LIMIT ".escape_limit($offset).", ".escape_limit($limit);
	}
	$sql = "
	SELECT
	p.id,
	p.date,
	p.owner,
	p.user,
	p.title,
	p.body AS body,
	p.message_status,
	'Message' as `type`,
	p.sender_id,
	p.sender_name,
	p.recipient_id,
	p.recipient_name,
	p.mail_chain
	FROM
	onsite_mail AS p
	WHERE p.deleted != 1 AND p.owner != ? AND p.recipient_id = ? 
	$search
	ORDER BY `date` asc
	$limit
	";
	$all = $row = array();
	$data = array($owner,$owner);
	if($search) $data = array($owner,$owner,$owner,$owner);
	$res = sqlStatement($sql, $data);
	for($iter = 0;$row = sqlFetchArray($res);$iter++){
		$all[$iter] = $row;
	}
	return $all;
}

function getPortalPatientNotifications($owner = '', $limit = '', $offset = 0, $search = '')
{
	if($limit){
		$limit = "LIMIT ".escape_limit($offset).", ".escape_limit($limit);
	}
	$sql = "
	SELECT
	pr.id,
	date_created AS `date`,
	'Patient Reminders' AS `user`,
	due_status AS title,
	CONCAT(lo.title, ':', lo2.title) AS body,
	'' as message_status,
	'Notification' as `type`
	FROM
	patient_reminders AS pr
	LEFT JOIN list_options AS lo
	ON lo.option_id = pr.category
	AND lo.list_id = 'rule_action_category'
	LEFT JOIN list_options AS lo2
	ON lo2.option_id = pr.item
	AND lo2.list_id = 'rule_action'
	WHERE pid = ?
	AND active = 1
	AND date_created > DATE_SUB(NOW(), INTERVAL 1 MONTH)
	$search
	ORDER BY `date` asc
	$limit
	";
	$all = $row = array();
	$res = sqlStatement($sql, array($owner));
	for($iter = 0;$row = sqlFetchArray($res);$iter++){
		$all[$iter] = $row;
	}
	return $all;
}

function getPortalPatientSentNotes($owner = '', $limit = '', $offset = 0, $search = '')
{
	if($limit){
		$limit = "LIMIT ".escape_limit($offset).", ".escape_limit($limit);
	}
	$sql = "
	SELECT
	p.id,
	p.date,
	p.assigned_to,
	p.title,
	p.body,
	p.activity,
	p.message_status,
	'Message' as `type`,
	p.mail_chain,
	p.owner,
	p.sender_id,
	p.sender_name,
	p.recipient_id,
	p.recipient_name
	FROM
	onsite_mail AS p
	WHERE p.sender_id = ?
	AND p.deleted != 1
	AND p.owner = ?
	AND p.message_status != 'Done'
	$search
	ORDER BY `date` asc
	$limit
	";
	$all = $row = array();
	$res = sqlStatement($sql, array($owner,$owner));
	for($iter = 0;$row = sqlFetchArray($res);$iter++){
		$all[$iter] = $row;
	}
	return $all;
}
function updatePortalMailMessageStatus($id, $message_status)
{
	if ($message_status == "Done") {
		sqlStatement("update onsite_mail set message_status = ?, activity = '0' where id = ?", array($message_status, $id) );
	}
	elseif($message_status == "Delete") {
		sqlStatement("update onsite_mail set message_status = ?, activity = '1', deleted = '1',delete_date = ? where id = ?", array($message_status, date('Y-m-d H:i:s'), $id) );
	}
	else{
		sqlStatement("update onsite_mail set message_status = ?, activity = '1' where id = ?", array($message_status, $id) );
	}
}
function getMails($owner,$dotype,$nsrch,$nfsrch){
	if($owner){
		if($dotype == "inbox"){
			if($nsrch && $nfsrch){
				$result_notes = getPortalPatientNotes($owner,'','0',$nsrch);
				$result_notifications = getPortalPatientNotifications($owner,'','0',$nfsrch);
				$result = array_merge((array)$result_notes,(array)$result_notifications);
			}else{
				$result_notes = getPortalPatientNotes($owner);
				$result_notifications = getPortalPatientNotifications($owner);
				$result = array_merge((array)$result_notes,(array)$result_notifications);
				//$result = $result_notes;
			}
			return $result;
		}elseif($dotype == "sent"){
			if($nsrch){
				$result_sent_notes = getPortalPatientSentNotes($owner,'','0',$nsrch);
			}else{
				$result_sent_notes = getPortalPatientSentNotes($owner);
			}
			return $result_sent_notes;
		}elseif($dotype == "all"){
			$result = array();
			$result_notes = getPortalPatientNotes($owner,'','0',"OR (p.deleted != 1 AND (p.sender_id = ? OR p.recipient_id = ?)) ");
			$result_notifications = getPortalPatientNotifications($owner);
			$result = array_merge((array)$result_notes,(array)$result_notifications);
			return $result;
		}elseif($dotype == "deleted"){
			$result = array();
			$result = getPortalPatientDeleted($owner,'','0',"OR (p.deleted = 1 AND (p.sender_id = ? OR p.recipient_id = ?)) ");
			return $result;
			}
		}else{
			return 'failed';
		}
}

function getMailDetails($id){
	if($owner){
		$result = getMailById($id);
		if($result['owner'] == $owner && $result['message_status'] == 'New'){
			updatePortalMailMessageStatus($id,'Read');
		}
		return $result;
	}else{
		return 'failed';
	}
}

function sendMail($owner,$note,$title='Unassigned',$to, $noteid,$sid,$sn,$rid,$rn){
	if($owner){
		addPortalMailboxMail($owner,$note,'1','1',$title,$to,'','New',$noteid,$sid,$sn,$rid,$rn);
		return 1;
	}else{
		return 'failed';
	}
}


function updateStatus($id,$status){
		updatePortalMailMessageStatus($id,$status);
		return 1;
}
?>