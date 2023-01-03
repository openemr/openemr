<?php

//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_vision", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Vision", $newid, "vision", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_vision set pid = ?
        groupname= ?,
        user= ?,
        authorized= ?,
        activity=1, date = NOW(), od_k1=?,
        od_k1_axis= ?,
        od_k2= ?,
        od_k2_axis= ?,
        od_testing_status= ?,
        os_k1= ?,
        os_k1_axis= ?,
        os_k2= ?,
        os_k2_axis= ?,
        os_testing_status= ?,
        additional_notes= ?,
        where id= ?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized,
        $_POST["od_k1"], $_POST["od_k1_axis"], $_POST["od_k2"], $_POST["od_k2_axis"], $_POST["od_testing_status"],
        $_POST["os_k1"], $_POST["os_k1_axis"], $_POST["os_k2"], $_POST["os_k2_axis"], $_POST["os_testing_status"],
        $_POST["additional_notes"], $id));
}

formHeader("Redirecting....");
formJump();
formFooter();
