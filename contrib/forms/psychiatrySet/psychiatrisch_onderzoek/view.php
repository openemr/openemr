<?php
/*
 * Psychiatrisch Onderzoek
 * Report of Research psihiatric - Dutch specific form
 * Version: 1.0 - 29-03-2008
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    Larry Lart
 * @link      http://www.open-emr.org
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/patient.inc");

$returnurl = 'encounter_top.php';

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

// get autosave id for Psychiatrisch Onderzoek
$vectAutosave = sqlQuery( "SELECT id, autosave_flag, autosave_datetime FROM form_psychiatrisch_onderzoek
                            WHERE pid = ".$_SESSION["pid"].
                            " AND groupname='".$_SESSION["authProvider"].
                            "' AND user='".$_SESSION["authUser"]."' AND
                            authorized=$userauthorized AND activity=1
                            AND autosave_flag=1
                            ORDER by id DESC limit 1" );


if( $vectAutosave['id'] && $vectAutosave['id'] != "" && $vectAutosave['id'] > 0 )
{
  $obj = formFetch("form_psychiatrisch_onderzoek", $vectAutosave['id']);

} else
{
    $obj = formFetch("form_psychiatrisch_onderzoek", $_GET["id"] );
}

$tmpDate = stripslashes($obj{"datum_onderzoek"});
if( $tmpDate && $tmpDate != '0000-00-00 00:00:00' ) $m_strEventDate = $tmpDate;

?>

<html>
    <head>
        <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
        <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
    </head>

<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
  .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold;
                padding-left:3px; padding-right:3px; }
                 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal;
                               padding-left:3px; padding-right:3px; }
</style>

<script type="text/javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="../../../library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<?php

if( $_GET["id"] )
  $psychiatrisch_onderzoek_id = $_GET["id"];
else
  $psychiatrisch_onderzoek_id = "0";

?>
<script type="text/javascript">
$(document).ready(function(){
        autosave();
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
           <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
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

<?php
include_once("$srcdir/api.inc");
//$obj = formFetch("form_psychiatrisch_onderzoek", (int)$_GET["id"]);
?>


<form method=post action="<?php echo $rootdir?>/forms/psychiatrisch_onderzoek/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form">
<span class="title"><?php xl('Psychiatric Examination','e'); ?></span><Br><br>

<table>
<tr>
<td><?php xl('Examination Date','e'); ?>:</td><td>
<input type='text' class='datepicker' name='datum_onderzoek' id='datum_onderzoek' size='10' value='<?php echo $m_strEventDate ?>'
          title='<?php xl('Examination Date','e'); ?>: yyyy-mm-dd'></input>


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
<textarea cols=80 rows=5 wrap=virtual name="anamnese" id="anamnese"><?php echo stripslashes($obj{"anamnese"});?></textarea><br>
<br><span class=text><?php xl('Psychiatric Examination','e'); ?></span><br>
<textarea cols=80 rows=5 wrap=virtual name="psychiatrisch_onderzoek" id="psychiatrisch_onderzoek"><?php echo stripslashes($obj{"psychiatrisch_onderzoek"});?></textarea><br>
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
 onclick="delete_autosave();top.restoreSession()">[<?php xl('Don\'t Save Changes','e'); ?>]</a>
</form>

<div id="timestamp"></div>


<?php
formFooter();
?>
