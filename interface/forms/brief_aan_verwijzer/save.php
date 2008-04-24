<?php
////////////////////////////////////////////////////////////////////
// Form:	form_brief_aan_verwijzer 
// Package:	letter to - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 30-03-2008
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
      $newid = formSubmit( "form_brief_aan_verwijzer", $_POST, $_GET["id"], $userauthorized );
      // add shoul be here or downoutside if block ?
    addForm( $encounter, "Brief Aan Verwijzer", $newid, "brief_aan_verwijzer", $pid, $userauthorized );
      
    } else
    {
      $_POST['autosave_flag'] = 0;
      $newid = formUpdate( "form_brief_aan_verwijzer", $_POST, $_GET["saveid"], $userauthorized );
    }
    
    

} elseif( $_GET["mode"] == "update" )
{

  $strSql = "UPDATE form_brief_aan_verwijzer
                SET pid = ".$_SESSION["pid"].", groupname='".$_SESSION["authProvider"]."', user='".$_SESSION["authUser"]."', 
                authorized=$userauthorized, activity=1, date = NOW(), 
                introductie='".$_POST["introductie"]."',
                reden_van_aanmelding='".$_POST["reden_van_aanmelding"]."', 
                anamnese='".$_POST["anamnese"]."',
                psychiatrisch_onderzoek='".$_POST["psychiatrisch_onderzoek"]."',
                beschrijvend_conclusie='".$_POST["beschrijvend_conclusie"]."',
                advies_beleid='".$_POST["advies_beleid"]."',
                autosave_flag=0, 
                autosave_datetime=NOW() 
                  WHERE id = ".$_GET['id'].";";

  sqlQuery( $strSql );

}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
