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
$newid = formSubmit("form_vision", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Vision", $newid, "vision", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_vision set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), od_k1='".$_POST["od_k1"]."', od_k1_axis='".$_POST["od_k1_axis"]."', od_k2='".$_POST["od_k2"]."', od_k2_axis='".$_POST["od_k2_axis"]."', od_testing_status='".$_POST["od_testing_status"]."', os_k1='".$_POST["os_k1"]."', os_k1_axis='".$_POST["os_k1_axis"]."', os_k2='".$_POST["os_k2"]."', os_k2_axis='".$_POST["os_k2_axis"]."', os_testing_status='".$_POST["os_testing_status"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
