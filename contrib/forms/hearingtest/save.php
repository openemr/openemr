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
$newid = formSubmit("form_hearingtest", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Hearing Test", $newid, "hearingtest", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_hearingtest set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), right_ear_250='".$_POST["right_ear_250"]."', right_ear_500='".$_POST["right_ear_500"]."', right_ear_1000='".$_POST["right_ear_1000"]."', right_ear_2000='".$_POST["right_ear_2000"]."', right_ear_3000='".$_POST["right_ear_3000"]."', right_ear_4000='".$_POST["right_ear_4000"]."', right_ear_5000='".$_POST["right_ear_5000"]."', right_ear_6000='".$_POST["right_ear_6000"]."', left_ear_250='".$_POST["left_ear_250"]."', left_ear_500='".$_POST["left_ear_500"]."', left_ear_1000='".$_POST["left_ear_1000"]."', left_ear_2000='".$_POST["left_ear_2000"]."', left_ear_3000='".$_POST["left_ear_3000"]."', left_ear_4000='".$_POST["left_ear_4000"]."', left_ear_5000='".$_POST["left_ear_5000"]."', left_ear_6000='".$_POST["left_ear_6000"]."', with_hearing_aid='".$_POST["with_hearing_aid"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
