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
$newid = formSubmit("form_vitalsigns", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Vital Signs", $newid, "vitalsigns", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_vitalsigns set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), standing_bp_1='".$_POST["standing_bp_1"]."', standing_bp_2='".$_POST["standing_bp_2"]."', sitting_bp_1='".$_POST["sitting_bp_1"]."', sitting_bp_2='".$_POST["sitting_bp_2"]."', supine_bp_1='".$_POST["supine_bp_1"]."', supine_bp_2='".$_POST["supine_bp_2"]."', systolic_bp_1='".$_POST["systolic_bp_1"]."', systolic_bp_2='".$_POST["systolic_bp_2"]."', diastolic_bp_1='".$_POST["diastolic_bp_1"]."', diastolic_bp_2='".$_POST["diastolic_bp_2"]."', heart_rate_beats_per_minute='".$_POST["heart_rate_beats_per_minute"]."', temperature_c='".$_POST["temperature_c"]."', temperature_f='".$_POST["temperature_f"]."', temperature_method='".$_POST["temperature_method"]."', respiration_beats_per_minute='".$_POST["respiration_beats_per_minute"]."', height_feet='".$_POST["height_feet"]."', height_inches='".$_POST["height_inches"]."', height_centimeters='".$_POST["height_centimeters"]."', weight_lbs='".$_POST["weight_lbs"]."', weight_ozs='".$_POST["weight_ozs"]."', weight_kgs='".$_POST["weight_kgs"]."', body_mass_index='".$_POST["body_mass_index"]."', figure_shape='".$_POST["figure_shape"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
