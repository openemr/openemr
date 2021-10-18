<?php

/**
 * Health Questionnaire 9 form save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nilesh B Hake <nbhbiotech.hake@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2020 NBH Health Soft <nbhbiotech.hake@gmail.com>
 * @copyright Copyright (c) 2018-2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");


if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

if(!isset($_POST["unabletoevaluate"])){
	$_POST["unabletoevaluate"] = "";
}

$formid = $_POST["id"];
if(!$_POST["id"]){
	unset($_POST["id"]);
}

$columns = '';
$values = '';
$updateStmt = ' ON DUPLICATE KEY UPDATE ';

foreach($_POST as $key => $value){
	$columns .= '`' . add_escape_custom($key) . '`, ';
	$values .= "'" . add_escape_custom($value) . '\', ';
	//if($key != 'patientID' and $key != 'encounterID') {
		$updateStmt .=  add_escape_custom($key) . "=" . "'" . add_escape_custom($value) . '\', ';
	//}
}
$columns = substr($columns, 0, -2);
$values = substr($values, 0, -2);
$updateStmt = substr($updateStmt, 0, -2);
$preadmissioninsertupd = 'INSERT INTO `form_health_questionnaire_9` ('.$columns.') VALUES ('.$values.')' . $updateStmt;
$newid = sqlInsert($preadmissioninsertupd);

if(!$formid){
	addForm($encounter, "Health Questionnaire 9", $newid, "health_questionnaire_9", $_SESSION["pid"], $userauthorized);
}
//header('Location: new.php?pid='.$_POST['pid'].'&encounter='.$_POST['encounter']);
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();