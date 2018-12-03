<?php
/**
 *
 * Copyright (C) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Naina Mohamed <naina@capminds.com>
 * @link    http://www.open-emr.org
 */
 

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

if (! $encounter) { // comes from globals.php
    die(xl("Internal error: we do not seem to be in an encounter!"));
}

$id = 0 + (isset($_GET['id']) ? $_GET['id'] : '');

$sets = "pid = ?,
  groupname = ?,
  user = ?,
  authorized = ?, activity=1, date = NOW(),
  provider               = ?,
  client_name            = ?,
  client_number          = ?,
  admit_date             =  ?,
  presenting_issues          = ?,
  patient_history            =  ?,
  medications                = ?,
  anyother_relevant_information          = ?,
  diagnosis                    = ?,
  treatment_received           = ?,
  recommendation_for_follow_up                    = ?";
  

  
if (empty($id)) {
    $newid = sqlInsert("INSERT INTO form_treatment_plan SET $sets",
    array($_SESSION["pid"]), $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, add_escape_custom($_POST["provider"]), add_escape_custom($_POST["client_name"]),
    add_escape_custom($_POST["client_number"]), add_escape_custom($_POST["admit_date"]), add_escape_custom($_POST["presenting_issues"]), add_escape_custom($_POST["patient_history"]), 
    add_escape_custom($_POST["medications"]), add_escape_custom($_POST["anyother_relevant_information"]), add_escape_custom($_POST["diagnosis"]),
    add_escape_custom($_POST["treatment_received"]), add_escape_custom($_POST["recommendation_for_follow_up"]));
    
    addForm($encounter, "Treatment Plan", $newid, "treatment_plan", $pid, $userauthorized);
} else {
    sqlStatement("UPDATE form_treatment_plan SET $sets WHERE id = ?", array(add_escape_custom("$id")));
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
