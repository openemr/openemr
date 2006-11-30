<?php
// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

function scanned_notes_report($pid, $useless_encounter, $cols, $id) {
 global $webserver_root, $web_root, $encounter;

 $count = 0;

 $data = sqlQuery("SELECT * " .
  "FROM form_scanned_notes WHERE " .
  "id = '$id' AND activity = '1'");

 if ($data) {
  $imagepath = "$webserver_root/documents/$pid/encounters/${encounter}_$id.jpg";
  $imageurl  = "$web_root/documents/$pid/encounters/${encounter}_$id.jpg";

  // echo "<!-- Image path is '$imagepath'. -->\n";

  echo "<table cellpadding='0' cellspacing='0'>\n";

  if ($data['notes']) {
   echo " <tr>\n";
   echo "  <td valign='top'><span class='bold'>Comments: </span><span class='text'>";
   echo nl2br($data['notes']) . "</span></td>\n";
   echo " </tr>\n";
  }

  if (is_file($imagepath)) {
   echo " <tr>\n";
   echo "  <td valign='top'>\n";
   // Gecko does the right thing with this width, but IE ignores it:
   // echo "   <img src='$imageurl' style='width:100%' />\n";
   echo "   <img src='$imageurl' />\n";
   echo "  </td>\n";
   echo " </tr>\n";
  }

  print "</table>\n";
 }
}
?>
