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
	$newid = formSubmit("form_ped_pain", $_POST, $_GET["id"], $userauthorized);
	addForm($encounter, "Pediatric Pain Evaluation", $newid, "ped_pain", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
	sqlInsert("update form_ped_pain set `pid` = {$_SESSION["pid"]},
	`groupname`='".$_SESSION["authProvider"]."',
	`user`='".$_SESSION["authUser"]."',
	`authorized`=$userauthorized,
	`activity`=1, 
	`date` = NOW(), 
	`location`='".$_POST["location"]."',
	`duration`='".$_POST["duration"]."',
	`severity`='".$_POST["severity"]."',
	`fever`='".$_POST["fever"]."',
	`lethargy`='".$_POST["lethargy"]."',
	`vomiting`='".$_POST["vomiting"]."',
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
