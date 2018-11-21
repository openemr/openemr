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
  authorized = , activity=1, date = NOW(),
  provider          = ?,
  client_name          = ?,
  transfer_to          = ?,
  transfer_date          = ?,
  status_of_admission          = ?,
  diagnosis          =  ?,
  intervention_provided          =  ?,
  overall_status_of_discharge                    = ?";

  
if (empty($id)) {
    $newid = sqlInsert("INSERT INTO form_transfer_summary SET $sets",
    array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, add_escape_custom($_POST["provider"]), 
    add_escape_custom($_POST["client_name"]), add_escape_custom($_POST["transfer_to"]), add_escape_custom($_POST["transfer_date"]), 
    add_escape_custom($_POST["status_of_admission"]), add_escape_custom($_POST["diagnosis"]), add_escape_custom($_POST["intervention_provided"]), 
    add_escape_custom($_POST["overall_status_of_discharge"])));
    addForm($encounter, "Transfer Summary", $newid, "transfer_summary", $pid, $userauthorized);
} else {
    sqlStatement("UPDATE form_transfer_summary SET $sets WHERE id = ?", add_escape_custom("$id"));
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
