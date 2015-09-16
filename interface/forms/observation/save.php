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
// Author:   Jacob T Paul <jacob@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes = $_POST['true'];

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = $_POST['false'];

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
require_once("$srcdir/formdata.inc.php");

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$id             = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$code           = $_POST["code"];
$code_obs       = $_POST["comments"];
$code_desc      = $_POST["description"];
$code_type      = $_POST["code_type"];
$table_code     = $_POST["table_code"];
$ob_value       = $_POST["ob_value"];
$ob_value_phin  = $_POST["ob_value_phin"];
$ob_unit        = $_POST["ob_unit"];
$code_date      = $_POST["code_date"];

if ($id && $id != 0) {
    sqlStatement("DELETE FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?", array($id, $_SESSION["pid"], $_SESSION["encounter"]));
    $newid = $id;
} else {
    $res2 = sqlStatement("SELECT MAX(id) as largestId FROM `form_observation`");
    $getMaxid = sqlFetchArray($res2);
    if ($getMaxid['largestId']) {
        $newid = $getMaxid['largestId'] + 1;
    } else {
        $newid = 1;
    }
    addForm($encounter, "Observation Form", $newid, "observation", $_SESSION["pid"], $userauthorized);
}


$code_desc = array_filter($code_desc);
if (!empty($code_desc)) {
    foreach ($code_desc as $key => $codeval):
      if($code[$key] == 'SS003') {
        $ob_value[$key] = $ob_value_phin[$key];
        $ob_unit_value = "";
      }
      elseif($code[$key] == '8661-1') {
        $ob_unit_value = "";
      }
      elseif($code[$key] == '21612-7') {
         if(! empty($ob_unit)) {
           foreach ($ob_unit as $key1 => $val):
             if($key1 == 0)
              $ob_unit_value = $ob_unit[$key1];
             else {
               if($key1 == $key)
                 $ob_unit_value = $ob_unit[$key1];
             }
           endforeach;
         }
      }
        $sets = "id     = ". add_escape_custom($newid) .",
            pid         = ". add_escape_custom($_SESSION["pid"]) .",
            groupname   = '" . add_escape_custom($_SESSION["authProvider"]) . "',
            user        = '" . add_escape_custom($_SESSION["authUser"]) . "',
            encounter   = '" . add_escape_custom($_SESSION["encounter"]) . "',
            authorized  = ". add_escape_custom($userauthorized) .", 
            activity    = 1,
            observation = '" . add_escape_custom($code_obs[$key]) . "',
            code        = '" . add_escape_custom($code[$key]) . "',
            code_type   = '" . add_escape_custom($code_type[$key]) . "',
            description = '" . add_escape_custom($code_desc[$key]) . "',
            table_code  = '" . add_escape_custom($table_code[$key]) . "',
            ob_value    = '" . add_escape_custom($ob_value[$key]) . "',
            ob_unit     = '" . add_escape_custom($ob_unit_value) . "',
            date        = '" . add_escape_custom($code_date[$key]) . "'";
        sqlInsert("INSERT INTO form_observation SET $sets");
    endforeach;
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
