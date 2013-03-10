<?php
//------------Forms generated from formsWiz

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_dictation", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Speech Dictation", $newid, "dictation", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_dictation set pid = ?,groupname=?,user=?,authorized=?,activity=1, date = NOW(), dictation=?, additional_notes=? where id=?",array($_SESSION["pid"],$_SESSION["authProvider"],$_SESSION["authUser"],$userauthorized,$_POST["dictation"],$_POST["additional_notes"],$_GET["id"]));
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
