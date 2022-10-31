<?php

////////////////////////////////////////////////////////////////////
// Form:    Psychiatrisch Onderzoek
// Package: Report of First visit - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 29-03-2008
////////////////////////////////////////////////////////////////////

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    if ($_GET["id"] != '') {
        echo "lalala 1";
        $_GET["id"] = '0';
        $newid = formSubmit("form_psychiatrisch_onderzoek", $_POST, $_GET["id"], $userauthorized);
      // add new form ???
        addForm($encounter, "Psychiatric Examination", $newid, "psychiatrisch_onderzoek", $pid, $userauthorized);
    } else {
        echo "lalala 2";

        $_POST['autosave_flag'] = 0;
     /// $newid = formUpdate( "form_psychiatrisch_onderzoek", $_POST, $_GET["saveid"], $userauthorized );
    }
} elseif ($_GET["mode"] == "update") {
    $strSql = "UPDATE form_psychiatrisch_onderzoek
                SET pid = ?, groupname=?, user=?,
                authorized=?, activity=1, date = NOW(),
                datum_onderzoek=?,
                reden_van_aanmelding=?,
                conclusie_van_intake=?,
                medicatie=?,
                anamnese=?,
                psychiatrisch_onderzoek=?,
                beschrijvende_conclusie=?,
                behandelvoorstel=?,
                autosave_flag=1,
                autosave_datetime=NOW()
                  WHERE id = ?;";

    sqlQuery($strSql, array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["datum_onderzoek"], $_POST["reden_van_aanmelding"],
    $_POST["conclusie_van_intake"], $_POST["medicatie"], $_POST["anamnese"], $_POST["psychiatrisch_onderzoek"], $_POST["beschrijvende_conclusie"], $_POST["behandelvoorstel"], $_GET["id"]));
}

formHeader("Redirecting....");
formJump();
formFooter();
