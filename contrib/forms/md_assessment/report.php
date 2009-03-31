<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function md_assessment_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $data = formFetch("form_md_assessment", $id);
  $width = 100/$cols;
  if ($data) {
    print "<table><tr>";
    foreach($data as $key => $value) {
      if ($key == "id" || $key == "pid" || $key == "user" ||
        $key == "groupname" || $key == "authorized" || $key == "activity" ||
        $key == "date" || $value == "" || $value == "0000-00-00 00:00:00")
      {
        continue;
      }
      if ($value == "on") {
        $value = "yes";
      }
      $key=ucwords(str_replace("_"," ",$key));
      print "<td width='${width}%' valign='top'><span class=bold>$key: </span><span class=text>$value</span></td>";
      $count++;
      if ($count == $cols) {
        $count = 0;
        print "</tr><tr>\n";
      }
    }
  }
  print "</tr></table>";
}
?>
