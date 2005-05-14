<?php
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

foreach ($_POST as $k => $var) {
	$_POST[$k] = mysql_escape_string($var);
	echo "$var\n";
}

if ($encounter == "") $encounter = date("Ymd");

if ($_GET["mode"] == "new"){
	$newid = formSubmit("form_ped_GI", $_POST, $_GET["id"], $userauthorized);
	addForm($encounter, "Pediatric GI Evaluation", $newid, "ped_GI", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
	sqlInsert("update form_ped_GI set `pid` = {$_SESSION["pid"]},
	`groupname`='".$_SESSION["authProvider"]."',
	`user`='".$_SESSION["authUser"]."',
	`authorized`=$userauthorized,
	`activity`=1, 
	`date` = NOW(), 
	`diarrhea`='".$_POST["diarrhea"]."',
	`with_every_bowel_movement`='".$_POST["with_every_bowel_movement"]."',
	`after_every_meal`='".$_POST["after_every_meal"]."',
	`blood_or_mucus_in_stool`='".$_POST["blood_or_mucus_in_stool"]."',
	`diarrhea_onset`='".$_POST["diarrhea_onset"]."',
	`worms`='".$_POST["worms"]."',
	`vomits`='".$_POST["vomits"]."',
	`duration`='".$_POST["duration"]."',
	`projectile`='".$_POST["projectile"]."',
	`more_often_than_2_hours`='".$_POST["more_often_than_2_hours"]."',
	`vomit_after_every_meal`='".$_POST["vomit_after_every_meal"]."',
	`blood_in_vomitus`='".$_POST["blood_in_vomitus"]."',
	`taking_medication`='".$_POST["taking_medication"]."',
	`oral_rehydration`='".$_POST["oral_rehydration"]."',
	`eating_solid_food`='".$_POST["eating_solid_food"]."',
	`fever`='".$_POST["fever"]."',
	`pain`='".$_POST["pain"]."',
	`lethargy`='".$_POST["lethargy"]."',
	`oral_hydration_capable`='".$_POST["oral_hydration_capable"]."',
	`urine_output_last_6_hours`='".$_POST["urine_output_last_6_hours"]."',
	`pain_with_urination`='".$_POST["pain_with_urination"]."',
	`cough_or_breathing_difficulty`='".$_POST["cough_or_breathing_difficulty"]."',
	`able_to_sleep`='".$_POST["able_to_sleep"]."',
	`nasal_discharge`='".$_POST["nasal_discharge"]."',
	`previous_hospitalization`='".$_POST["previous_hospitalization"]."',
	`siblings_affected`='".$_POST["siblings_affected"]."',
	`immunization_up_to_date`='".$_POST["immunization_up_to_date"]."',
	`notes`='".$_POST["notes"]."'
	WHERE id=$id");
}

$_SESSION["encounter"] = $encounter;

formHeader("Redirecting....");
formJump();
formFooter();

?>
