<?php
// Copyright (C) 2009, 2012 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/lists.inc");

$plid = $_GET['plid'] + 0; // pid
$s = '';

if (isset($_GET['date'])) {
  $date = $_GET['date'];
  $date = substr($date,0,4) . '-' . substr($date,4,2) . '-' . substr($date,6,2);
  // get patient notes
  $res = sqlStatement("SELECT title, body FROM pnotes WHERE " .
    "pid = '$plid' AND LEFT(date,10) = '$date' AND " .
    "title LIKE 'Roster' AND deleted = 0 ORDER BY date LIMIT 1");
  while ($row = sqlFetchArray($res)) {
    $s .= nl2br(htmlentities($row['body'],ENT_QUOTES)) . "<br />";
  }
  if ($s === '') $s = "No notes";
}
else {
  // get issues
  $res = sqlStatement("SELECT type, title FROM lists WHERE pid = '$plid' " .
    "AND activity = 1 ORDER BY type, begdate");
  while ($row = sqlFetchArray($res)) {
    $s .= $ISSUE_TYPES[$row['type']][1] . ": " .
      htmlentities($row['title'],ENT_QUOTES) . "<br />";
  }
  if ($s === '') $s = "No issues";
}

// $s = str_replace("\r", "", $s);
// $s = str_replace("\n", "", $s);
// echo "ttCallback('$s');\n";

echo $s;
?>
