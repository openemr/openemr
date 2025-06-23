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

use OpenEMR\Common\Logging\SystemLogger;

/**
 * Retrieve a note, given its ID
 *
 * @param string $id the ID of the note to retrieve.
 * @param string $cols A list of columns to retrieve. defaults to '*' for all.
 */
function getPnoteById($id, $cols = "*")
{
    return sqlQuery("SELECT "  . escape_sql_column_name(process_cols_escape($cols), array('pnotes')) . " FROM pnotes WHERE id=? " .
    ' AND deleted != 1 ' . // exclude ALL deleted notes
    'order by date DESC limit 0,1', array($id));
}

/**
 * Check a note, given its ID to see if it matches the user
 *
 * @param string $id the ID of the note to retrieve.
 * @param string $user the user seeking to view the note
 */
function checkPnotesNoteId(int $id, string $user): bool
{
    $check = sqlQuery("SELECT `id`, `user`, `assigned_to` FROM pnotes WHERE id = ? AND deleted != 1", array($id));
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
    $check = sqlQuery("SELECT `id` FROM users WHERE portal_user = 1 AND username = ? AND active = 1", array($user));
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
    $user_plug = '';
  // Set whether to show chosen user or all users
    if ($show_all == 'yes') {
        $usrvar = '_%';
    } else {
        if (checkPortalAuthUser($user)) {
            $user_plug = "|| pnotes.assigned_to = 'portal-user'";
        }
        $usrvar = $user;
    }

  // run the query
  // 2013-02-08 EMR Direct: minor changes to query so notes with pid=0 don't disappear
    $sql = "SELECT pnotes.id, pnotes.user, pnotes.pid, pnotes.title, pnotes.date, pnotes.message_status, pnotes.activity,
          IF(pnotes.pid = 0 OR pnotes.user != pnotes.pid,users.fname,patient_data.fname) as users_fname,
          IF(pnotes.pid = 0 OR pnotes.user != pnotes.pid,users.lname,patient_data.lname) as users_lname,
          patient_data.fname as patient_data_fname, patient_data.lname as patient_data_lname
          FROM ((pnotes LEFT JOIN users ON pnotes.user = users.username)
          LEFT JOIN patient_data ON pnotes.pid = patient_data.pid) WHERE $activity_query
          pnotes.deleted != '1' AND (pnotes.assigned_to LIKE ? $user_plug)";
    if (!empty($sortby) || !empty($sortorder)  || !empty($begin) || !empty($listnumber)) {
        $sql .= " order by " . escape_sql_column_name($sortby, array('users','patient_data','pnotes'), true) .
            " " . escape_sort_order($sortorder) .
            " limit " . escape_limit($begin) . ", " . escape_limit($listnumber);
    }

    $result = sqlStatement($sql, array($usrvar));

  // return the results
    if ($count) {
        if (sqlNumRows($result) != 0) {
            $total = sqlNumRows($result);
        } else {
            $total = 0;
        }

        return $total;
    } else {
        return $result;
    }
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

    $sqlParameterArray = array();
    if ($docid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), array('pnotes', 'gprelations')) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 1 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid != p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $docid);
    } elseif ($orderid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), array('pnotes', 'gprelations')) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 2 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid != p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $orderid);
    } else {
        $sql = "SELECT "  . escape_sql_column_name(process_cols_escape($cols), array('pnotes')) . " FROM pnotes AS p " .
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
        $sql .= " AND message_status IN ('" . str_replace(",", "','", add_escape_custom($status)) . "')";
    }

    $sql .= " ORDER BY date DESC";
    if ($limit != "all") {
        $sql .= " LIMIT " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $res = sqlStatement($sql, $sqlParameterArray);

    $all = array();
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
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

    $sqlParameterArray = array();
    if ($docid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), array('pnotes', 'gprelations')) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 1 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid = p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $docid);
    } elseif ($orderid) {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), array('pnotes','gprelations')) . " FROM pnotes AS p, gprelations AS r " .
        "WHERE p.date LIKE ? AND r.type1 = 2 AND " .
        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid = p.user";
        array_push($sqlParameterArray, '%' . $date . '%', $orderid);
    } else {
        $sql = "SELECT " . escape_sql_column_name(process_cols_escape($cols), array('pnotes')) . " FROM pnotes AS p " .
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
        $sql .= " AND message_status IN ('" . str_replace(",", "','", add_escape_custom($status)) . "')";
    }

    $sql .= " ORDER BY date DESC";
    if ($limit != "all") {
        $sql .= " LIMIT " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $res = sqlStatement($sql, $sqlParameterArray);

    $all = array();
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getPatientNotes($pid = '', $limit = '', $offset = 0, $search = '')
{
    if ($limit) {
        $limit = "LIMIT " . escape_limit($offset) . ", " . escape_limit($limit);
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
      p.activity
    FROM
      pnotes AS p
      LEFT JOIN patient_data AS pd
        ON pd.id = p.pid
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

function getPatientNotifications($pid = '', $limit = '', $offset = 0, $search = '')
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
        AND lo.list_id = 'rule_action_category' AND lo.activity = 1
      LEFT JOIN list_options AS lo2
        ON lo2.option_id = pr.item
        AND lo2.list_id = 'rule_action' AND lo2.activity = 1
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

function getPatientSentNotes($pid = '', $limit = '', $offset = 0, $search = '')
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
      REPLACE(
        p.body,
        '-patient-',
        CONCAT(pd.lname, ' ', pd.fname)
      ) AS body,
      p.activity,
      p.message_status,
      'Message' as `type`
    FROM
      pnotes AS p
      LEFT JOIN patient_data AS pd
        ON pd.id = p.pid
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



/** Add a note to a patient's medical record.
 *
 * @param int $pid the ID of the patient whos medical record this note is going to be attached to.
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

    if (empty($datetime)) {
        $datetime = date('Y-m-d H:i:s');
    }

  // make inactive if set as Done
    if ($message_status == 'Done') {
        $activity = 0;
    }
    $user = ($background_user != "" ? $background_user : $_SESSION['authUser']);
    $body = date('Y-m-d H:i') . ' (' . $user;
    if ($assigned_to) {
        $body .= " to $assigned_to";
    }

    $body = $body . ') ' . $newtext;

    return sqlInsert(
        'INSERT INTO pnotes (date, body, pid, user, groupname, ' .
        'authorized, activity, title, assigned_to, message_status, update_by, update_date) VALUES ' .
        '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
        array($datetime, $body, $pid, $user, ($_SESSION['authProvider'] ?? null), $authorized, $activity, $title, $assigned_to, $message_status, ($_SESSION['authUserID'] ?? null))
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

    return sqlInsert(
        "INSERT INTO pnotes (date, body, pid, user, groupname, " .
        "authorized, activity, title, assigned_to, message_status, update_by, update_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        array($datetime, $body, $pid, $pid, 'Default', $authorized, $activity, $title, $assigned_to, $message_status, $_SESSION['authUserID'])
    );
}

function updatePnote($id, $newtext, $title, $assigned_to, $message_status = "", $datetime = "")
{
    $row = getPnoteById($id);
    if (! $row) {
        die("updatePnote() did not find id '" . text($id) . "'");
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
    ' (' . $_SESSION['authUser'];
    if ($assigned_to) {
        $body .= " to $assigned_to";
    }

    $body = $body . ') ' . $newtext;


    $sql = "UPDATE pnotes SET " .
        "body = ?, activity = ?, title= ?, " .
        "assigned_to = ?, update_by = ?, update_date = NOW()";
    $bindingParams =  array($body, $activity, $title, $assigned_to, $_SESSION['authUserID']);
    if ($message_status) {
        $sql .= " ,message_status = ?";
        $bindingParams[] = $message_status;
    }
    if ($GLOBALS['messages_due_date']) {
        $sql .= " ,date = ?";
        $bindingParams[] = $datetime;
    }
    $sql .= " WHERE id = ?";
    $bindingParams[] = $id;
    sqlStatement($sql, $bindingParams);
}

function updatePnoteMessageStatus($id, $message_status)
{
    if ($message_status == "Done") {
        sqlStatement("update pnotes set message_status = ?, activity = '0', update_by = ?, update_date = NOW() where id = ?", array($message_status, $_SESSION['authUserID'], $id));
    } else {
        sqlStatement("update pnotes set message_status = ?, activity = '1', update_by = ?, update_date = NOW() where id = ?", array($message_status, $_SESSION['authUserID'], $id));
    }
}

/**
 * Set the patient id in an existing message where pid=0
 * @param $id the id of the existing note
 * @param $patient_id the patient id to associate with the note
 * @author EMR Direct <http://www.emrdirect.com/>
 */
function updatePnotePatient($id, $patient_id)
{
    $row = getPnoteById($id);
    if (! $row) {
        die("updatePnotePatient() did not find id '" . text($id) . "'");
    }

    $activity = $assigned_to ? '1' : '0';

    $pid = $row['pid'];

    if ($pid != 0 || (int)$patient_id < 1) {
        (new SystemLogger())->errorLogCaller("invalid operation", ['id' => $id, 'patient_id' => $patient_id, 'pid' => $pid]);
        die("updatePnotePatient invalid operation");
    }

    $pid = (int) $patient_id;
    $newtext = "\n" . date('Y-m-d H:i') . " (patient set by " . $_SESSION['authUser'] . ")";
    $body = $row['body'] . $newtext;

    sqlStatement("UPDATE pnotes SET pid = ?, body = ?, update_by = ?, update_date = NOW() WHERE id = ?", array($pid, $body, $_SESSION['authUserID'], $id));
}

function authorizePnote($id, $authorized = "1")
{
    sqlQuery("UPDATE pnotes SET authorized = ? , update_by = ?, update_date = NOW() WHERE id = ?", array ($authorized, $_SESSION['authUserID'], $id));
}

function disappearPnote($id)
{
    sqlStatement("UPDATE pnotes SET activity = '0', message_status = 'Done', update_by = ?, update_date = NOW()  WHERE id=?", array($_SESSION['authUserID'], $id));
    return true;
}

function reappearPnote($id)
{
    sqlStatement("UPDATE pnotes SET activity = '1', message_status = IF(message_status='Done','New',message_status), update_by = ?, update_date = NOW() WHERE id=?", array($_SESSION['authUserID'], $id));
    return true;
}

function deletePnote($id)
{
    $assigned = getAssignedToById($id);
    if (!checkPortalAuthUser($_SESSION['authUser']) && $assigned == 'portal-user') {
        return false;
    }
    if (
        $assigned == $_SESSION['authUser']
        || $assigned == 'portal-user'
        || getMessageStatusById($id) == 'Done'
    ) {
        sqlStatement("UPDATE pnotes SET deleted = '1', update_by = ?, update_date = NOW() WHERE id=?", array($_SESSION['authUserID'], $id));
        return true;
    } else {
        return false;
    }
}

// Note that it is assumed that html escaping has happened before this function is called
function pnoteConvertLinks($note)
{
    $noteActiveLink = preg_replace('!(https://[-a-zA-Z()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank" rel="noopener">$1</a>', $note);
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
    $result = sqlQuery("SELECT assigned_to FROM pnotes WHERE id=?", array($id));
    return $result['assigned_to'];
}

/**
 * Retrieve message_status field given the note ID
 *
 * @param string $id the ID of the note to retrieve.
 */
function getMessageStatusById($id)
{
    $result = sqlQuery("SELECT message_status FROM pnotes WHERE id=?", array($id));
    return $result['message_status'];
}
