<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

// This function is invoked from printPatientForms in report.inc
// when viewing a "comprehensive patient report".  Also from
// interface/patient_file/encounter/forms.php.
//
function lbf_report($pid, $encounter, $cols, $id, $formname) {
  require_once($GLOBALS["srcdir"] . "/options.inc.php");
  echo "<table>\n";

  $arr = array();
  $fres = sqlStatement("SELECT field_id, field_value FROM lbf_data WHERE form_id = ?", array($id) );
  while ($frow = sqlFetchArray($fres)) {
    $arr[$frow['field_id']] = $frow['field_value'];
  }
  display_layout_rows($formname, $arr);

  echo "</table>\n";
}
?>
