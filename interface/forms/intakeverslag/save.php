<?php
////////////////////////////////////////////////////////////////////
// Form:	Intakeverslag
// Package:	Report of First visit - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 27-03-2008
////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

foreach ($_POST as $k => $var)
{
  $_POST[$k] = mysql_real_escape_string($var);
  // echo "$var\n";
}

if($encounter == "") $encounter = date("Ymd");

if( $_GET["mode"] == "new" )
{
    if( $_GET["id"] != '' )
    {
      $_GET["id"] = '0';
      $newid = formSubmit( "form_intakeverslag", $_POST, $_GET["id"], $userauthorized );
      // moved here ?
    addForm( $encounter, "Intakeverslag", $newid, "intakeverslag", $pid, $userauthorized );
      
    } else
    {
      $_POST['autosave_flag'] = 0;
      $newid = formUpdate( "form_intakeverslag", $_POST, $_GET["saveid"], $userauthorized );
    }
    
    

} elseif( $_GET["mode"] == "update" )
{
    sqlQuery( "UPDATE form_intakeverslag 
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
                autosave_flag=0, 
                autosave_datetime=0 
                  WHERE id = ".$_GET["id"].";" );
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
