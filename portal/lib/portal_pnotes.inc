<?php
/**
 *
 * This file contains functions for handling on-site portal notes attached to patient files.
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 2 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

require_once("$srcdir/pnotes.inc");

function addPortalMailboxPnote(
    $pid,
    $newtext,
    $authorized = '0',
    $activity = '1',
    $title = 'Unassigned',
    $assigned_to = '',
    $datetime = '',
    $message_status = "New",
    $master_note = '0'
) {

    if (empty($datetime)) {
        $datetime = date('Y-m-d H:i:s');
    }

    // make inactive if set as Done
    if ($message_status == "Done") {
        $activity = 0;
    }

    $user = $_SESSION['authUser']?$_SESSION['authUser']:$pid;
    $pname = $_SESSION['ptName']?$_SESSION['ptName']:$user;

    /* $body =  " ($pname";
	if ($assigned_to) $body .= " to $assigned_to";
	$body = $body . ') ' . $newtext; */

    $body = $newtext;

    return sqlInsert(
        "INSERT INTO pnotes (date, body, pid, user, groupname, " .
            "authorized, activity, title, assigned_to, message_status, portal_relation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        array($datetime, $body, $pid, $user, 'Default', $authorized, $activity, $title, $assigned_to, $message_status,$master_note)
    );
}

function getPortalPatientNotes($pid = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
        $limit = "LIMIT ".escape_limit($offset).", ".escape_limit($limit);
    }

    $sql = "
	SELECT
	p.id,
	p.date,
	p.user,
	p.title,
	REPLACE(
	p.body,
	'-patient-',
	CONCAT(pd.fname, ' ', pd.lname)
	) AS body,
	p.message_status,
	'Message' as `type`,
	p.portal_relation
	FROM
	pnotes AS p
	LEFT JOIN patient_data AS pd
	ON pd.pid = p.pid
	WHERE assigned_to = '-patient-'
	AND p.deleted != 1
	AND p.pid = ?
	$search
	ORDER BY `date` desc
	$limit
	";
    $res = sqlStatement($sql, array($pid));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getPortalPatientNotifications($pid = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
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
	ORDER BY `date` desc
	$limit
	";
    $res = sqlStatement($sql, array($pid));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getPortalPatientSentNotes($pid = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
        $limit = "LIMIT ".escape_limit($offset).", ".escape_limit($limit);
    }

    $sql = "
	SELECT
	p.id,
	p.date,
	p.assigned_to,
	p.title,
	REPLACE(
	p.body,
	'-patient-',
	CONCAT(pd.fname, ' ', pd.lname)
	) AS body,
	p.activity,
	p.message_status,
	'Message' as `type`,
	p.portal_relation
	FROM
	pnotes AS p
	LEFT JOIN patient_data AS pd
	ON pd.pid = p.pid
	WHERE `user` = ?
	AND p.deleted != 1
	AND p.pid = ?
	AND p.message_status != 'Done'
	$search
	ORDER BY `date` desc
	$limit
	";
    $res = sqlStatement($sql, array($pid,$pid));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}
function updatePortalPnoteMessageStatus($id, $message_status)
{
    if ($message_status == "Done") {
        sqlStatement("update pnotes set message_status = ?, activity = '0' where id = ?", array($message_status, $id));
    } else {
        sqlStatement("update pnotes set message_status = ?, activity = '1' where id = ?", array($message_status, $id));
    }
}
function getMails($pid, $dotype, $nsrch, $nfsrch)
{

    if ($pid) {
        if ($dotype == "inbox") {
            if ($nsrch && $nfsrch) {
                $result_notes = getPortalPatientNotes($pid, '', '0', $nsrch);
                $result_notifications = getPortalPatientNotifications($pid, '', '0', $nfsrch);
                $result = array_merge((array)$result_notes, (array)$result_notifications);
            } else {
                $result_notes = getPortalPatientNotes($pid);
                $result_notifications = getPortalPatientNotifications($pid);
                $result = array_merge((array)$result_notes, (array)$result_notifications);
            }

            return $result;
        } elseif ($dotype == "sent") {
            if ($nsrch) {
                $result_sent_notes = getPortalPatientSentNotes($pid, '', '0', $nsrch);
            } else {
                $result_sent_notes = getPortalPatientSentNotes($pid);
            }

            return $result_sent_notes;
        }
    } else {
        return 'failed';
    }
}

function getMailDetails($id)
{
    if ($pid) {
        $result = getPnoteById($id);
        if ($result['assigned_to'] == '-patient-' && $result['message_status'] == 'New') {
            updatePortalPnoteMessageStatus($id, 'Read');
        }

        return $result;
    } else {
        return 'failed';
    }
}

function sendMail($pid, $note, $title = 'Unassigned', $to, $noteid)
{
    if ($pid) {
        addPortalMailboxPnote($pid, $note, '1', '1', $title, $to, '', 'New', $noteid);
        return 1;
    } else {
        return 'failed';
    }
}


function updateStatus($id, $status)
{
    if ($pid) {
        updatePortalPnoteMessageStatus($id, $status);
    } else {
        return 'failed';
    }
}
