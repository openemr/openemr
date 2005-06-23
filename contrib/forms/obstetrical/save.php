<?php
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
foreach ($_POST as $k => $var) {
$_POST[$k] = mysql_escape_string($var);
echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_obstetrical", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Obstetrical Form", $newid, "obstetrical", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_obstetrical set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), name='".$_POST["name"]."', birthdate='".$_POST["birthdate"]."', feeding='".$_POST["feeding"]."', birth_status='".$_POST["birth_status"]."', gender='".$_POST["gender"]."', circumcised='".$_POST["circumcised"]."', delivery_method='".$_POST["delivery_method"]."', labor_hours='".$_POST["labor_hours"]."', birth_weight='".$_POST["birth_weight"]."', pregnancy_weeks='".$_POST["pregnancy_weeks"]."', anesthesia='".$_POST["anesthesia"]."', pediatrician='".$_POST["pediatrician"]."', length_inches='".$_POST["length_inches"]."', head_circumference_inches='".$_POST["head_circumference_inches"]."', reactions_to_medications_and_immunizations='".$_POST["reactions_to_medications_and_immunizations"]."', birth_complications='".$_POST["birth_complications"]."', developmental_problems='".$_POST["developmental_problems"]."', chronic_illness='".$_POST["chronic_illness"]."', chronic_medication='".$_POST["chronic_medication"]."', hospitalization='".$_POST["hospitalization"]."', surgery='".$_POST["surgery"]."', injury='".$_POST["injury"]."', day_care='".$_POST["day_care"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
