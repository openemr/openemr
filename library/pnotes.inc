<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("{$GLOBALS['srcdir']}/sql.inc");

// 06-2009, BM migrated the patient_note_types array to the list_options table

function getPnoteById($id, $cols = "*")
{
  return sqlQuery("SELECT $cols FROM pnotes WHERE id='$id' " .
    " AND deleted != 1 ". // exclude ALL deleted notes
    "order by date DESC limit 0,1");
}

function getPnotesByDate($date, $activity = "1", $cols = "*", $pid = "%",
  $limit = "all", $start = 0, $username = '', $docid = 0)
{
  if ($docid) {
    $sql = "SELECT $cols FROM pnotes AS p, gprelations AS r " .
    "WHERE p.date LIKE '%$date%' AND r.type1 = 1 AND " .
    "r.id1 = '$docid' AND r.type2 = 6 AND p.id = r.id2";
  }
  else {
    $sql = "SELECT $cols FROM pnotes AS p " .
      "WHERE date LIKE '%$date%' AND pid LIKE '$pid'";
  }
  $sql .= " AND deleted != 1"; // exclude ALL deleted notes
  if ($activity != "all")
    $sql .= " AND activity = '$activity'";
  if ($username)
    $sql .= " AND assigned_to LIKE '$username'";
  $sql .= " ORDER BY date DESC";
  if($limit != "all")
    $sql .= " LIMIT $start, $limit";

  $res = sqlStatement($sql);

  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $all[$iter] = $row;
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
  $title='Unassigned', $assigned_to = '', $datetime = '')
{
  if (empty($datetime)) $datetime = date('Y-m-d H:i:s');

  $body = date('Y-m-d H:i') . ' (' . $_SESSION['authUser'];
  if ($assigned_to) $body .= " to $assigned_to";
  $body = addslashes($body . ') ' . $newtext);

  return sqlInsert("INSERT INTO pnotes (date, body, pid, user, groupname, " .
    "authorized, activity, title, assigned_to) VALUES ('$datetime', '$body', '$pid', '" .
    $_SESSION['authUser'] . "', '". $_SESSION['authProvider'] .
    "', '$authorized', '$activity', '$title', '$assigned_to')");
}

function updatePnote($id, $newtext, $title, $assigned_to)
{
  $row = getPnoteById($id);
  if (! $row) die("updatePnote() did not find id '$id'");
  $activity = $assigned_to ? '1' : '0';

  $body = $row['body'] . "\n" . date('Y-m-d H:i') .
    ' (' . $_SESSION['authUser'];
  if ($assigned_to) $body .= " to $assigned_to";
  $body = addslashes($body . ') ' . $newtext);

  sqlStatement("UPDATE pnotes SET " .
    "body = '$body', activity = '$activity', title='$title', " .
    "assigned_to = '$assigned_to' WHERE id = '$id'");
}

function authorizePnote($id, $authorized = "1")
{
  sqlQuery("UPDATE pnotes SET authorized = '$authorized' WHERE id = '$id'");
}

function disappearPnote($id)
{
  sqlStatement("UPDATE pnotes SET activity = '0' WHERE id='$id'");
  return true;
}

function reappearPnote ($id)
{
  sqlStatement("UPDATE pnotes SET activity = '1' WHERE id='$id'");
  return true;
}

function deletePnote($id)
{
  sqlStatement("UPDATE pnotes SET deleted = '1' WHERE id='$id'");
  return true;
}
?>
