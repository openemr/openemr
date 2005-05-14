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
$newid = formSubmit("form_contacts", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Contacts", $newid, "contacts", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_contacts set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), od_base_curve='".$_POST["od_base_curve"]."', od_sphere='".$_POST["od_sphere"]."', od_cylinder='".$_POST["od_cylinder"]."', od_axis='".$_POST["od_axis"]."', od_diameter='".$_POST["od_diameter"]."', os_base_curve='".$_POST["os_base_curve"]."', os_sphere='".$_POST["os_sphere"]."', os_cylinder='".$_POST["os_cylinder"]."', os_axis='".$_POST["os_axis"]."', os_diameter='".$_POST["os_diameter"]."', material='".$_POST["material"]."', color='".$_POST["color"]."', bifocal_type='".$_POST["bifocal_type"]."', add_value='".$_POST["add_value"]."', va_far='".$_POST["va_far"]."', va_near='".$_POST["va_near"]."', additional_notes='".$_POST["additional_notes"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
