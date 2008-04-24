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
foreach ($_POST as $k => $var)
{
  $_POST[$k] = mysql_real_escape_string($var);
  // echo "$var\n";
}

/////////////////
// here we check to se if there was an autosave version prior to the real save 
$vectAutosave = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_psychiatrisch_onderzoek
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );

// if yes then update this else insert
if( $vectAutosave['autosave_flag'] == 1 || $_POST["mode"] == "update" )
{
  if( $_POST["mode"] == "update" )
      $newid = $_POST["id"];
  else
      $newid = $vectAutosave['id'];
  
  $strSql = "UPDATE form_psychiatrisch_onderzoek
                SET pid = ".$_SESSION["pid"].", groupname='".$_SESSION["authProvider"]."', user='".$_SESSION["authUser"]."', 
                authorized=$userauthorized, activity=1, date = NOW(), 
                datum_onderzoek='".$_POST["datum_onderzoek"]."',
                reden_van_aanmelding='".$_POST["reden_van_aanmelding"]."', 
                conclusie_van_intake='".$_POST["conclusie_van_intake"]."',
                medicatie='".$_POST["medicatie"]."',
                anamnese='".$_POST["anamnese"]."',
                psychiatrisch_onderzoek='".$_POST["psychiatrisch_onderzoek"]."',
                beschrijvende_conclusie='".$_POST["beschrijvende_conclusie"]."',
                behandelvoorstel='".$_POST["behandelvoorstel"]."',
                autosave_flag=1, 
                autosave_datetime=NOW() 
                  WHERE id = ".$newid.";";

  sqlQuery( $strSql );

//echo "DEBUG :: id=$newid, sql=$strSql<br>";
                  
} else
{
    $newid = formSubmit( "form_psychiatrisch_onderzoek", $_POST, $_GET["id"], $userauthorized );
    addForm( $encounter, "Psychiatrisch Onderzoek", $newid, "psychiatrisch_onderzoek", $pid, $userauthorized );
    
    //echo "Debug :: insert<br>";
}


//get timestamp
$result = sqlQuery("SELECT autosave_datetime FROM form_psychiatrisch_onderzoek
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1 AND id=$newid
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );
//$timestamp = mysql_result($result, 0);

//output timestamp
echo 'Last Saved: '.$result['autosave_datetime'];

?>
