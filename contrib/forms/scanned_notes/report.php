<?php
// Copyright (C) 2006-2012 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

function scanned_notes_report($pid, $useless_encounter, $cols, $id) {
 global $webserver_root, $web_root, $encounter;

 // In the case of a patient report, the passed encounter is vital.
 $thisenc = $useless_encounter ? $useless_encounter : $encounter;

 $count = 0;

 $data = sqlQuery("SELECT * " .
  "FROM form_scanned_notes WHERE " .
  "id = '$id' AND activity = '1'");

 if ($data) {
  if ($data['notes']) {
   echo "  <span class='bold'>Comments: </span><span class='text'>";
   echo nl2br($data['notes']) . "</span><br />\n";
  }

  for ($i = -1; true; ++$i) {
    $suffix = ($i < 0) ? "" : "-$i";
    $imagepath = $GLOBALS['OE_SITE_DIR'] .
      "/documents/$pid/encounters/${thisenc}_$id$suffix.jpg";
    $imageurl  = "$web_root/sites/" . $_SESSION['site_id'] .
      "/documents/$pid/encounters/${thisenc}_$id$suffix.jpg";
    if (is_file($imagepath)) {
      echo "   <img src='$imageurl'";
      // Flag images with excessive width for possible stylesheet action.
      $asize = getimagesize($imagepath);
      if ($asize[0] > 750) {
        echo " class='bigimage'";
      }
      echo " />\n";
      echo " <br />\n";
    }
    else {
      if ($i >= 0) break;
    }
  }
 }
}
?>
