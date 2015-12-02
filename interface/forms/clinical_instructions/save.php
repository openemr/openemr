<?php

// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Jacob Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+
//SANITIZE ALL ESCAPES
$sanitize_all_escapes = $_POST['true'];

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = $_POST['false'];

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$id = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$instruction = $_POST["instruction"];

if ($id && $id != 0) {
    sqlInsert("UPDATE form_clinical_instructions SET instruction =? WHERE id = ?", array($instruction, $id));
} else {
    $newid = sqlInsert("INSERT INTO form_clinical_instructions (pid,encounter,user,instruction) VALUES (?,?,?,?)", array($pid, $encounter, $_SESSION['authUser'], $instruction));
    addForm($encounter, "Clinical Instructions", $newid, "clinical_instructions", $pid, $userauthorized);
}
formHeader("Redirecting....");
formJump();
formFooter();
?>

