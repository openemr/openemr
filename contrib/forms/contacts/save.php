<?php
//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
foreach ($_POST as $k => $var) {
    $_POST[$k] = add_escape_custom($var);
    echo text($var) . "\n";
}

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_contacts", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Contacts", $newid, "contacts", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlInsert("update form_contacts set pid = ?,groupname= ?,user= ?, authorized= ?,activity=1, date = NOW(), od_base_curve= ?, od_sphere= ?, od_cylinder= ?,
    od_axis= ?, od_diameter= ?, os_base_curve= ?, os_sphere= ?, os_cylinder= ?, os_axis= ?, os_diameter= ?, material= ?, color= ?, bifocal_type= ?, 
    add_value= ?, va_far= ?, va_near= ?, additional_notes= ? WHERE id= ?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["od_base_curve"], $_POST["od_sphere"], $_POST["od_cylinder"],
    $_POST["od_axis"], $_POST["od_diameter"], $_POST["os_base_curve"], $_POST["os_sphere"], $_POST["os_cylinder"], $_POST["os_axis"], $_POST["os_diameter"],
    $_POST["material"], $_POST["color"], $_POST["bifocal_type"], $_POST["add_value"], $_POST["va_far"], $_POST["va_near"], $_POST["additional_notes"], $id));
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
