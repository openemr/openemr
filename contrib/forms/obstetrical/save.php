<?php

//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_obstetrical", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Obstetrical Form", $newid, "obstetrical", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_obstetrical set pid = ?,groupname= ?, user= ?,authorized= ?, activity= 1, date = NOW(), name= ?, birthdate= ?, feeding= ?,
    birth_status= ?, gender= ?, circumcised= ?, delivery_method= ?, labor_hours= ?, birth_weight= ?, pregnancy_weeks= ?,
    anesthesia= ?, pediatrician= ?, length_inches= ?, head_circumference_inches= ?, reactions_to_medications_and_immunizations= ?, birth_complications= ?,
    developmental_problems= ?, chronic_illness= ?, chronic_medication= ?, hospitalization= ?, surgery= ?,
    injury= ?, day_care= ?, additional_notes= ? WHERE id= ?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["name"], $_POST["birthdate"], $_POST["feeding"], $_POST["birth_status"], $_POST["gender"], $_POST["circumcised"], $_POST["delivery_method"],
    $_POST["labor_hours"], $_POST["birth_weight"], $_POST["pregnancy_weeks"], $_POST["anesthesia"], $_POST["pediatrician"], $_POST["length_inches"], $_POST["head_circumference_inches"], $_POST["reactions_to_medications_and_immunizations"], $_POST["birth_complications"],
    $_POST["developmental_problems"], $_POST["chronic_illness"], $_POST["chronic_medication"], $_POST["hospitalization"], $_POST["injury"], $_POST["day_care"], $_POST["additional_notes"], $id));
}


formHeader("Redirecting....");
formJump();
formFooter();
