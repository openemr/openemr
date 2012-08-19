<?php
////////////////////////////////////////////////////////////////////
// Form:	PSYCHIATRISCH ONDERZOEK
// Package:	Research psihiatric - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 29-03-2008
////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/patient.inc");
formHeader("Form: psychiatrisch_onderzoek");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

$result = getPatientData($pid, "fname,lname,pid,pubpid,phone_home,pharmacy_id,DOB,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
$provider_results = sqlQuery("select * from users where username='" . $_SESSION{"authUser"} . "'");

////////////////////////////////////////////////////////////////////
// Function:	getPatientDateOfLastEncounter
function getPatientDateOfLastEncounter( $nPid )
{
  // get date of last encounter F103 or F153
  $strEventDate = sqlQuery("SELECT MAX(pc_eventDate) AS max 
                  FROM openemr_postcalendar_events 
                  WHERE pc_pid = $nPid 
                  AND pc_apptstatus = '@' 
                  AND ( pc_catid = 17 OR pc_catid = 25 OR pc_catid = 13 OR pc_catid = 26 ) 
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
$vectAutosavePO = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_psychiatrisch_onderzoek 
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=1 
                            ORDER by id DESC limit 1" );

//fetch data from INTAKE-VERSLAG
$obj_iv = formFetch("form_intakeverslag", $vectIntakeverslagQuery['id']);
// fetch data from PSYCHIATRISCH ONDERZOEK
$obj_po = formFetch("form_psychiatrisch_onderzoek", $vectAutosavePO['id']);
/////////////////
// here we mix the data
// Reden van aanmelding 
if( $obj_po['reden_van_aanmelding'] != '' ) 
  $obj['reden_van_aanmelding'] = $obj_po['reden_van_aanmelding'];
elseif( $obj_iv['reden_van_aanmelding'] != '' )
  $obj['reden_van_aanmelding'] = $obj_iv['reden_van_aanmelding'];
else
  $obj['reden_van_aanmelding'] = '';
// Conclusie van intake
if( $obj_po['conclusie_van_intake'] != '' ) 
  $obj['conclusie_van_intake'] = $obj_po['conclusie_van_intake'];
elseif( $obj_iv['beschrijvende_conclusie'] != '' )
  $obj['conclusie_van_intake'] = $obj_iv['beschrijvende_conclusie'];
else
  $obj['conclusie_van_intake'] = '';
// Medicatie - local
if( $obj_po['medicatie'] != '' ) 
  $obj['medicatie'] = $obj_po['medicatie'];
else
  $obj['medicatie'] = '';
// Anamnese - local
if( $obj_po['anamnese'] != '' ) 
  $obj['anamnese'] = $obj_po['anamnese'];
else
  $obj['anamnese'] = '';
// Psychiatrisch onderzoek i.e.z. - local
if( $obj_po['psychiatrisch_onderzoek'] != '' ) 
  $obj['psychiatrisch_onderzoek'] = $obj_po['psychiatrisch_onderzoek'];
else
  $obj['psychiatrisch_onderzoek'] = '';
// Beschrijvende conclusie
if( $obj_po['beschrijvende_conclusie'] != '' ) 
  $obj['beschrijvende_conclusie'] = $obj_po['beschrijvende_conclusie'];
elseif( $obj_iv['beschrijvende_conclusie'] != '' )
  $obj['beschrijvende_conclusie'] = $obj_iv['beschrijvende_conclusie'];
else
  $obj['beschrijvende_conclusie'] = '';
// Behandelvoorstel
if( $obj_po['behandelvoorstel'] != '' ) 
  $obj['behandelvoorstel'] = $obj_po['behandelvoorstel'];
elseif( $obj_iv['behandelvoorstel'] != '' )
  $obj['behandelvoorstel'] = $obj_iv['behandelvoorstel'];
else
  $obj['behandelvoorstel'] = ''; 

$tmpDate = stripslashes($obj{"datum_onderzoek"});
if( $tmpDate && $tmpDate != '0000-00-00 00:00:00' ) $m_strEventDate = $tmpDate;

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

if( $vectAutosavePO['id'] )
  $psychiatrisch_onderzoek_id = $vectAutosavePO['id'];
else
  $psychiatrisch_onderzoek_id = "0";

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
              url: "../../forms/psychiatrisch_onderzoek/delete_autosave.php",
              data: "id=" + <?php echo $psychiatrisch_onderzoek_id ?>  
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
  
  var a_datum_onderzoek = $("#datum_onderzoek").val();
  var a_reden_van_aanmelding = $("#reden_van_aanmelding").val();
  var a_conclusie_van_intake = $("#conclusie_van_intake").val();
  var a_medicatie = $("#medicatie").val();
  var a_anamnese = $("#anamnese").val();
  var a_psychiatrisch_onderzoek = $("#psychiatrisch_onderzoek").val();
  var a_beschrijvende_conclusie = $("#beschrijvende_conclusie").val();
  var a_behandelvoorstel = $("#behandelvoorstel").val();
    
  if( a_datum_onderzoek.length > 0 || a_reden_van_aanmelding.length > 0 )
  {
    $.ajax(
            {
              type: "POST",
              url: "../../forms/psychiatrisch_onderzoek/autosave.php",
              data: "id=" + <?php echo $psychiatrisch_onderzoek_id ?> + 
                        "&datum_onderzoek=" + $("#datum_onderzoek").val() +
                        "&reden_van_aanmelding=" + a_reden_van_aanmelding +
                        "&conclusie_van_intake=" + a_conclusie_van_intake +
                        "&medicatie=" + a_medicatie +
                        "&anamnese=" + a_anamnese +
                        "&psychiatrisch_onderzoek=" + a_psychiatrisch_onderzoek +
                        "&beschrijvende_conclusie=" + a_beschrijvende_conclusie +
                        "&behandelvoorstel=" + a_behandelvoorstel
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
<form method=post action="<?php echo $rootdir;?>/forms/psychiatrisch_onderzoek/save.php?mode=new&saveid=<?php echo $psychiatrisch_onderzoek_id; ?>" name="my_form">
<span class="title"><?php xl('Psychiatric Examination','e'); ?></span><br><br>

<table>
<tr>
<td><?php xl('Examination Date','e'); ?>:</td><td>
<input type='text' name='datum_onderzoek' id='datum_onderzoek' size='10' value='<?php echo $m_strEventDate ?>'
          onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php xl('Examination Date','e'); ?>: yyyy-mm-dd'></input>
<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
          id='img_last_encounter' border='0' alt='[?]' style='cursor:pointer'
          title='<?php xl('Click here to choose a date','e'); ?>'>

                     
<?php 

?></td>
</tr>
</table>

<br><span class=text><?php xl('Reason for Visit','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="reden_van_aanmelding" id="reden_van_aanmelding"><?php echo stripslashes($obj{"reden_van_aanmelding"});?></textarea><br>
<br><span class=text><?php xl('Intake Conclusion','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="conclusie_van_intake" id="conclusie_van_intake"><?php echo stripslashes($obj{"conclusie_van_intake"});?></textarea><br>
<br><span class=text><?php xl('Medications','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="medicatie" id="medicatie"><?php echo stripslashes($obj{"medicatie"});?></textarea><br>

<br><span class=text><?php xl('History','e'); ?></span><br>
<textarea cols=80 rows=10 wrap=virtual name="anamnese" id="anamnese"><?php echo stripslashes($obj{"anamnese"});?></textarea><br>

<br><span class=text><?php xl('Psychiatric Examination','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="psychiatrisch_onderzoek" id="psychiatrisch_onderzoek"><?php echo stripslashes($obj{"psychiatrisch_onderzoek"});?></textarea><br>
<br><span class=text><?php xl('Conclusions','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="beschrijvende_conclusie" id="beschrijvende_conclusie"><?php echo stripslashes($obj{"beschrijvende_conclusie"});?></textarea><br>
<br><span class=text><?php xl('Treatment Plan','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="behandelvoorstel" id="behandelvoorstel"><?php echo stripslashes($obj{"behandelvoorstel"});?></textarea><br>



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

<script language='JavaScript'>
 Calendar.setup({inputField:"datum_onderzoek", ifFormat:"%Y-%m-%d", button:"img_last_encounter"});
</script>

<div id="timestamp"></div>

<?php
formFooter();
?>
