<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("{$GLOBALS['srcdir']}/sql.inc");

// 06-2009, BM migrated the patient_note_types array to the list_options table

function getPnoteById($id, $cols = "*")
{
  return sqlQuery("SELECT $cols FROM pnotes WHERE id=? " .
    " AND deleted != 1 ". // exclude ALL deleted notes
    "order by date DESC limit 0,1", array($id) );
}

function getPnotesByDate($date, $activity = "1", $cols = "*", $pid = "%",
  $limit = "all", $start = 0, $username = '', $docid = 0, $status = "")
{
$sqlParameterArray = array();
  if ($docid) {
    $sql = "SELECT $cols FROM pnotes AS p, gprelations AS r " .
    "WHERE p.date LIKE ? AND r.type1 = 1 AND " .
    "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid != p.user";
    array_push($sqlParameterArray, '%'.$date.'%', $docid);
  }
  else {
    $sql = "SELECT $cols FROM pnotes AS p " .
      "WHERE date LIKE ? AND pid LIKE ? AND p.pid != p.user";
    array_push($sqlParameterArray, '%'.$date.'%', $pid);
  }
  $sql .= " AND deleted != 1"; // exclude ALL deleted notes
  if ($activity != "all") {
    $sql .= " AND activity = ?";
    array_push($sqlParameterArray, $activity);
  }
  if ($username) {
    $sql .= " AND assigned_to LIKE ?";
    array_push($sqlParameterArray, $username);
  }
  if ($status)
    $sql .= " AND message_status IN ('".str_replace(",", "','", $status)."')";
  $sql .= " ORDER BY date DESC";
  if($limit != "all")
    $sql .= " LIMIT $start, $limit";

  $res = sqlStatement($sql, $sqlParameterArray);

  $all=array();
  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $all[$iter] = $row;
  return $all;
}

function getSentPnotesByDate($date, $activity = "1", $cols = "*", $pid = "%",
  $limit = "all", $start = 0, $username = '', $docid = 0, $status = "")
{
$sqlParameterArray = array();
  if ($docid) {
    $sql = "SELECT $cols FROM pnotes AS p, gprelations AS r " .
    "WHERE p.date LIKE ? AND r.type1 = 1 AND " .
    "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid = p.user";
    array_push($sqlParameterArray, '%'.$date.'%', $docid);
  }
  else {
    $sql = "SELECT $cols FROM pnotes AS p " .
      "WHERE date LIKE ? AND pid LIKE ? AND p.pid = p.user";
    array_push($sqlParameterArray, '%'.$date.'%', $pid);
  }
  $sql .= " AND deleted != 1"; // exclude ALL deleted notes
  if ($activity != "all") {
    $sql .= " AND activity = ?";
    array_push($sqlParameterArray, $activity);
  }
  if ($username) {
    $sql .= " AND assigned_to LIKE ?";
    array_push($sqlParameterArray, $username);
  }
  if ($status)
    $sql .= " AND message_status IN ('".str_replace(",", "','", $status)."')";
  $sql .= " ORDER BY date DESC";
  if($limit != "all")
    $sql .= " LIMIT $start, $limit";

  $res = sqlStatement($sql, $sqlParameterArray);

  $all=array();
  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $all[$iter] = $row;
  return $all;
}

function getPatientNotes($pid = '', $limit = '', $offset = 0, $search = '')
{
  if($limit){
    $limit = "LIMIT $offset, $limit";
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
      'Message' as `type`
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
  for($iter = 0;$row = sqlFetchArray($res);$iter++){
    $all[$iter] = $row;
  }
  return $all;
}

function getPatientNotifications($pid = '', $limit = '', $offset = 0, $search = '')
{
  if($limit){
    $limit = "LIMIT $offset, $limit";
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
  for($iter = 0;$row = sqlFetchArray($res);$iter++){
    $all[$iter] = $row;
  }
  return $all;
}

function getPatientSentNotes($pid = '', $limit = '', $offset = 0, $search = '')
{
  if($limit){
    $limit = "LIMIT $offset, $limit";
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
      $search
    ORDER BY `date` desc
    $limit
  ";
  $res = sqlStatement($sql, array($pid,$pid));
  for($iter = 0;$row = sqlFetchArray($res);$iter++){
    $all[$iter] = $row;
  }
  return $all;
}

function getPnotesByPid ($pid, $activity = "1", $cols = "*", $limit=10, $start=0)
{
  $res = sqlStatement("SELECT $cols FROM pnotes WHERE pid LIKE '$pid' " .
    "AND activity = '$activity' ".
    " AND deleted != 1 ".
    " ORDER BY date DESC LIMIT $start,$limit");
  for ($iter = 0; $row = sqlFetchArray($res); $iter++)
    $all[$iter] = $row;
  return $all;
}

function addPnote($pid, $newtext, $authorized = '0', $activity = '1',
  $title='Unassigned', $assigned_to = '', $datetime = '', $message_status = "New")
{
  if (empty($datetime)) $datetime = date('Y-m-d H:i:s');

  $body = date('Y-m-d H:i') . ' (' . $_SESSION['authUser'];
  if ($assigned_to) $body .= " to $assigned_to";
  $body = $body . ') ' . $newtext;

  return sqlInsert("INSERT INTO pnotes (date, body, pid, user, groupname, " .
    "authorized, activity, title, assigned_to, message_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    array($datetime, $body, $pid, $_SESSION['authUser'], $_SESSION['authProvider'], $authorized, $activity, $title, $assigned_to, $message_status) );
}

function addMailboxPnote($pid, $newtext, $authorized = '0', $activity = '1',
  $title='Unassigned', $assigned_to = '', $datetime = '', $message_status = "New")
{
  if (empty($datetime)) $datetime = date('Y-m-d H:i:s');

  $body = date('Y-m-d H:i') . ' (' . $pid;
  if ($assigned_to) $body .= " to $assigned_to";
  $body = $body . ') ' . $newtext;

  return sqlInsert("INSERT INTO pnotes (date, body, pid, user, groupname, " .
    "authorized, activity, title, assigned_to, message_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    array($datetime, $body, $pid, $pid, 'Default', $authorized, $activity, $title, $assigned_to, $message_status) );
}

function updatePnote($id, $newtext, $title, $assigned_to, $message_status = "")
{
  $row = getPnoteById($id);
  if (! $row) die("updatePnote() did not find id '$id'");
  $activity = $assigned_to ? '1' : '0';

  $body = $row['body'] . "\n" . date('Y-m-d H:i') .
    ' (' . $_SESSION['authUser'];
  if ($assigned_to) $body .= " to $assigned_to";
  $body = $body . ') ' . $newtext;

  if ($message_status) {
    sqlStatement("UPDATE pnotes SET " .
      "body = ?, activity = ?, title= ?, " .
      "assigned_to = ?, message_status = ? WHERE id = ?",
      array($body, $activity, $title, $assigned_to, $message_status, $id) );
  }
  else {
    sqlStatement("UPDATE pnotes SET " .
      "body = ?, activity = ?, title= ?, " .
      "assigned_to = ? WHERE id = ?",
      array($body, $activity, $title, $assigned_to, $id) );
  }
}

function updatePnoteMessageStatus($id, $message_status)
{
  sqlStatement("update pnotes set message_status = ? where id = ?", array($message_status, $id) );
}

function authorizePnote($id, $authorized = "1")
{
  sqlQuery("UPDATE pnotes SET authorized = '$authorized' WHERE id = '$id'");
}

function disappearPnote($id)
{
  sqlStatement("UPDATE pnotes SET activity = '0' WHERE id=?", array($id) );
  return true;
}

function reappearPnote ($id)
{
  sqlStatement("UPDATE pnotes SET activity = '1' WHERE id=?", array($id) );
  return true;
}

function deletePnote($id)
{
  sqlStatement("UPDATE pnotes SET deleted = '1' WHERE id=?", array($id) );
  return true;
}
?>
