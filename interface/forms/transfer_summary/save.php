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
 
  //SANITIZE ALL ESCAPES
 $sanitize_all_escapes=$_POST['true'];

 //STOP FAKE REGISTER GLOBALS
 $fake_register_globals=$_POST['false'];
  
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

if (! $encounter) { // comes from globals.php
 die(xl("Internal error: we do not seem to be in an encounter!"));
}


$id = 0 + (isset($_GET['id']) ? $_GET['id'] : '');

$sets = "pid = {$_SESSION["pid"]},
  groupname = '" . $_SESSION["authProvider"] . "',
  user = '" . $_SESSION["authUser"] . "',
  authorized = $userauthorized, activity=1, date = NOW(),
  provider          = '" . add_escape_custom(formData("provider")) . "',
  client_name          = '" . add_escape_custom(formData("client_name")) . "',
  transfer_to          = '" . add_escape_custom(formData("transfer_to")) . "',
  transfer_date          = '" . add_escape_custom(formData("transfer_date")) . "',
  status_of_admission          = '" . add_escape_custom(formData("status_of_admission")) . "',
  diagnosis          =  '" . add_escape_custom(formData("diagnosis")) . "',
  intervention_provided          =  '" . add_escape_custom(formData("intervention_provided")) . "',
  overall_status_of_discharge                    = '" . add_escape_custom(formData("overall_status_of_discharge")) ."'";

  
  if (empty($id)) {
  $newid = sqlInsert("INSERT INTO form_transfer_summary SET $sets");
  addForm($encounter, "Transfer Summary", $newid, "transfer_summary", $pid, $userauthorized);
}
else {
  sqlStatement("UPDATE form_transfer_summary SET $sets WHERE id = '". add_escape_custom("$id"). "'");
}

$_SESSION["encounter"] = htmlspecialchars($encounter);
formHeader("Redirecting....");
formJump();
formFooter();
?>

