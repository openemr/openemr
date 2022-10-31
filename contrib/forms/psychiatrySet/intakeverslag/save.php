<?php

////////////////////////////////////////////////////////////////////
// Form:    Intakeverslag
// Package: Report of First visit - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 27-03-2008
////////////////////////////////////////////////////////////////////

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    if ($_GET["id"] != '') {
        $_GET["id"] = '0';
        $newid = formSubmit("form_intakeverslag", $_POST, $_GET["id"], $userauthorized);
      // moved here ?
        addForm($encounter, "Psychiatric Intake", $newid, "intakeverslag", $pid, $userauthorized);
    } else {
        $_POST['autosave_flag'] = 0;
        $newid = formUpdate("form_intakeverslag", $_POST, $_GET["saveid"], $userauthorized);
    }
} elseif ($_GET["mode"] == "update") {
       sqlQuery("UPDATE form_intakeverslag
                SET pid = ?, groupname= ?, user= ?,
                authorized=, activity=1, date = NOW(),
                intakedatum=?,
                reden_van_aanmelding=?,
                klachten_probleemgebieden=?,
                hulpverlening_onderzoek=?,
                hulpvraag_en_doelen=?,
                bijzonderheden_systeem=?,
                werk_opleiding_vrije_tijdsbesteding=?,
                relatie_kinderen=?,
                somatische_context=?,
                alcohol=?,
                drugs=?,
                roken=?,
                medicatie=?,
                familieanamnese=?,
                indruk_observaties=?,
                beschrijvende_conclusie=?,
                behandelvoorstel=?,
                autosave_flag=0,
                autosave_datetime=0
                  WHERE id = ?;", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["intakedatum"], $_POST["reden_van_aanmelding"], $_POST["klachten_probleemgebieden"],
                  $_POST["hulpverlening_onderzoek"], $_POST["hulpvraag_en_doelen"], $_POST["bijzonderheden_systeem"], $_POST["werk_opleiding_vrije_tijdsbesteding"], $_POST["relatie_kinderen"], $_POST["somatische_context"],
                  $_POST["alcohol"], $_POST["drugs"], $_POST["roken"], $_POST["medicatie"], $_POST["familieanamnese"], $_POST["indruk_observaties"], $_POST["beschrijvende_conclusie"], $_POST["behandelvoorstel"],
                  $_GET["id"]));
}

formHeader("Redirecting....");
formJump();
formFooter();
