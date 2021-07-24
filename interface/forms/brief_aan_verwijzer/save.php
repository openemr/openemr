<?php

////////////////////////////////////////////////////////////////////
// Form:    form_brief_aan_verwijzer
// Package: letter to - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 30-03-2008
////////////////////////////////////////////////////////////////////

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    if ($_GET["id"] != '') {
        $_GET["id"] = '0';
        $newid = formSubmit("form_brief_aan_verwijzer", $_POST, $_GET["id"], $userauthorized);
      // add shoul be here or downoutside if block ?
        addForm($encounter, "Psychiatric Brief Letter", $newid, "brief_aan_verwijzer", $pid, $userauthorized);
    } else {
        $_POST['autosave_flag'] = 0;
        $newid = formUpdate("form_brief_aan_verwijzer", $_POST, $_GET["saveid"], $userauthorized);
    }
} elseif ($_GET["mode"] == "update") {
    $strSql = "UPDATE form_brief_aan_verwijzer
                SET pid = ?, groupname=?, user=?,
                authorized=?, activity=1, date = NOW(),
                introductie=?,
                reden_van_aanmelding=?,
                anamnese=?,
                psychiatrisch_onderzoek=?,
                beschrijvend_conclusie=?,
                advies_beleid=?,
                autosave_flag=0,
                autosave_datetime=NOW()
                  WHERE id = ?;";

    sqlQuery($strSql, array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["introductie"], $_POST["reden_van_aanmelding"],
    $_POST["anamnese"], $_POST["psychiatrisch_onderzoek"], $_POST["beschrijvend_conclusie"], $_POST["advies_beleid"], $newid));
}

formHeader("Redirecting....");
formJump();
formFooter();
