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
$newid = formSubmit("form_pain", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Pain Evaluation", $newid, "pain", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_pain set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), history_of_pain='".$_POST["history_of_pain"]."', dull='".$_POST["dull"]."', colicky='".$_POST["colicky"]."', sharp='".$_POST["sharp"]."', duration_of_pain='".$_POST["duration_of_pain"]."', pain_referred_to_other_sites='".$_POST["pain_referred_to_other_sites"]."', what_relieves_pain='".$_POST["what_relieves_pain"]."', what_makes_pain_worse='".$_POST["what_makes_pain_worse"]."', accompanying_symptoms_vomitting='".$_POST["accompanying_symptoms_vomitting"]."', accompanying_symptoms_nausea='".$_POST["accompanying_symptoms_nausea"]."', accompanying_symptoms_headache='".$_POST["accompanying_symptoms_headache"]."', accompanying_symptoms_other='".$_POST["accompanying_symptoms_other"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
