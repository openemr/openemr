<?php

// Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

$ci_report_cols  = 2;
$ci_report_colno = 0;

// Helper function used by cricket_injury_audit_report().
// Writes a title/value pair to a table cell.
//
function ci_report_item($title, $value) {
 global $ci_report_cols, $ci_report_colno;
 if (!$value) return;
 if (++$ci_report_colno > $ci_report_cols) {
  $ci_report_colno = 1;
  echo " </tr>\n <tr>\n";
 }
 echo "  <td valign='top'><span class='bold'>$title: </span>" .
  "<span class='text'>$value &nbsp;</span></td>\n";
}

// This function is invoked from printPatientForms in report.inc
// when viewing a "comprehensive patient report".  Also from
// interface/patient_file/encounter/forms.php.
//
function cricket_injury_audit_report($pid, $encounter, $cols, $id) {
 global $ci_report_cols;

 include('cia.inc.php');

 $row = sqlQuery ("SELECT form_encounter.onset_date AS occdate, ci.* " .
  "FROM forms, form_encounter, form_cricket_injury_audit AS ci WHERE " .
  "forms.formdir = 'cricket_injury_audit' AND " .
  "forms.form_id = '$id' AND " .
  "ci.id = '$id' AND ci.activity = '1' AND " .
  "form_encounter.encounter = forms.encounter AND " .
  "form_encounter.pid = forms.pid");

 if (!$row) return;

 $ci_report_cols = $cols;

 echo "<table cellpadding='0' cellspacing='0'>\n";
 echo " <tr>\n";

 ci_report_item("County"    , $arr_county[$row['cicounty']]);
 ci_report_item("Team"      , $arr_team[$row['citeam']]);
 ci_report_item("Duration"  , $arr_duration[$row['ciduration']]);
 ci_report_item("Role"      , $arr_role[$row['cirole']]);
 ci_report_item("Injured In", $arr_matchtype[$row['cimatchtype']]);
 ci_report_item("Cause"     , $arr_cause[$row['cicause']]);
 ci_report_item("Activity"  , $arr_activity[$row['ciactivity']]);
 ci_report_item("Bat Side"  , $arr_batside[$row['cibatside']]);
 ci_report_item("Bowl Side" , $arr_bowlside[$row['cibowlside']]);
 ci_report_item("Bowl Type" , $arr_bowltype[$row['cibowltype']]);

 echo " </tr>\n";
 echo "</table>\n";
}
?>
