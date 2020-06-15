<?php

//

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_ped_pain", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Pediatric Pain Evaluation", $newid, "ped_pain", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_ped_fever set pid = ?,
	groupname = ?,
	user = ?
	authorized =,
	activity =1,
	date = NOW(),
	location = ?,
	duration = ?,
	severity = ?,
	fever = ?,
	lethargy = ?,
	vomiting = ?,
	oral_hydration_capable = ?,
	urine_output_last_6_hours = ?,
	pain_with_urination = ?,
	cough_or_breathing_difficulty = ?,
	able_to_sleep = ?,
	nasal_discharge = ?
	previous_hospitalization = ?,
	siblings_affected = ?,
	immunization_up_to_date = ?,
	notes = ?,
	WHERE id = ?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["location"], $_POST["duration"], $_POST["severity"], $_POST["fever"],
     $_POST["lethargy"], $_POST["vomiting"], $_POST["oral_hydration_capable"], $_POST["urine_output_last_6_hours"],
    $_POST["pain_with_urination"], $_POST["cough_or_breathing_difficulty"], $_POST["able_to_sleep"], $_POST["nasal_discharge"], $_POST["previous_hospitalization"],
    $_POST["siblings_affected"], $_POST["immunization_up_to_date"], $_POST["notes"], $id ));
}



formHeader("Redirecting....");
formJump();
formFooter();
