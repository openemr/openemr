<?php
////////////////////////////////////////////////////////////////////
// Form:	form_brief_aan_verwijzer
// Package:	letter to ... - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 30-03-2008
////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/patient.inc");

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

$result = getPatientData($pid, "fname,lname,pid,pubpid,phone_home,sex,pharmacy_id,DOB,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
$provider_results = sqlQuery("select * from users where username='" . $_SESSION{"authUser"} . "'");

////////////////////////////////////////////////////////////////////
// Function:	getPatientDateOfLastEncounter
function getPatientDateOfLastEncounter( $nPid )
{
  // get date of last encounter no codes
  $strEventDate = sqlQuery("SELECT MAX(pc_eventDate) AS max 
                  FROM openemr_postcalendar_events 
                  WHERE pc_pid = $nPid 
                  AND pc_apptstatus = '@' 
                  AND pc_eventDate >= '2007-01-01'");
  
  // now check if there was a previous encounter
  if( $strEventDate['max'] != "" )
    return( $strEventDate['max'] );
  else
    return( "00-00-0000" );
}

$m_strEventDate = getPatientDateOfLastEncounter( $result['pid'] );

// get autosave id for Psychiatrisch Onderzoek
$vectAutosave = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_brief_aan_verwijzer
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );

if( $vectAutosave['id'] && $vectAutosave['id'] != "" && $vectAutosave['id'] > 0 )
{
  $obj = formFetch("form_brief_aan_verwijzer", $vectAutosave['id']);
  
} else
{
  $obj = formFetch("form_brief_aan_verwijzer", (int)$_GET["id"] );
}

?>

<html>
    <head>
        <link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
         <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>

<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>

<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
  .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold;
                padding-left:3px; padding-right:3px; }
                 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal;
                               padding-left:3px; padding-right:3px; }
</style>
                               
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="../../../library/js/jquery121.js"></script>

<?php

if( $_GET["id"] )
  $brief_aan_verwijzer_id = $_GET["id"];
else
  $brief_aan_verwijzer_id = "0";

?>
<script type="text/javascript">
$(document).ready(function(){
        autosave();
                        });

function delete_autosave( )
{
  if( confirm('Are you sure you want to completely remove this form?') )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/brief_aan_verwijzer/delete_autosave.php",
              data: "id=" + <?php echo $brief_aan_verwijzer_id ?>  
                        ,
                                cache: false,
                                success: function( message )
                {
                     $("#timestamp").empty().append(message);
                }
            });
    return true;
    
  } else
  {
    return false;
  }

}

function autosave( )
{
  var t = setTimeout("autosave()", 20000);
  
  var a_introductie = $("#introductie").val();
  var a_reden_van_aanmelding = $("#reden_van_aanmelding").val();
  var a_anamnese = $("#anamnese").val();
  var a_psychiatrisch_onderzoek = $("#psychiatrisch_onderzoek").val();
  var a_beschrijvend_conclusie = $("#beschrijvend_conclusie").val();
  var a_advies_beleid = $("#advies_beleid").val();
    
  if( a_introductie.length > 0 || a_reden_van_aanmelding.length > 0 )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/brief_aan_verwijzer/autosave.php",
              data: "id=" + <?php echo $brief_aan_verwijzer_id ?> + 
                        "&introductie=" + $("#introductie").val() +
                        "&reden_van_aanmelding=" + a_reden_van_aanmelding +
                        "&anamnese=" + a_anamnese +
                        "&psychiatrisch_onderzoek=" + a_psychiatrisch_onderzoek +
                        "&beschrijvend_conclusie=" + a_beschrijvend_conclusie +
                        "&advies_beleid=" + a_advies_beleid +
                        "&mode=update" 
                        ,
                                cache: false,
                                success: function( message )
                {
                                        $("#timestamp").empty().append(message);
                }
            });
  }

}

</script>

<?php
include_once("$srcdir/api.inc");
//$obj = formFetch("form_brief_aan_verwijzer", (int)$_GET["id"]);
?>

<form method=post action="<?echo $rootdir?>/forms/brief_aan_verwijzer/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">BRIEF AAN VERWIJZER</span><Br><br>

<span class=text>BRIEF AAN VERWIJZER</span><br>

<br><span class=text>Introductie</span><br>
<textarea cols=80 rows=5 wrap=virtual name="introductie" id="introductie"><?php echo stripslashes($obj{"introductie"});?></textarea><br>

<br><span class=text>Reden van aanmelding</span><br>
<textarea cols=80 rows=5 wrap=virtual name="reden_van_aanmelding" id="reden_van_aanmelding"><?php echo stripslashes($obj{"reden_van_aanmelding"});?></textarea><br>
<br><span class=text>Anamnese:</span><br>
<textarea cols=80 rows=5 wrap=virtual name="anamnese" id="anamnese"><?php echo stripslashes($obj{"anamnese"});?></textarea><br>
<br><span class=text>Psychiatrisch onderzoek:</span><br>
<textarea cols=80 rows=5 wrap=virtual name="psychiatrisch_onderzoek" id="psychiatrisch_onderzoek"><?php echo stripslashes($obj{"psychiatrisch_onderzoek"});?></textarea><br>
<br><span class=text>Beschrijvend conclusie:</span><br>
<textarea cols=80 rows=5 wrap=virtual name="beschrijvend_conclusie" id="beschrijvend_conclusie"><?php echo stripslashes($obj{"beschrijvend_conclusie"});?></textarea><br>
<br><span class=text>Advies/beleid</span><br>
<textarea cols=80 rows=5 wrap=virtual name="advies_beleid" id="advies_beleid"><?php echo stripslashes($obj{"advies_beleid"});?></textarea><br>

<table><tr>
<?php 
// this to be used/moved above for form header with patient name/etc
?>
</tr></table>

<br><br><span style="margin: 5px; border: 1px solid green; font-size: 0.8em; padding: 3px;">add note here ?????</span>

<br><br>
<a href="javascript:document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link_submit"
 onclick="delete_autosave();top.restoreSession()">[<?php xl('Don\'t Save Changes','e'); ?>]</a>
</form>

<div id="timestamp"></div>

<?php
formFooter();
?>
