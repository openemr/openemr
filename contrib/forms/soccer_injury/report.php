<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");

// This function is invoked from printPatientForms in report.inc
// when viewing a "comprehensive patient report".
//
function soccer_injury_report( $pid, $encounter, $cols, $id) {
 $row = sqlQuery ("SELECT forms.date, si.siinjtime " .
  "FROM forms, form_soccer_injury AS si WHERE " .
  "forms.formdir = 'soccer_injury' AND forms.form_id = '$id' AND " .
  "si.id = '$id' AND si.activity = '1'");

 echo "<span class='text'>";
 echo " Occurred " . substr($row['date'], 0, 10) . " " . substr($row['siinjtime'], 0, 5);
 echo "</span><br>\n";
}
?>
