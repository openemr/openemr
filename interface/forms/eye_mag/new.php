<?php
/** 
* forms/eye_mag/new.php 
* 
* The page shown when the user requests a new form
*
* Copyright (C) 2010-14 Raymond Magauran <magauran@MedFetch.com> 
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
* @author Ray Magauran <magauran@MedFetch.com> 
* @link http://www.open-emr.org 
*/
$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$form_name = "eye_mag";
$table_name = "form_eye_mag";
$form_folder = "eye_mag";
include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
formHeader("Form: ".$form_name);
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
@extract($_REQUEST); 
@extract($_SESSION); 

if (!$user) $user = $_SESSION['authUser'];
if (!$group) $group = $_SESSION['authProvider'];

if (!$encounter) $encounter = date("Ymd");
$query = "select * from form_encounter where pid ='$pid' and encounter= '$encounter'";
$encounter_data = sqlQuery($query);
$encounter_date = $encounter_data[date];

$query = "SELECT * " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = ? AND fe.date = ? AND " .
    "f.formdir = 'eye_mag' AND f.encounter = fe.encounter AND f.deleted = 0";
$erow = sqlQuery($query,array($pid,$encounter_date));
    
if ($erow['form_id'] > '0') {
    formHeader("Redirecting....");
    formJump('./view_form.php?formname='.$form_name.'&id='.$erow['form_id'].'&pid='.$pid);
    formFooter();
    exit;
}  else {
    $id = $erow2['count']++;
    $newid = formSubmit($table_name, $_POST, $id, $userauthorized); 
    $sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
            "user, groupname, authorized, formdir) values (";
    $sql .= "'$encounter_date'";
    $sql .= ", '$encounter','$form_name','$newid', '$pid', '$user', '$group', '$authorized', '$form_folder')";
    $answer = sqlInsert( $sql );
 }
    formHeader("Redirecting....");
    formJump('./view_form.php?formname='.$form_name.'&id='.$newid.'&pid='.$pid);
    formFooter();
    exit;
?>

