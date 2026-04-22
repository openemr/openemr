<?php

/**
 * This file contains functions for handling notes attached to patient files.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * 2013-02-08 EMR Direct: changes to allow notes added by background-services with pid=0
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Retrieve a note, given its ID
 *
 * @param string $id the ID of the note to retrieve.
 * @param string $cols A list of columns to retrieve. defaults to '*' for all.
 */
function getPnoteById($id, $cols = "*")
{
    return QueryUtils::querySingleRow("SELECT "  . escape_sql_column_name(process_cols_escape($cols), ['pnotes']) . " FROM pnotes WHERE id=? " .
    ' AND deleted != 1 ' . // exclude ALL deleted notes
    'order by date DESC limit 0,1', [$id]);
}

/**
 * Check a note, given its ID to see if it matches the user
 *
 * @param string $id the ID of the note to retrieve.
 * @param string $user the user seeking to view the note
 */
function checkPnotesNoteId(int $id, string $user): bool
{
    $check = QueryUtils::querySingleRow("SELECT `id`, `user`, `assigned_to` FROM pnotes WHERE id = ? AND deleted != 1", [$id]);
    if (
        !empty($check['id'])
        && ($check['id'] == $id)
        && (in_array($user, [$check['user'], $check['assigned_to']]))
    ) {
        return true;
    } elseif (
        checkPortalAuthUser($user)
        && !empty($check['id'])
        && ($check['id'] == $id)
        && ('portal-user' === $check['assigned_to'])
    ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check if an auth portal user
 *
 * @param string $user the user seeking to view the note
 * @return bool
 */
function checkPortalAuthUser(string $user): bool
{
    $check = QueryUtils::querySingleRow("SELECT `id` FROM users WHERE portal_user = 1 AND username = ? AND active = 1", [$user]);
    if (!empty($check['id'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get the patient notes for the given user.
 *
 * This function is used to retrieve notes assigned to the given user, or
 * optionally notes assigned to any user.
 *
 * @param string $activity 0 for deleted notes, 1 (the default) for active
 *                         notes, or 'All' for all.
 * @param string $show_all whether to display only the selected user's
 *                         messages, or all users' messages.
 * @param string $user The user whom's notes you want to retrieve.
 * @param bool $count Whether to return a count, or just return 0.
 * @param string $sortby A field to sort results by. (options are users.lname,patient_data.lname,pnotes.title,pnotes.date,pnotes.message_status) (will default to users.lname)
 * @param string $sortorder whether to sort ascending or descending.
 * @param string $begin what row to start retrieving results from.
 * @param string $listnumber number of rows to return.
 * @return int The number of rows retrieved, or 0 if $count was true.
 */
function getPnotesByUser($activity = "1", $show_all = "no", $user = '', $count = false, $sortby = '', $sortorder = '', $begin = '', $listnumber = '')
{

  // Set the activity part of query
    if ($activity == '1') {
        $activity_query = " pnotes.message_status != 'Done' AND pnotes.activity = 1 AND ";
    } elseif ($activity == '0') {
        $activity_query = " (pnotes.message_status = 'Done' OR pnotes.activity = 0) AND ";
    } else { //$activity=='all'
        $activity_query = " ";
    }
    $sqlBindArray = [];
    $includePortalUser = false;
  // Set whether to show chosen user or all users
    if ($show_all == 'yes') {
        $usrvar = '_%';
    } else {
        if (checkPortalAuthUser($user)) {
            $includePortalUser = true;
        }
        $usrvar = $user;
    }

  // run the query
  // 2013-02-08 EMR Direct: minor changes to query so notes with pid=0 don't disappear
    $fromWhere = "FROM ((pnotes LEFT JOIN users ON pnotes.user = users.username)
          LEFT JOIN patient_data ON pnotes.pid = patient_data.pid) WHERE $activity_query
          pnotes.deleted != '1' AND (pnotes.assigned_to LIKE ?";
    $sqlBindArray[] = $usrvar;
    if ($includePortalUser) {
        $fromWhere .= " OR pnotes.assigned_to = ?";
        $sqlBindArray[] = 'portal-user';
    }
    $fromWhere .= ")";

  // return the results
    if ($count) {
        $row = QueryUtils::querySingleRow("SELECT COUNT(*) AS cnt $fromWhere", $sqlBindArray);
        return $row !== false ? (int) $row['cnt'] : 0;
    }

    $sql = "SELECT pnotes.id, pnotes.user, pnotes.pid, pnotes.title, pnotes.date, pnotes.message_status, pnotes.activity,
          IF(pnotes.pid = 0 OR pnotes.user != pnotes.pid,users.fname,patient_data.fname) as users_fname,
          IF(pnotes.pid = 0 OR pnotes.user != pnotes.pid,users.lname,patient_data.lname) as users_lname,
          patient_data.fname as patient_data_fname, patient_data.lname as patient_data_lname
          $fromWhere";
    if (!empty($sortby) || !empty($sortorder)  || !empty($begin) || !empty($listnumber)) {
        $sql .= " order by " . escape_sql_column_name($sortby, ['users','patient_data','pnotes'], true) .
            " " . escape_sort_order($sortorder) .
            " limit " . escape_limit($begin) . ", " . escape_limit($listnumber);
    }
    return QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
}

function getPnotesByDate(
    $date,
    $activity = "1",
    $cols = "*",
    $pid = "%",
    $limit = "all",
    $start = 0,
    $username = '',
    $docid = 0,
    $status = "",
    $orderid = 0
) {

    $sqlParameterArray = [];
    if ($docid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), ['pnotes', 'gprelations']) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 1 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid != p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $docid);
    } elseif ($orderid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), ['pnotes', 'gprelations']) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 2 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid != p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $orderid);
    } else {
        $sql = "SELECT "  . escape_sql_column_name(process_cols_escape($cols), ['pnotes']) . " FROM pnotes AS p " .
        "WHERE date LIKE ? AND pid LIKE ? AND p.pid != p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $pid);
    }

    $sql .= " AND deleted != 1"; // exclude ALL deleted notes
    if ($activity != "all") {
        if ($activity == '0') {
            // only return inactive
            $sql .= " AND (activity = '0' OR message_status = 'Done') ";
        } else { // $activity == '1'
            // only return active
            $sql .= " AND activity = '1' AND message_status != 'Done' ";
        }
    }

    if ($username) {
        $sql .= " AND assigned_to LIKE ?";
        array_push($sqlParameterArray, $username);
    }

    if ($status) {
        $statusArr = explode(",", $status);
        $placeholders = implode(",", array_fill(0, count($statusArr), "?"));
        $sql .= " AND message_status IN ($placeholders)";
        array_push($sqlParameterArray, ...$statusArr);
    }

    $sql .= " ORDER BY date DESC";
    if ($limit != "all") {
        $sql .= " LIMIT " . escape_limit($start) . ", " . escape_limit($limit);
    }

    return QueryUtils::fetchRecords($sql, $sqlParameterArray);
}

// activity can only be 0, 1, or 'all'
function getSentPnotesByDate(
    $date,
    $activity = "1",
    $cols = "*",
    $pid = "%",
    $limit = "all",
    $start = 0,
    $username = '',
    $docid = 0,
    $status = "",
    $orderid = 0
) {

    $sqlParameterArray = [];
    if ($docid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), ['pnotes', 'gprelations']) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 1 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid = p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $docid);
    } elseif ($orderid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), ['pnotes','gprelations']) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 2 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid = p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $orderid);
    } else {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), ['pnotes']) . " FROM pnotes AS p " .
        "WHERE date LIKE ? AND pid LIKE ? AND p.pid = p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $pid);
    }

    $sql .= " AND deleted != 1"; // exclude ALL deleted notes
    if ($activity != "all") {
        if ($activity == '0') {
            // only return inactive
            $sql .= " AND (activity = '0' OR message_status = 'Done') ";
        } else { // $activity == '1'
            // only return active
            $sql .= " AND activity = '1' AND message_status != 'Done' ";
        }
    }

    if ($username) {
        $sql .= " AND assigned_to LIKE ?";
        array_push($sqlParameterArray, $username);
    }

    if ($status) {
        $statusArr = explode(",", $status);
        $placeholders = implode(",", array_fill(0, count($statusArr), "?"));
        $sql .= " AND message_status IN ($placeholders)";
        array_push($sqlParameterArray, ...$statusArr);
    }

    $sql .= " ORDER BY date DESC";
    if ($limit != "all") {
        $sql .= " LIMIT " . escape_limit($start) . ", " . escape_limit($limit);
    }

    return QueryUtils::fetchRecords($sql, $sqlParameterArray);
}

/** Add a note to a patient's medical record.
 *
 * @param int $pid the ID of the patient whose medical record this note is going to be attached to.
 * @param string $newtext the note contents.
 * @param int $authorized
 * @param int $activity
 * @param string $title
 * @param string $assigned_to
 * @param string $datetime
 * @param string $message_status
 * @param string $background_user if set then the pnote is created by a background-service rather than a user
 * @return int the ID of the added note.
 */
function addPnote(
    $pid,
    $newtext,
    $authorized = '0',
    $activity = '1',
    $title = 'Unassigned',
    $assigned_to = '',
    $datetime = '',
    $message_status = 'New',
    $background_user = ""
) {
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    if (empty($datetime)) {
        $datetime = date('Y-m-d H:i:s');
    }

  // make inactive if set as Done
    if ($message_status == 'Done') {
        $activity = 0;
    }
    $user = ($background_user != "" ? $background_user : $session->get('authUser'));
    $body = date('Y-m-d H:i') . ' (' . $user;
    if ($assigned_to) {
        $body .= " to $assigned_to";
    }

    $body = $body . ') ' . $newtext;

    return QueryUtils::sqlInsert(
        'INSERT INTO pnotes (date, body, pid, user, groupname, ' .
        'authorized, activity, title, assigned_to, message_status, update_by, update_date) VALUES ' .
        '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
        [$datetime, $body, $pid, $user, $session->get('authProvider'), $authorized, $activity, $title, $assigned_to, $message_status, $session->get('authUserID')]
    );
}

function addMailboxPnote(
    $pid,
    $newtext,
    $authorized = '0',
    $activity = '1',
    $title = 'Unassigned',
    $assigned_to = '',
    $datetime = '',
    $message_status = "New"
) {
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    if (empty($datetime)) {
        $datetime = date('Y-m-d H:i:s');
    }

  // make inactive if set as Done
    if ($message_status == "Done") {
        $activity = 0;
    }

    $body = date('Y-m-d H:i') . ' (' . $pid;
    if ($assigned_to) {
        $body .= " to $assigned_to";
    }

    $body = $body . ') ' . $newtext;

    return QueryUtils::sqlInsert(
        "INSERT INTO pnotes (date, body, pid, user, groupname, " .
        "authorized, activity, title, assigned_to, message_status, update_by, update_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$datetime, $body, $pid, $pid, 'Default', $authorized, $activity, $title, $assigned_to, $message_status, $session->get('authUserID')]
    );
}

/**
 * Update a patient note.
 *
 * @param int|string $id Note ID
 * @param string $newtext New text to append
 * @param string $title Note title/type
 * @param string $assigned_to Username to assign to
 * @param string $message_status Message status
 * @param string $datetime Optional datetime
 * @param int|null $pid Patient ID for access control. When provided, the note must belong
 *                      to this patient or the update is denied (IDOR protection).
 */
function updatePnote($id, $newtext, $title, $assigned_to, $message_status = "", $datetime = "", ?int $pid = null): void
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $row = getPnoteById($id);
    if (! $row) {
        throw new \RuntimeException("updatePnote() did not find id '" . text($id) . "'");
    }
    // IDOR protection: verify note belongs to expected patient
    if ($pid !== null && (int)$row['pid'] !== $pid) {
        die("updatePnote() access denied: note does not belong to patient");
    }

    if (empty($datetime)) {
        $datetime = date('Y-m-d H:i:s');
    }

    $activity = $assigned_to ? '1' : '0';

  // make inactive if set as Done
    if ($message_status == "Done") {
        $activity = 0;
    }

    $body = $row['body'] . "\n" . date('Y-m-d H:i') .
    ' (' . $session->get('authUser');
    if ($assigned_to) {
        $body .= " to $assigned_to";
    }

    $body = $body . ') ' . $newtext;


    $sql = "UPDATE pnotes SET " .
        "body = ?, activity = ?, title= ?, " .
        "assigned_to = ?, update_by = ?, update_date = NOW()";
    $bindingParams =  [$body, $activity, $title, $assigned_to, $session->get('authUserID')];
    if ($message_status) {
        $sql .= " ,message_status = ?";
        $bindingParams[] = $message_status;
    }
    if (OEGlobalsBag::getInstance()->getBoolean('messages_due_date')) {
        $sql .= " ,date = ?";
        $bindingParams[] = $datetime;
    }
    $sql .= " WHERE id = ?";
    $bindingParams[] = $id;
    QueryUtils::sqlStatementThrowException($sql, $bindingParams);
}

/**
 * Update a note's message status.
 *
 * @param int|string $id Note ID
 * @param string $message_status New status
 * @param int|null $pid Patient ID for access control. When provided, the note must belong
 *                      to this patient or the update is denied (IDOR protection).
 */
function updatePnoteMessageStatus($id, $message_status, ?int $pid = null): void
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    // IDOR protection: verify note belongs to expected patient
    if ($pid !== null) {
        $row = getPnoteById((string)$id, 'pid');
        if (!is_array($row) || (int)$row['pid'] !== $pid) {
            die("updatePnoteMessageStatus() access denied: note does not belong to patient");
        }
    }
    if ($message_status == "Done") {
        QueryUtils::sqlStatementThrowException("update pnotes set message_status = ?, activity = '0', update_by = ?, update_date = NOW() where id = ?", [$message_status, $session->get('authUserID'), $id]);
    } else {
        QueryUtils::sqlStatementThrowException("update pnotes set message_status = ?, activity = '1', update_by = ?, update_date = NOW() where id = ?", [$message_status, $session->get('authUserID'), $id]);
    }
}

/**
 * Set the patient id in an existing message where pid=0
 * @param $id the id of the existing note
 * @param $patient_id the patient id to associate with the note
 * @author EMR Direct <http://www.emrdirect.com/>
 */
function updatePnotePatient($id, int $patient_id): void
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $row = getPnoteById($id);
    if (! $row) {
        throw new \RuntimeException("updatePnotePatient() did not find id '" . text($id) . "'");
    }

    if ($row['pid'] != 0 || $patient_id < 1) {
        ServiceContainer::getLogger()->error("updatePnotePatient invalid operation for id {id}, patient_id {patient_id}, pid {pid}", ['id' => $id, 'patient_id' => $patient_id, 'pid' => $row['pid']]);
        throw new \RuntimeException("updatePnotePatient invalid operation");
    }

    $newtext = "\n" . date('Y-m-d H:i') . " (patient set by " . $session->get('authUser') . ")";
    $body = $row['body'] . $newtext;

    QueryUtils::sqlStatementThrowException("UPDATE pnotes SET pid = ?, body = ?, update_by = ?, update_date = NOW() WHERE id = ?", [$patient_id, $body, $session->get('authUserID'), $id]);
}

/**
 * Authorize a patient note.
 *
 * @param int|string $id Note ID
 * @param string $authorized Authorization status
 * @param int|null $pid Patient ID for access control. When provided, the note must belong
 *                      to this patient or the update is denied (IDOR protection).
 */
function authorizePnote($id, $authorized = "1", ?int $pid = null): void
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    // IDOR protection: verify note belongs to expected patient
    if ($pid !== null) {
        $row = getPnoteById((string)$id, 'pid');
        if (!is_array($row) || (int)$row['pid'] !== $pid) {
            die("authorizePnote() access denied: note does not belong to patient");
        }
    }
    QueryUtils::sqlStatementThrowException("UPDATE pnotes SET authorized = ? , update_by = ?, update_date = NOW() WHERE id = ?", [$authorized, $session->get('authUserID'), $id]);
}

/**
 * Mark a note as inactive/done.
 *
 * @param int|string $id Note ID
 * @param int|null $pid Patient ID for access control. When provided, the note must belong
 *                      to this patient or the update is denied (IDOR protection).
 * @return bool True on success
 */
function disappearPnote($id, ?int $pid = null): bool
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    // IDOR protection: verify note belongs to expected patient
    if ($pid !== null) {
        $row = getPnoteById((string)$id, 'pid');
        if (!is_array($row) || (int)$row['pid'] !== $pid) {
            return false;
        }
    }
    QueryUtils::sqlStatementThrowException("UPDATE pnotes SET activity = '0', message_status = 'Done', update_by = ?, update_date = NOW()  WHERE id=?", [$session->get('authUserID'), $id]);
    return true;
}

/**
 * Mark a note as active again.
 *
 * @param int|string $id Note ID
 * @param int|null $pid Patient ID for access control. When provided, the note must belong
 *                      to this patient or the update is denied (IDOR protection).
 * @return bool True on success
 */
function reappearPnote($id, ?int $pid = null): bool
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    // IDOR protection: verify note belongs to expected patient
    if ($pid !== null) {
        $row = getPnoteById((string)$id, 'pid');
        if (!is_array($row) || (int)$row['pid'] !== $pid) {
            return false;
        }
    }
    QueryUtils::sqlStatementThrowException("UPDATE pnotes SET activity = '1', message_status = IF(message_status='Done','New',message_status), update_by = ?, update_date = NOW() WHERE id=?", [$session->get('authUserID'), $id]);
    return true;
}

/**
 * Delete (soft-delete) a patient note.
 *
 * @param int|string $id Note ID
 * @param int|null $pid Patient ID for access control. When provided, the note must belong
 *                      to this patient or the delete is denied (IDOR protection).
 * @return bool True on success, false on denial
 */
function deletePnote($id, ?int $pid = null): bool
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    // IDOR protection: verify note belongs to expected patient
    if ($pid !== null) {
        $row = getPnoteById((string)$id, 'pid');
        if (!is_array($row) || (int)$row['pid'] !== $pid) {
            return false;
        }
    }
    $assigned = getAssignedToById($id);
    $authUser = $session->get('authUser');
    if (!checkPortalAuthUser($authUser) && $assigned == 'portal-user') {
        return false;
    }
    if (
        $assigned == $authUser
        || $assigned == 'portal-user'
        || getMessageStatusById($id) == 'Done'
    ) {
        QueryUtils::sqlStatementThrowException("UPDATE pnotes SET deleted = '1', update_by = ?, update_date = NOW() WHERE id=?", [$session->get('authUserID'), $id]);
        return true;
    } else {
        return false;
    }
}

// Note that it is assumed that html escaping has happened before this function is called
function pnoteConvertLinks($note)
{
    $noteActiveLink = preg_replace('!(https://[-a-zA-Z()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank" rel="noopener">$1</a>', (string) $note);
    if (empty($noteActiveLink)) {
        // something bad happened (preg_replace returned null) or the $note was empty
        return $note;
    } else {
        return $noteActiveLink;
    }
}

/**
 * Retrieve assigned_to field given the note ID
 *
 * @param string $id the ID of the note to retrieve.
 */
function getAssignedToById($id)
{
    $result = QueryUtils::querySingleRow("SELECT assigned_to FROM pnotes WHERE id=?", [$id]);
    return $result['assigned_to'];
}

/**
 * Retrieve message_status field given the note ID
 *
 * @param string $id the ID of the note to retrieve.
 */
function getMessageStatusById($id)
{
    $result = QueryUtils::querySingleRow("SELECT message_status FROM pnotes WHERE id=?", [$id]);
    return $result['message_status'];
}
