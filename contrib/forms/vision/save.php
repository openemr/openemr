<?php
//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_vision", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Vision", $newid, "vision", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlInsert("update form_vision set pid = '" . add_escape_custom($_SESSION["pid"]) .
        "', groupname='" . add_escape_custom($_SESSION["authProvider"]) .
        "', `user`='" . add_escape_custom($_SESSION["authUser"]) .
        "', authorized='" . add_escape_custom($userauthorized) .
        "', activity=1, date = NOW(), od_k1='" . add_escape_custom($_POST["od_k1"]) .
        "', od_k1_axis='" . add_escape_custom($_POST["od_k1_axis"]) .
        "', od_k2='" . add_escape_custom($_POST["od_k2"]) .
        "', od_k2_axis='" . add_escape_custom($_POST["od_k2_axis"]) .
        "', od_testing_status='" . add_escape_custom($_POST["od_testing_status"]) .
        "', os_k1='" . add_escape_custom($_POST["os_k1"]) .
        "', os_k1_axis='" . add_escape_custom($_POST["os_k1_axis"]) .
        "', os_k2='" . add_escape_custom($_POST["os_k2"]) .
        "', os_k2_axis='" . add_escape_custom($_POST["os_k2_axis"]) .
        "', os_testing_status='" . add_escape_custom($_POST["os_testing_status"]) .
        "', additional_notes='" . add_escape_custom($_POST["additional_notes"]) .
        "' where id='" . add_escape_custom($id) . "'");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
