<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

function newEvent($event, $user, $groupname, $comments="") {
  $adodb = $GLOBALS['adodb']['db'];
  $sql = "insert into log ( date, event, user, groupname, comments ) " .
    "values ( NOW(), " . $adodb->qstr($event) . "," . $adodb->qstr($user) .
    "," . $adodb->qstr($groupname) . "," .$adodb->qstr($comments) . ")";
  return sqlInsertClean($sql);
}

function	getEventByDate($date, $user="",
  $cols="DISTINCT date, event, user, groupname, comments")
{
  $sql = "SELECT $cols FROM log WHERE date >= '$date 00:00:00' AND date <= '$date 23:59:59'";
  if ($user) $sql .= " AND user LIKE '$user'";
  $sql .= " ORDER BY date DESC LIMIT 5000";
  $res = sqlStatement($sql);
  for($iter=0; $row=sqlFetchArray($res); $iter++) {
    $all[$iter] = $row;
  }
  return $all;
}
?>
