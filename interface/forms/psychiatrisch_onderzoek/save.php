<?php
////////////////////////////////////////////////////////////////////
// Form:	Psychiatrisch Onderzoek
// Package:	Report of First visit - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 29-03-2008
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
      echo "lalala 1";
      $_GET["id"] = '0';
      $newid = formSubmit( "form_psychiatrisch_onderzoek", $_POST, $_GET["id"], $userauthorized );
      // add new form ???
    addForm( $encounter, "Psychiatrisch Onderzoek", $newid, "psychiatrisch_onderzoek", $pid, $userauthorized );
      
    } else
    {
      echo "lalala 2";
      
      $_POST['autosave_flag'] = 0;
     /// $newid = formUpdate( "form_psychiatrisch_onderzoek", $_POST, $_GET["saveid"], $userauthorized );
    }
    
    

} elseif( $_GET["mode"] == "update" )
{

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
                autosave_flag=0, 
                autosave_datetime=NOW() 
                  WHERE id = ".$_GET["id"].";";

  sqlQuery( $strSql );

}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
