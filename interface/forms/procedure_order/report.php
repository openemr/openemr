<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
include_once($GLOBALS["srcdir"] . "/options.inc.php");

function procedure_order_report($pid, $encounter, $cols, $id) {
 $cols = 1; // force always 1 column
 $count = 0;
 $data = sqlQuery("SELECT * " .
  "FROM procedure_order WHERE " .
  "procedure_order_id = '$id' AND activity = '1'");
 if ($data) {
  print "<table cellpadding='0' cellspacing='0'>\n<tr>\n";
  foreach($data as $key => $value) {
   if ($key == "procedure_order_id" || $key == "pid" || $key == "user" || $key == "groupname" ||
       $key == "authorized" || $key == "activity" || $key == "date" ||
       $value == "" || $value == "0" || $value == "0.00") {
    continue;
   }

   $key=ucwords(str_replace("_"," ",$key));
   if ($key == "Order Priority") {
    print "<td valign='top'><span class='bold'>" . xl($key). ": </span><span class='text'>" .
     generate_display_field(array('data_type'=>'1','list_id'=>'ord_priority'),$value) .
     " &nbsp;</span></td>\n";
   }
   else if ($key == "Order Status") {
    print "<td valign='top'><span class='bold'>" . xl($key). ": </span><span class='text'>" .
     generate_display_field(array('data_type'=>'1','list_id'=>'ord_status'),$value) .
     " &nbsp;</span></td>\n";
   }
   else {
    print "<td valign='top'><span class='bold'>" . xl($key). ": </span><span class='text'>$value &nbsp;</span></td>\n";   
   }
   $count++;
   if ($count == $cols) {
    $count = 0;
    print "</tr>\n<tr>\n";
   }
  }
  print "</tr>\n</table>\n";
 }
}
?>
