<?php
////////////////////////////////////////////////////////////////////
// Form:	Psychiatrisch Onderzoek - Autosave
// Package:	Psychiatric Research - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 29-03-2008
////////////////////////////////////////////////////////////////////

//local includes
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");


// escape the strings
foreach ($_POST as $k => $var) {
    $_POST[$k] = add_escape_custom($var);
  // echo "$var\n";
}

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

//echo "DEBUG :: id=$newid, sql=$strSql<br>";
} else {
    $newid = formSubmit("form_psychiatrisch_onderzoek", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Psychiatric Examination", $newid, "psychiatrisch_onderzoek", $pid, $userauthorized);
    
    //echo "Debug :: insert<br>";
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
echo xl('Last Saved') . ': '.$result['autosave_datetime'];
