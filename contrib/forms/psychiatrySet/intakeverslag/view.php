<?php
////////////////////////////////////////////////////////////////////
// Form:	Intakeverslag - view
// Package:	Report of First visit - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 27-03-2008
////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/patient.inc");

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

$result = getPatientData($pid, "fname,lname,pid,pubpid,phone_home,pharmacy_id,DOB,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
$provider_results = sqlQuery("select * from users where username='" . $_SESSION{"authUser"} . "'");
$age = getPatientAge($result["DOB_YMD"]);

////////////////////////////////////////////////////////////////////
// Function:	getPatientDateOfLastEncounter
function getPatientDateOfLastEncounter( $nPid )
{
  $strEventDate = sqlQuery("SELECT MAX(pc_eventDate) AS max 
                  FROM openemr_postcalendar_events 
                  WHERE pc_pid = $nPid 
                  AND pc_apptstatus = '@' 
                  AND ( pc_catid = 12 OR pc_catid = 16 ) 
                  AND pc_eventDate >= '2007-01-01'");
  
  // now check if there was a previous encounter
  if( $strEventDate['max'] != "" )
    return( $strEventDate['max'] );
  else
    return( "00-00-0000" );
}

$m_strEventDate = getPatientDateOfLastEncounter( $result['pid'] );

// get autosave id
$vectAutosave = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_intakeverslag 
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );

//$obj = formFetch("form_intakeverslag", $vectAutosave['id']);

if( $vectAutosave['id'] && $vectAutosave['id'] != "" && $vectAutosave['id'] > 0 )
{
  $obj = formFetch("form_intakeverslag", $vectAutosave['id']);
  
} else
{
  $obj = formFetch("form_intakeverslag", (int)$_GET["id"] );
}

$tmpDate = stripslashes($obj{"intakedatum"});
if( $tmpDate && $tmpDate != '0000-00-00 00:00:00' ) $m_strEventDate = $tmpDate;

?>

<html>
    <head>
        <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
    </head>

<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
//$obj = formFetch("form_intakeverslag", (int)$_GET["id"]);
?>

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
  $intakeverslag_id = $_GET["id"];
else
  $intakeverslag_id = "0";

?>
<script type="text/javascript">
$(document).ready(function(){
        autosave();
                        });

function delete_autosave( )
{
  if( confirm("<?php xl('Are you sure you want to completely remove this form?','e'); ?>") )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/intakeverslag/delete_autosave.php",
              data: "id=" + <?php echo $intakeverslag_id ?>  
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
  
  var a_intakedatum = $("#intakedatum").val();
  var a_reden_van_aanmelding = $("#reden_van_aanmelding").val();
  var a_klachten_probleemgebieden = $("#klachten_probleemgebieden").val();
  var a_hulpverlening_onderzoek = $("#hulpverlening_onderzoek").val();
  var a_hulpvraag_en_doelen = $("#hulpvraag_en_doelen").val();
  var a_bijzonderheden_systeem = $("#bijzonderheden_systeem").val();
  var a_werk_opleiding_vrije_tijdsbesteding = $("#werk_opleiding_vrije_tijdsbesteding").val();
  var a_relatie_kinderen = $("#relatie_kinderen").val();
  var a_somatische_context = $("#somatische_context").val();
  var a_alcohol = $("#alcohol").val();
  var a_drugs = $("#drugs").val();
  var a_roken = $("#roken").val();
  var a_medicatie = $("#medicatie").val();
  var a_familieanamnese = $("#familieanamnese").val();
  var a_indruk_observaties = $("#indruk_observaties").val();
  var a_beschrijvende_conclusie = $("#beschrijvende_conclusie").val();
  var a_behandelvoorstel = $("#behandelvoorstel").val();
  
  if( a_intakedatum.length > 0 || a_reden_van_aanmelding.length > 0 )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/intakeverslag/autosave.php",
              data: "id=" + <?php echo $intakeverslag_id ?> + 
                        "&intakedatum=" + $("#intakedatum").val() +
                        "&reden_van_aanmelding=" + a_reden_van_aanmelding +
                        "&klachten_probleemgebieden=" + a_klachten_probleemgebieden +
                        "&hulpverlening_onderzoek=" + a_hulpverlening_onderzoek +
                        "&hulpvraag_en_doelen=" + a_hulpvraag_en_doelen +
                        "&bijzonderheden_systeem=" + a_bijzonderheden_systeem +
                        "&werk_opleiding_vrije_tijdsbesteding=" + a_werk_opleiding_vrije_tijdsbesteding +
                        "&relatie_kinderen=" + a_relatie_kinderen +
                        "&somatische_context=" + a_somatische_context +
                        "&alcohol=" + a_alcohol +
                        "&drugs=" + a_drugs +
                        "&roken=" + a_roken +
                        "&medicatie=" + a_medicatie +
                        "&familieanamnese=" + a_familieanamnese +
                        "&indruk_observaties=" + a_indruk_observaties +
                        "&beschrijvende_conclusie=" + a_beschrijvende_conclusie +
                        "&behandelvoorstel=" + a_behandelvoorstel +
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


<form method=post action="<?php echo $rootdir?>/forms/intakeverslag/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form">
<span class="title"><?php xl('Psychiatric Intake','e'); ?></span><Br><br>

<table>
<tr>
<td><?php xl('Intake Date','e'); ?>:</td><td>
<input type='text' name='intakedatum' id='intakedatum' size='10' value='<?php echo $m_strEventDate ?>'
          onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php xl('Intake Date','e'); ?>: yyyy-mm-dd'></input>
<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
          id='img_last_encounter' border='0' alt='[?]' style='cursor:pointer'
          title='<?php xl('Click here to choose a date','e'); ?>'>

                     
<?php 

?></td>
</tr>
</table>

<br><span class=text><?php xl('Reason for Visit','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="reden_van_aanmelding" id="reden_van_aanmelding"><?php echo stripslashes($obj{"reden_van_aanmelding"});?></textarea><br>
<br><span class=text><?php xl('Problem List','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="klachten_probleemgebieden" id="klachten_probleemgebieden"><?php echo stripslashes($obj{"klachten_probleemgebieden"});?></textarea><br>

<br><span class=text><?php xl('Psychiatric History','e'); ?></span><br>
<textarea cols=80 rows=10 wrap=virtual name="hulpverlening_onderzoek" id="hulpverlening_onderzoek"><?php echo stripslashes($obj{"hulpverlening_onderzoek"});?></textarea><br>

<br><span class=text><?php xl('Treatment Goals','e'); ?></span><br>
<textarea cols=80 rows=10 wrap=virtual name="hulpvraag_en_doelen" id="hulpvraag_en_doelen"><?php echo stripslashes($obj{"hulpvraag_en_doelen"});?></textarea><br>

<br><span class=text><?php xl('Specialty Systems','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="bijzonderheden_systeem" id="bijzonderheden_systeem"><?php echo stripslashes($obj{"bijzonderheden_systeem"});?></textarea><br>
<br><span class=text><?php xl('Work/ Education/ Hobbies','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="werk_opleiding_vrije_tijdsbesteding" id="werk_opleiding_vrije_tijdsbesteding"><?php echo stripslashes($obj{"werk_opleiding_vrije_tijdsbesteding"});?></textarea><br>
<br><span class=text><?php xl('Relation(s) / Children','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="relatie_kinderen" id="relatie_kinderen"><?php echo stripslashes($obj{"relatie_kinderen"});?></textarea><br>
<br><span class=text><?php xl('Somatic Context','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="somatische_context" id="somatische_context"><?php echo stripslashes($obj{"somatische_context"});?></textarea><br>

<br>
<table>
<tr>
<td align="right"  class=text><?php xl('Alcohol','e'); ?></td>
<td><input type="text" name="alcohol" size="60" value="<?php echo stripslashes($obj{"alcohol"});?>" id="alcohol"></input></td>
</tr><tr>
<td align="right" class=text><?php xl('Drugs','e'); ?></td>
<td><input type="text" name="drugs" size="60" value="<?php echo stripslashes($obj{"drugs"});?>" id="drugs"></input></td>
</tr><tr>
<td align="right" class=text><?php xl('Tobacco','e'); ?></td>
<td><input type="text" name="roken" size="60" value="<?php echo stripslashes($obj{"roken"});?>" id="roken"></input></td>
</tr>
</table>

<br><span class=text><?php xl('Medications','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="medicatie" id="medicatie"><?php echo stripslashes($obj{"medicatie"});?></textarea><br>
<br><span class=text><?php xl('Family History','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="familieanamnese" id="familieanamnese"><?php echo stripslashes($obj{"familieanamnese"});?></textarea><br>
<br><span class=text><?php xl('Assessment','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="indruk_observaties" id="indruk_observaties"><?php echo stripslashes($obj{"indruk_observaties"});?></textarea><br>
<br><span class=text><?php xl('Conclusions','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="beschrijvende_conclusie" id="beschrijvende_conclusie"><?php echo stripslashes($obj{"beschrijvende_conclusie"});?></textarea><br>
<br><span class=text><?php xl('Treatment Plan','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="behandelvoorstel" id="behandelvoorstel"><?php echo stripslashes($obj{"behandelvoorstel"});?></textarea><br>

<table><tr>
<?php 
// this to be used/moved above for form header with patient name/etc
?>
</tr></table>

<br><br>
<a href="javascript:document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link_submit"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save Changes','e'); ?>]</a>
</form>

<script language='JavaScript'>
 Calendar.setup({inputField:"intakedatum", ifFormat:"%Y-%m-%d", button:"img_last_encounter"});
</script>

<div id="timestamp"></div>

<?php
formFooter();
?>
