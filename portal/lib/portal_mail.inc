<?php

/**
 * This file contains functions for handling on-site portal mail.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Logging\EventAuditLogger;

function addPortalMailboxMail(
    $owner,
    $newtext,
    $authorized = '0',
    $activity = '1',
    $title = 'Unassigned',
    $assigned_to = '',
    $datetime = '',
    $message_status = "New",
    $master_note = '0',
    $sid = '',
    $sn = '',
    $rid = '',
    $rn = '',
    $replyid = 0
) {

    if (empty($datetime)) {
        $datetime = date('Y-m-d H:i:s');
    }

    $user = $_SESSION['portal_username'] ? $_SESSION['portal_username'] : $_SESSION['authUser'];
    // make inactive if set as Done
    if ($message_status == "Done") {
        $activity = 0;
    }

    $body = $newtext;
    if ($master_note == '0') {
        $n = sqlQueryNoLog("SELECT MAX(id) as newid from onsite_mail");
        $master_note = $n['newid'] + 1;
    }

    if ($replyid) {
        if ($owner != $sid) {
            $hold = $master_note;
            $master_note = $replyid;
            $replyid = $hold;
        } else {
            $replyid = $master_note;
        }
    } elseif ($owner != $sid) {
        $replyid = $master_note - 1;
    } else {
        $replyid = $master_note;
    }

    return sqlInsert(
        "INSERT INTO onsite_mail (date, body, owner, user, groupname, " .
            "authorized, activity, title, assigned_to, message_status, mail_chain, sender_id, sender_name, recipient_id, recipient_name, reply_mail_chain) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)",
        array($datetime, $body, $owner, $user, 'Default', $authorized, $activity, $title, $assigned_to, $message_status,$master_note,$sid,$sn,$rid,$rn,$replyid)
    );
}

function getPortalPatientDeleted($owner = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
        $limit = "LIMIT " . escape_limit($offset) . ", " . escape_limit($limit);
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
	p.mail_chain,
	p.reply_mail_chain
	FROM
	onsite_mail AS p
	WHERE p.deleted != 0 AND p.owner = ? AND p.recipient_id = ?
	$search
	ORDER BY `date` desc
	$limit
	";
    $all = $row = array();
    $data = array($owner,$owner);
    if ($search) {
        $data = array($owner,$owner,$owner);
    }

    $res = sqlStatement($sql, $data);
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getPortalPatientNotes($owner = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
        $limit = "LIMIT " . escape_limit($offset) . ", " . escape_limit($limit);
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
	p.mail_chain,
	p.reply_mail_chain
	FROM
	onsite_mail AS p
	WHERE p.deleted != 1 AND p.owner = ? AND p.recipient_id = ?
	$search
	ORDER BY `date` desc
	$limit
	";
    $all = $row = array();
    $data = array($owner,$owner);
    if ($search) {
        $data = array($owner,$owner,$owner);
    }

    $res = sqlStatement($sql, $data);
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getPortalPatientNotifications($owner = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
        $limit = "LIMIT " . escape_limit($offset) . ", " . escape_limit($limit);
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
	ORDER BY `date` desc
	$limit
	";
    $all = $row = array();
    $res = sqlStatement($sql, array($owner));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getPortalPatientSentNotes($owner = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
        $limit = "LIMIT " . escape_limit($offset) . ", " . escape_limit($limit);
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
	p.reply_mail_chain,
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
	ORDER BY `date` desc
	$limit
	";
    $all = $row = array();
    $res = sqlStatement($sql, array($owner,$owner));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function updatePortalMailMessageStatus($id, $message_status, $owner)
{
    if ($message_status == "Done") {
        sqlStatement("update onsite_mail set message_status = ?, activity = '0' where id = ? and `owner` = ?", array($message_status, $id, $owner));
    } elseif ($message_status == "Delete") {
        sqlStatement("update onsite_mail set message_status = ?, activity = '1', deleted = '1',delete_date = ? where (mail_chain = ? OR id = ?) and `owner` = ?", array($message_status, date('Y-m-d H:i:s'), $id, $id, $owner));
    } else {
        sqlStatement("update onsite_mail set message_status = ?, activity = '1' where id = ? and `owner` = ?", array($message_status, $id, $owner));
    }

    if ($message_status == "Delete") {
        $stats = sqlQuery("Select * From onsite_mail Where id = ? AND `owner` = ?", array($id, $owner));
        $by = $_SESSION['authUser'] ? $_SESSION['authUser'] : $_SESSION['ptName'];
        $loguser = $_SESSION['authUser'] ? $_SESSION['authUser'] : $_SESSION['portal_username'];
        $evt = "secure message soft delete by " . $by . " msg id: $id from " . $stats['sender_name'] . " to recipient: " . $stats['recipient_name'];
        $log_from = '';
        $puser = '';
        if ($_SESSION['patient_portal_onsite_two']) {
            $log_from = 'patient-portal';
            $puser = $_SESSION['pid'];
        }
        EventAuditLogger::instance()->newEvent("delete", $loguser, 'Portal', 1, $evt, $puser, $log_from, '');
    }
}

function getMails($owner, $dotype, $nsrch, $nfsrch)
{
    if ($owner) {
        if ($dotype == "inbox") {
            if ($nsrch && $nfsrch) {
                $result_notes = getPortalPatientNotes($owner, '', '0', $nsrch);
                $result_notifications = getPortalPatientNotifications($owner, '', '0', $nfsrch);
                $result = array_merge((array)$result_notes, (array)$result_notifications);
            } else {
                $result_notes = getPortalPatientNotes($owner);
                $result_notifications = getPortalPatientNotifications($owner);
                $result = array_merge((array)$result_notes, (array)$result_notifications);
                //$result = $result_notes;
            }

            return $result;
        } elseif ($dotype == "sent") {
            if ($nsrch) {
                $result_sent_notes = getPortalPatientSentNotes($owner, '', '0', $nsrch);
            } else {
                $result_sent_notes = getPortalPatientSentNotes($owner);
            }

            return $result_sent_notes;
        } elseif ($dotype == "all") {
            $result = array();
            $result_notes = getPortalPatientNotes($owner, '', '0', "OR (p.deleted != 1 AND (p.owner = ?)) ");
            $result_notifications = getPortalPatientNotifications($owner);
            $result = array_merge((array)$result_notes, (array)$result_notifications);
            return $result;
        } elseif ($dotype == "deleted") {
            $result = array();
            $result = getPortalPatientDeleted($owner, '', '0', "OR (p.deleted = 1 AND (p.owner = ?)) ");
            return $result;
        }
    } else {
        return 'failed';
    }
}

function sendMail($owner, $note, string $title = null, $to, $noteid, $sid, $sn, $rid, $rn, $status = 'New', $replyid = '')
{
    if (!$title) {
        $title = 'Unassigned';
    }
    if ($owner) {
        addPortalMailboxMail($owner, $note, '1', '1', $title, $to, '', $status, $noteid, $sid, $sn, $rid, $rn, $replyid);
        return 1;
    } else {
        return 'failed';
    }
}
