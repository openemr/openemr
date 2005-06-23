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

// calculate body mass index bmi=k/(m**2)
$k=$_POST["weight"]/2.2;
$w=$_POST["height"]/39.4;
$_POST["BMI"]=round ( ($k/($w*$w)),2);
$bmi=$_POST["BMI"];
if ( $bmi > 42 ) { $_POST["BMI_status"]='Obesity III'; }
elseif ( $bmi > 34 ) { $_POST["BMI_status"]='Obesity II'; }
elseif ( $bmi > 30 ) { $_POST["BMI_status"]='Obesity I'; }
elseif ( $bmi > 27 ) { $_POST["BMI_status"]='Overweight'; }
elseif ( $bmi > 25 ) { $_POST["BMI_status"]='Normal BL'; }
elseif ( $bmi > 18.5 ) { $_POST["BMI_status"]='Normal'; }
elseif ( $bmi > 10 ) { $_POST["BMI_status"]='Underweight'; }

if ($_GET["mode"] == "new"){
	$newid = formSubmit("form_vitals", $_POST, $_GET["id"], $userauthorized);
	addForm($encounter, "Vital Signs", $newid, "vitals", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
	sqlInsert("update form_vitals set `pid` = {$_SESSION["pid"]},
	`groupname`='".$_SESSION["authProvider"]."',
	`user`='".$_SESSION["authUser"]."',
	`authorized`=$userauthorized,
	`activity`=1, 
	`date` = NOW(), 
	`bps` ='".$_POST["bps"]."',
	`bpd` ='".$_POST["bpd"]."',
	`weight` ='".$_POST["weight"]."',
	`height` ='".$_POST["height"]."',
	`temperature` ='".$_POST["temperature"]."',
	`temp_method` ='".$_POST["temp_method"]."',
	`pulse` ='".$_POST["pulse"]."',
	`respiration` ='".$_POST["respiration"]."',
	`note` ='".$_POST["note"]."',
	`BMI` ='".$_POST["BMI"]."',
	`BMI_status` ='".$_POST["BMI_status"]."',
	`waist_circ` ='".$_POST["waist_circ"]."'
	WHERE id=$id");
}

$_SESSION["encounter"] = $encounter;

formHeader("Redirecting....");
formJump();
formFooter();

?>
