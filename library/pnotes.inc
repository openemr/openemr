<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

function getPnoteById($id, $cols = "*")
{
  return sqlQuery("SELECT $cols FROM pnotes WHERE id='$id' " .
    "order by date DESC limit 0,1");
}

function getPnotesByDate($date, $activity = "1", $cols = "*", $pid = "%",
  $limit = "all", $start = 0, $username = '')
{
  $sql = "SELECT $cols FROM pnotes WHERE date LIKE '%$date%' " .
    "AND pid LIKE '$pid' ";
  if ($activity != "all")
    $sql .= "AND activity = '$activity' ";
  if ($username)
    $sql .= "AND assigned_to = '$username' ";
  $sql .= "ORDER BY date DESC";
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
    "AND activity = '$activity' ORDER BY date DESC LIMIT $start,$limit");
  for ($iter = 0; $row = sqlFetchArray($res); $iter++)
    $all[$iter] = $row;
  return $all;
}

function addPnote($pid, $newtext, $authorized = '0', $activity = '1',
  $title='Unassigned', $assigned_to = '')
{
  $body = date('Y-m-d H:i') . ' (' . $_SESSION['authUser'];
  if ($assigned_to) $body .= " to $assigned_to";
  $body = addslashes($body . ') ' . $newtext);

  return sqlInsert("INSERT INTO pnotes (date, body, pid, user, groupname, " .
    "authorized, activity, title, assigned_to) VALUES (NOW(), '$body', '$pid', '" .
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
?>
