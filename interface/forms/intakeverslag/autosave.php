<?php
////////////////////////////////////////////////////////////////////
// Form:	Intakeverslag - Autosave
// Package:	Report of First visit - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 28-03-2008
////////////////////////////////////////////////////////////////////

//local includes
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//echo "intakedatum=".$_POST["intakedatum"];
//var_dump($_POST);

// escape the strings
foreach ($_POST as $k => $var)
{
  $_POST[$k] = mysql_real_escape_string($var);
  // echo "$var\n";
}

/////////////////
// here we check to se if there was an autosave version prior to the real save 
$vectAutosave = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_intakeverslag 
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
  
  $strSql = "UPDATE form_intakeverslag 
                SET pid = ".$_SESSION["pid"].", groupname='".$_SESSION["authProvider"]."', user='".$_SESSION["authUser"]."', 
                authorized=$userauthorized, activity=1, date = NOW(), 
                intakedatum='".$_POST["intakedatum"]."',
                reden_van_aanmelding='".$_POST["reden_van_aanmelding"]."', 
                klachten_probleemgebieden='".$_POST["klachten_probleemgebieden"]."',
                hulpverlening_onderzoek='".$_POST["hulpverlening_onderzoek"]."',
                hulpvraag_en_doelen='".$_POST["hulpvraag_en_doelen"]."',
                bijzonderheden_systeem='".$_POST["bijzonderheden_systeem"]."',
                werk_opleiding_vrije_tijdsbesteding='".$_POST["werk_opleiding_vrije_tijdsbesteding"]."',
                relatie_kinderen='".$_POST["relatie_kinderen"]."',
                somatische_context='".$_POST["somatische_context"]."',
                alcohol='".$_POST["alcohol"]."',
                drugs='".$_POST["drugs"]."',
                roken='".$_POST["roken"]."',
                medicatie='".$_POST["medicatie"]."',
                familieanamnese='".$_POST["familieanamnese"]."',
                indruk_observaties='".$_POST["indruk_observaties"]."',
                beschrijvende_conclusie='".$_POST["beschrijvende_conclusie"]."',
                behandelvoorstel='".$_POST["behandelvoorstel"]."',
                autosave_flag=1, 
                autosave_datetime=NOW() 
                  WHERE id = ".$newid.";";

  sqlQuery( $strSql );

//echo "lalalalal id=$newid, sql=$strSql<br>";
                  
} else
{
    $newid = formSubmit( "form_intakeverslag", $_POST, $_GET["id"], $userauthorized );
    addForm( $encounter, "Psychiatric Intake", $newid, "intakeverslag", $pid, $userauthorized );
    
    //echo "debug :: insert<br>";
}


//get timestamp
$result = sqlQuery("SELECT autosave_datetime FROM form_intakeverslag 
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1 AND id=$newid
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );
//$timestamp = mysql_result($result, 0);

//output timestamp
echo xl('Last Saved') . ': '.$result['autosave_datetime'];

?>
