<?php
////////////////////////////////////////////////////////////////////
// Form:	Psychiatrisch Onderzoek - Delete Autosave
// Package:	remove autosaved form  - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 29-03-2008
////////////////////////////////////////////////////////////////////

//local includes
include_once("../../globals.php");

/////////////////
// here we check to se if there was an autosave version prior to the real save - hack!
$vectAutosave = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_psychiatrisch_onderzoek 
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );

if( $vectAutosave['autosave_flag'] == 1 )
{
  
  $strSql = "DELETE from form_psychiatrisch_onderzoek
                  WHERE id = ".$vectAutosave['id'].";";
  sqlQuery( $strSql );
}

//echo "debug :: form was deleted... sql=$strSql";

?>
