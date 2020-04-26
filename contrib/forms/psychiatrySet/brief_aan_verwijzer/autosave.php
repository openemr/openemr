<?php

////////////////////////////////////////////////////////////////////
// Form:    form_brief_aan_verwijzer - Autosave
// Package: letter to... - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 29-03-2008
////////////////////////////////////////////////////////////////////

//local includes
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");


/////////////////
// here we check to se if there was an autosave version prior to the real save
$vectAutosave = sqlQuery("SELECT id, autosave_flag, autosave_datetime FROM form_brief_aan_verwijzer
                            WHERE pid = ?
                            AND groupname= ?
                            AND user= ? AND
                            authorized= ? AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

// if yes then update this else insert
if ($vectAutosave['autosave_flag'] == 1 || $_POST["mode"] == "update") {
    if ($_POST["mode"] == "update") {
        $newid = $_POST["id"];
    } else {
        $newid = $vectAutosave['id'];
    }

    $strSql = "UPDATE form_brief_aan_verwijzer
                SET pid = ?, groupname=?, user=?, 
                authorized=?, activity=1, date = NOW(), 
                introductie=?,
                reden_van_aanmelding=?, 
                anamnese=?,
                psychiatrisch_onderzoek=?,
                beschrijvend_conclusie=?,
                advies_beleid=?,
                autosave_flag=1, 
                autosave_datetime=NOW() 
                  WHERE id = ?;";

    sqlQuery($strSql, array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["introductie"], $_POST["reden_van_aanmelding"],
    $_POST["anamnese"], $_POST["psychiatrisch_onderzoek"], $_POST["beschrijvend_conclusie"], $_POST["advies_beleid"], $newid));

//echo "DEBUG :: id=$newid, sql=$strSql<br />";
} else {
    $newid = formSubmit("form_brief_aan_verwijzer", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Psychiatric Brief Letter", $newid, "brief_aan_verwijzer", $pid, $userauthorized);

    //echo "Debug :: insert<br />";
}


//get timestamp
$result = sqlQuery("SELECT autosave_datetime FROM form_brief_aan_verwijzer
                            WHERE pid = ?
                            AND groupname= ?
                            AND user= ? AND
                            authorized= ? AND activity=1 AND id=?
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $id));

//$timestamp = mysql_result($result, 0);

//output timestamp
echo xlt('Last Saved') . ': ' . text($result['autosave_datetime']);
