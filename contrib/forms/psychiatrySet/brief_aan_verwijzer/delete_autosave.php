<?php

////////////////////////////////////////////////////////////////////
// Form:    Brief Aan Verwijzer - Delete Autosave
// Package: remove autosaved form  - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 30-03-2008
////////////////////////////////////////////////////////////////////

//local includes
require_once(__DIR__ . "/../../globals.php");

/////////////////
// here we check to se if there was an autosave version prior to the real save - hack!
$vectAutosave = sqlQuery("SELECT id, autosave_flag, autosave_datetime FROM form_brief_aan_verwijzer
                            WHERE pid = ?
                            AND groupname= ?
                            AND user= ? AND
                            authorized= ? AND activity=1
                            AND autosave_flag=1
                            ORDER by id DESC limit 1", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized));

if ($vectAutosave['autosave_flag'] == 1) {
    $strSql = "DELETE from  form_brief_aan_verwijzer
                  WHERE id = ?";
    sqlQuery($strSql, array($vectAutosave['id']));
}

//echo "debug :: form was deleted... sql=$strSql";
