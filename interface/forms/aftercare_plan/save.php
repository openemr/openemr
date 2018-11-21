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
  provider          =  ?,
  client_name          = ?,
  admit_date          = ?,
  discharged          = ?,
  goal_a_acute_intoxication          =  ?,
  goal_a_acute_intoxication_I          = ?,
  goal_a_acute_intoxication_II          = ?,
  goal_b_emotional_behavioral_conditions          = ?,
  goal_b_emotional_behavioral_conditions_I          = ?,
  goal_c_relapse_potential                           = ?,
  goal_c_relapse_potential_I                    =  ?;";

  
if (empty($id)) {
    $newid = sqlInsert("INSERT INTO form_aftercare_plan SET $sets", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, add_escape_custom($_POST["provider"]),
    add_escape_custom($_POST["client_name"]), add_escape_custom($_POST["admit_date"]), add_escape_custom($_POST["discharged"]), add_escape_custom($_POST["goal_a_acute_intoxication"]),
    add_escape_custom($_POST["goal_a_acute_intoxication_I"]), add_escape_custom($_POST["goal_a_acute_intoxication_II"]), add_escape_custom($_POST["goal_b_emotional_behavioral_conditions"]),
    add_escape_custom($_POST["goal_b_emotional_behavioral_conditions_I"]), add_escape_custom($_POST["goal_c_relapse_potential"]), add_escape_custom($_POST["goal_c_relapse_potential_I"])));
    
    addForm($encounter, "Aftercare Plan", $newid, "aftercare_plan", $pid, $userauthorized);
} else {
    sqlStatement("UPDATE form_aftercare_plan SET $sets WHERE id = ?;", array(add_escape_custom("$id")));
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
