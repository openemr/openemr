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
$newid = formSubmit("form_dictation", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Speech Dictation", $newid, "dictation", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_dictation set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), dictation='".$_POST["dictation"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
