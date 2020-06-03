<?php

////////////////////////////////////////////////////////////////////
// Form:    Psychiatrisch Onderzoek - Autosave
// Package: Psychiatric Research - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 29-03-2008
////////////////////////////////////////////////////////////////////

//local includes
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

/////////////////
// here we check to se if there was an autosave version prior to the real save
$vectAutosave = sqlQuery("SELECT id, autosave_flag, autosave_datetime FROM form_psychiatrisch_onderzoek
                            WHERE pid = ?
                            AND groupname= ?
                            AND user=? AND
                            authorized=? AND activity=1
                            AND autosave_flag=1
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

// if yes then update this else insert
if ($vectAutosave['autosave_flag'] == 1 || $_POST["mode"] == "update") {
    if ($_POST["mode"] == "update") {
        $newid = $_POST["id"];
    } else {
        $newid = $vectAutosave['id'];
    }

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
    $_POST["conclusie_van_intake"], $_POST["medicatie"], $_POST["anamnese"], $_POST["psychiatrisch_onderzoek"], $_POST["beschrijvende_conclusie"], $_POST["behandelvoorstel"], $newid));

//echo "DEBUG :: id=$newid, sql=$strSql<br />";
} else {
    $newid = formSubmit("form_psychiatrisch_onderzoek", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Psychiatric Examination", $newid, "psychiatrisch_onderzoek", $pid, $userauthorized);

    //echo "Debug :: insert<br />";
}


//get timestamp
$result = sqlQuery("SELECT autosave_datetime FROM form_psychiatrisch_onderzoek
                            WHERE pid = ?
                            AND groupname= ?
                            AND user=? AND
                            authorized=? AND activity=1 AND id=?
                            AND autosave_flag=1
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $newid));
//$timestamp = mysql_result($result, 0);

//output timestamp
echo xlt('Last Saved') . ': ' . text($result['autosave_datetime']);
