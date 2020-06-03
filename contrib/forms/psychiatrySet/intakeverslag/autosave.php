<?php

////////////////////////////////////////////////////////////////////
// Form:    Intakeverslag - Autosave
// Package: Report of First visit - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 28-03-2008
////////////////////////////////////////////////////////////////////

//local includes
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

//echo "intakedatum=".$_POST["intakedatum"];
//var_dump($_POST);

/////////////////
// here we check to se if there was an autosave version prior to the real save
$vectAutosave = sqlQuery("SELECT id, autosave_flag, autosave_datetime FROM form_intakeverslag 
                            WHERE pid = ?
                            AND groupname= ?
                            AND user= ? AND
                            authorized= ? AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized ));

// if yes then update this else insert
if ($vectAutosave['autosave_flag'] == 1 || $_POST["mode"] == "update") {
    if ($_POST["mode"] == "update") {
        $newid = $_POST["id"];
    } else {
        $newid = $vectAutosave['id'];
    }

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
                autosave_flag=1, 
                autosave_datetime=NOW() 
                  WHERE id = ?;", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["intakedatum"], $_POST["reden_van_aanmelding"], $_POST["klachten_probleemgebieden"],
                  $_POST["hulpverlening_onderzoek"], $_POST["hulpvraag_en_doelen"], $_POST["bijzonderheden_systeem"], $_POST["werk_opleiding_vrije_tijdsbesteding"], $_POST["relatie_kinderen"], $_POST["somatische_context"],
                  $_POST["alcohol"], $_POST["drugs"], $_POST["roken"], $_POST["medicatie"], $_POST["familieanamnese"], $_POST["indruk_observaties"], $_POST["beschrijvende_conclusie"], $_POST["behandelvoorstel"],
                  $newid));

//echo "lalalalal id=$newid, sql=$strSql<br />";
} else {
    $newid = formSubmit("form_intakeverslag", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Psychiatric Intake", $newid, "intakeverslag", $pid, $userauthorized);

    //echo "debug :: insert<br />";
}


//get timestamp
$result = sqlQuery("SELECT autosave_datetime FROM form_intakeverslag 
                            WHERE pid = ?
                            AND groupname = ?
                            AND user= ? AND
                            authorized= ? AND activity=1 AND id= ?
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $newid ));
//$timestamp = mysql_result($result, 0);

//output timestamp
echo txl('Last Saved') . ': ' . text($result['autosave_datetime']);
