<?php
//------------ sava.php for Ankleinjury Form created by Nikolai Vitsyn by 2004/01/23
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
foreach ($_POST as $k => $var) {
$_POST[$k] = mysql_escape_string($var);
//echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_ankleinjury", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Ankle Evaluation Form", $newid, "ankleinjury", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_ankleinjury set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(),
ankle_date_of_injuary='".$_POST["ankle_date_of_injuary"]."',
ankle_work_related='".$_POST["ankle_work_related"]."',
ankle_foot='".$_POST["ankle_foot"]."',
ankle_severity_of_pain='".$_POST["ankle_severity_of_pain"]."',
ankle_significant_swelling='".$_POST["ankle_significant_swelling"]."',
ankle_onset_of_swelling='".$_POST["ankle_onset_of_swelling"]."',
ankle_how_did_injury_occur='".$_POST["ankle_how_did_injury_occur"]."',
ankle_ottawa_bone_tenderness='".$_POST["ankle_ottawa_bone_tenderness"]."',
ankle_able_to_bear_weight_steps='".$_POST["ankle_able_to_bear_weight_steps"]."',
ankle_x_ray_interpretation='".$_POST["ankle_x_ray_interpretation"]."',
ankle_additional_x_ray_notes='".$_POST["ankle_additional_x_ray_notes"]."',
ankle_diagnosis1='".$_POST["ankle_diagnosis1"]."',
ankle_diagnosis2='".$_POST["ankle_diagnosis2"]."',
ankle_diagnosis3='".$_POST["ankle_diagnosis3"]."',
ankle_diagnosis4='".$_POST["ankle_diagnosis4"]."',
ankle_plan='".$_POST["ankle_plan"]."',
ankle_additional_diagnisis='".$_POST["ankle_additional_diagnisis"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
