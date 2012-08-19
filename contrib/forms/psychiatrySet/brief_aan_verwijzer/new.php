<?php
////////////////////////////////////////////////////////////////////
// Form:	brief_aan_verwijzer
// Package:	letter to verwijzer - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 30-03-2008
////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/patient.inc");

formHeader("Form: brief_aan_verwijzer");
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

// get last saved id for intakeverslag
$vectIntakeverslagQuery = sqlQuery( "SELECT id FROM form_intakeverslag 
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=0 
                            ORDER by id DESC limit 1" );

// get autosave id for Psychiatrisch Onderzoek
$vectPO = sqlQuery( "SELECT id FROM form_psychiatrisch_onderzoek 
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=0 
                            ORDER by id DESC limit 1" );

// get autosave id for Psychiatrisch Onderzoek
$vectAutosaveBAV = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_brief_aan_verwijzer
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );

//fetch data from INTAKE-VERSLAG
$obj_iv = formFetch( "form_intakeverslag", $vectIntakeverslagQuery['id'] );
// fetch data from PSYCHIATRISCH ONDERZOEK
$obj_po = formFetch( "form_psychiatrisch_onderzoek", $vectPO['id'] );
// fetch data from brief_aan_verwijzer
$obj_bav = formFetch( "form_brief_aan_verwijzer", $vectAutosaveBAV['id'] );

/////////////////
// here we mix the data

// Introductie - local
// create the inroductie form
if( $obj_bav['introductie'] != '' ) 
  $obj['introductie'] = $obj_bav['introductie'];
else
  $obj['introductie'] = xl("Since","",""," ") . $m_strEventDate . xl("we have seen your above patient for evaluation and treatment at our outpatient psychiatry clinic. Thank you for this referral.",""," ");

// Reden van aanmelding 
if( $obj_bav['reden_van_aanmelding'] != '' ) 
  $obj['reden_van_aanmelding'] = $obj_bav['reden_van_aanmelding'];
elseif( $obj_iv['reden_van_aanmelding'] != '' )
  $obj['reden_van_aanmelding'] = $obj_iv['reden_van_aanmelding'];
else
  $obj['reden_van_aanmelding'] = '';
  
// Anamnese
if( $obj_bav['anamnese'] != '' ) 
  $obj['anamnese'] = $obj_bav['anamnese'];
elseif( $obj_iv['klachten_probleemgebieden'] != '' )
  $obj['anamnese'] = $obj_iv['klachten_probleemgebieden'];
else
  $obj['anamnese'] = '';

// Psychiatrisch onderzoek 
if( $obj_bav['psychiatrisch_onderzoek'] != '' ) 
  $obj['psychiatrisch_onderzoek'] = $obj_bav['psychiatrisch_onderzoek'];
elseif( $obj_po['psychiatrisch_onderzoek'] != '' )
  $obj['psychiatrisch_onderzoek'] = $obj_po['psychiatrisch_onderzoek'];
else
  $obj['psychiatrisch_onderzoek'] = '';

// Beschrijvend conclusie 
if( $obj_bav['beschrijvend_conclusie'] != '' ) 
  $obj['beschrijvend_conclusie'] = $obj_bav['beschrijvend_conclusie'];
elseif( $obj_po['beschrijvende_conclusie'] != '' )
  $obj['beschrijvend_conclusie'] = $obj_po['beschrijvende_conclusie'];
else
  $obj['beschrijvend_conclusie'] = '';
  
// Advies/beleid
if( $obj_bav['advies_beleid'] != '' ) 
  $obj['advies_beleid'] = $obj_bav['advies_beleid'];
elseif( $obj_po['behandelvoorstel'] != '' )
  $obj['advies_beleid'] = $obj_po['behandelvoorstel'];
else
  $obj['advies_beleid'] = ''; 


?>

<html>
<head>
    <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>

                               

<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

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

if( $vectAutosaveBAV['id'] )
  $brief_aan_verwijzer_id = $vectAutosaveBAV['id'];
else
  $brief_aan_verwijzer_id = "0";

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
                        "&advies_beleid=" + a_advies_beleid
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
        
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form method=post action="<?php echo $rootdir;?>/forms/brief_aan_verwijzer/save.php?mode=new&saveid=<?php echo $brief_aan_verwijzer_id; ?>" name="my_form">
<span class="title"><?php xl('Psychiatric Brief Letter','e'); ?></span><br><br>

<br><span class=text><?php xl('Introduction','e'); ?></span><br>
<textarea cols=80 rows=3 wrap=virtual name="introductie" id="introductie"><?php echo stripslashes($obj{"introductie"});?></textarea><br>

<br><span class=text><?php xl('Reason for Visit','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="reden_van_aanmelding" id="reden_van_aanmelding"><?php echo stripslashes($obj{"reden_van_aanmelding"});?></textarea><br>
<br><span class=text><?php xl('History','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="anamnese" id="anamnese"><?php echo stripslashes($obj{"anamnese"});?></textarea><br>
<br><span class=text><?php xl('Psychiatric Examination','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="psychiatrisch_onderzoek" id="psychiatrisch_onderzoek"><?php echo stripslashes($obj{"psychiatrisch_onderzoek"});?></textarea><br>
<br><span class=text><?php xl('Conclusions','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="beschrijvend_conclusie" id="beschrijvend_conclusie"><?php echo stripslashes($obj{"beschrijvend_conclusie"});?></textarea><br>
<br><span class=text><?php xl('Treatment Plan','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="advies_beleid" id="advies_beleid"><?php echo stripslashes($obj{"advies_beleid"});?></textarea><br>

<table><tr>

<?php 
// here we fill in the header above with patient name etc ? ??? - move above 

?>
</tr></table>

<br><br>
<a href="javascript:document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link_submit" onclick="delete_autosave();top.restoreSession()">[<?php xl('Don\'t Save','e'); ?>]</a>
</form>


<div id="timestamp"></div>

<?php
formFooter();
?>
