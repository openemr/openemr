<?php

//

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_ped_GI", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Pediatric GI Evaluation", $newid, "ped_GI", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_ped_GI set pid = ?,
	groupname = ?,
	user = ?,
	authorized = ?,
	activity =1,
	date = NOW(),
	diarrhea = ?,
	with_every_bowel_movement = ?,
	after_every_meal = ?,
	blood_or_mucus_in_stool = ?,
	diarrhea_onset = ?,
	worms = ?,
	vomits = ?,
	duration = ?,
	projectile = ?,
	more_often_than_2_hours = ?,
	vomit_after_every_meal = ?,
	blood_in_vomitus = ?,
	taking_medication = ?,
	oral_rehydration = ?,
	eating_solid_food = ?,
	fever = ?,
	pain = ?,
	lethargy = ?,
	oral_hydration_capable = ?,
	urine_output_last_6_hours = ?,
	pain_with_urination = ?,
	cough_or_breathing_difficulty = ?,
	able_to_sleep = ?,
	nasal_discharge = ?,
	previous_hospitalization = ?,
	siblings_affected = ?,
	immunization_up_to_date = ?,
	notes = ?
	WHERE id = ?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["diarrhea"], $_POST["with_every_bowel_movement"], $_POST["after_every_meal"], $_POST["blood_or_mucus_in_stool"], $_POST["diarrhea_onset"],
    $_POST["worms"], $_POST["vomits"], $_POST["duration"], $_POST["projectile"], $_POST["more_often_than_2_hours"], $_POST["vomit_after_every_meal"],
    $_POST["blood_in_vomitus"], $_POST["taking_medication"], $_POST["oral_rehydration"], $_POST["eating_solid_food"], $_POST["fever"],
    $_POST["pain"], $_POST["lethargy"], $_POST["oral_hydration_capable"], $_POST["urine_output_last_6_hours"], $_POST["pain_with_urination"],
    $_POST["cough_or_breathing_difficulty"], $_POST["able_to_sleep"], $_POST["nasal_discharge"], $_POST["previous_hospitalization"],
    $_POST["siblings_affected"], $_POST["immunization_up_to_date"], $_POST["notes"], $id));
}


formHeader("Redirecting....");
formJump();
formFooter();
