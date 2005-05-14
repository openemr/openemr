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
$newid = formSubmit("form_neurologicalreview", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Neurological Review", $newid, "neurologicalreview", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_neurologicalreview set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), burning='".$_POST["burning"]."', confusion='".$_POST["confusion"]."', dizziness='".$_POST["dizziness"]."', dysphasia='".$_POST["dysphasia"]."', facial_tic='".$_POST["facial_tic"]."', focal_weakness='".$_POST["focal_weakness"]."', forgetfulness='".$_POST["forgetfulness"]."', headache='".$_POST["headache"]."', hyperesthesia='".$_POST["hyperesthesia"]."', lightheadedness='".$_POST["lightheadedness"]."', numbness='".$_POST["numbness"]."', paralysis='".$_POST["paralysis"]."', paresthesia='".$_POST["paresthesia"]."', symptoms_of_problems='".$_POST["symptoms_of_problems"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
