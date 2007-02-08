<?php

// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

$fi_report_cols  = 2;
$fi_report_colno = 0;

// Helper function used by football_injury_audit_report().
// Writes a title/value pair to a table cell.
//
function fi_report_item($title, $value) {
 global $fi_report_cols, $fi_report_colno;
 if (!$value) return;
 if (++$fi_report_colno > $fi_report_cols) {
  $fi_report_colno = 1;
  echo " </tr>\n <tr>\n";
 }
 echo "  <td valign='top'><span class='bold'>$title: </span>" .
  "<span class='text'>$value &nbsp;</span></td>\n";
}

// This function is invoked from printPatientForms in report.inc
// when viewing a "comprehensive patient report".  Also from
// interface/patient_file/encounter/forms.php.
//
function football_injury_audit_report($pid, $encounter, $cols, $id) {
 global $fi_report_cols;

 include('fia.inc.php');

 $row = sqlQuery ("SELECT form_encounter.onset_date AS occdate, fi.* " .
  "FROM forms, form_encounter, form_football_injury_audit AS fi WHERE " .
  "forms.formdir = 'football_injury_audit' AND " .
  "forms.form_id = '$id' AND " .
  "fi.id = '$id' AND fi.activity = '1' AND " .
  "form_encounter.encounter = forms.encounter AND " .
  "form_encounter.pid = forms.pid");

 if (!$row) return;

 $fi_report_cols = $cols;

 echo "<table cellpadding='0' cellspacing='0'>\n";
 echo " <tr>\n";

 if ($row['fiinjmin'] ) fi_report_item("Min of Injury", $row['fiinjmin']);
 if ($row['fiinjtime']) fi_report_item("During", $arr_injtime[$row['fiinjtime']]);
 if ($row['fimatchtype']) fi_report_item("Match Type", $arr_match_type[$row['fimatchtype']]);
 foreach ($arr_activity as $key => $value) {
  if ($row["fimech_$key"]) fi_report_item("Mechanism", $value);
 }
 foreach ($arr_sanction as $key => $value) {
  if ($row["fimech_$key"]) fi_report_item("Sanction", $value);
 }
 if ($row["fimech_othercon"]) fi_report_item("Other Contact", $row["fimech_othercon"]);
 if ($row["fimech_othernon"]) fi_report_item("Other Noncontact", $row["fimech_othernon"]);
 fi_report_item("Surface"  , $arr_surface[$row['fisurface']]);
 fi_report_item("Position" , $arr_position[$row['fiposition']]);
 fi_report_item("Footwear" , $arr_footwear[$row['fifootwear']]);
 fi_report_item("Side"     , $arr_side[$row['fiside']]);
 fi_report_item("Removed"  , $arr_removed[$row['firemoved']]);

 echo " </tr>\n";
 echo "</table>\n";
}
?>
